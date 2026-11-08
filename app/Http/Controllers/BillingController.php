<?php

namespace App\Http\Controllers;

use App\Models\BillingInvoice;
use App\Models\Plan;
use App\Services\PaymentGatewayService;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BillingController extends Controller
{
    public function __construct(
        private PaymentGatewayService $gatewayService
    ) {}

    /**
     * Display subscription overview, plans, and billing history.
     */
    public function index()
    {
        $tenant = TenantContext::getTenant();
        if (!$tenant) {
            if (auth()->user()?->isSuperAdmin()) {
                return redirect()->route('saas-admin.index');
            }

            abort(403, 'Akun ini belum terhubung ke usaha laundry.');
        }

        $subscription = $tenant->activeSubscription;
        $currentPlan  = $subscription?->plan ?? $tenant->currentPlan();
        $plans        = Plan::where('is_active', true)->get();
        $invoices     = $tenant->billingInvoices()->with('plan')->latest()->get();

        return view('billing.index', compact('tenant', 'subscription', 'currentPlan', 'plans', 'invoices'));
    }

    /**
     * Process plan selection and generate billing invoice + Midtrans Snap token.
     */
    public function choosePlan(Request $request)
    {
        $tenant = TenantContext::getTenant();
        if (!$tenant) {
            abort(404, 'Data tenant usaha tidak ditemukan.');
        }

        $validated = $request->validate([
            'plan_id'       => ['required', 'exists:plans,id'],
            'billing_cycle' => ['required', 'in:monthly,yearly'],
        ]);

        $plan = Plan::findOrFail($validated['plan_id']);
        $amount = $validated['billing_cycle'] === 'yearly' ? $plan->price_yearly : $plan->price_monthly;

        $invoiceNumber = 'INV-' . date('Ymd') . '-' . strtoupper(Str::random(5));

        $invoice = BillingInvoice::create([
            'invoice_number'  => $invoiceNumber,
            'tenant_id'       => $tenant->id,
            'subscription_id' => $tenant->activeSubscription?->id,
            'plan_id'         => $plan->id,
            'billing_cycle'   => $validated['billing_cycle'],
            'amount'          => $amount,
            'status'          => 'unpaid',
            'due_at'          => now()->addDays(3),
            'payment_method'  => null,
        ]);

        $this->gatewayService->ensureSnapToken($invoice);

        return redirect()->route('billing.invoice.show', $invoice)
            ->with('success', 'Tagihan berhasil dibuat. Silakan selesaikan pembayaran otomatis melalui Payment Gateway.');
    }

    /**
     * Show invoice payment details & automatic Payment Gateway checkout.
     */
    public function showInvoice(Request $request, BillingInvoice $invoice)
    {
        $tenant = TenantContext::getTenant();
        if ($invoice->tenant_id !== $tenant?->id && !auth()->user()?->isSuperAdmin()) {
            abort(403, 'Akses ke tagihan ini tidak diizinkan.');
        }

        $invoice->load(['plan', 'tenant.owner']);

        if ($invoice->status === 'unpaid') {
            if ($request->hasAny(['transaction_status', 'status_code', 'order_id']) || $request->boolean('auto_check')) {
                $synced = $this->gatewayService->syncInvoiceStatusFromGateway(
                    $invoice,
                    $request->query('transaction_id'),
                    $request->query('order_id'),
                    $request->only(['transaction_id', 'order_id', 'transaction_status', 'status_code', 'payment_type'])
                );
                $invoice->refresh();

                if ($synced || $invoice->status === 'paid') {
                    return redirect()->route('dashboard')
                        ->with('success', 'Pembayaran berhasil diverifikasi otomatis oleh Payment Gateway! Selamat datang di Dashboard Laundiqo.');
                }
            }

            if ($invoice->status === 'unpaid') {
                $this->gatewayService->ensureSnapToken($invoice);
            }
        }

        return view('billing.invoice', [
            'invoice'           => $invoice,
            'gatewayConfigured' => $this->gatewayService->isConfigured(),
            'snapJsUrl'         => $this->gatewayService->getSnapJsUrl(),
            'clientKey'         => $this->gatewayService->getClientKey(),
        ]);
    }

    /**
     * Automatically check and sync payment status from Payment Gateway API.
     */
    public function checkStatus(Request $request, BillingInvoice $invoice)
    {
        $tenant = TenantContext::getTenant();
        if ($invoice->tenant_id !== $tenant?->id && !auth()->user()?->isSuperAdmin()) {
            abort(403, 'Akses ke tagihan ini tidak diizinkan.');
        }

        if ($invoice->status === 'paid') {
            return redirect()->route('dashboard')
                ->with('success', 'Tagihan ini sudah lunas dan langganan Anda telah aktif.');
        }

        $snapResult = $request->only(['transaction_id', 'order_id', 'transaction_status', 'status_code', 'payment_type']);

        if ($this->gatewayService->syncInvoiceStatusFromGateway(
            $invoice,
            $request->input('transaction_id'),
            $request->input('order_id'),
            $snapResult
        )) {
            return redirect()->route('dashboard')
                ->with('success', 'Pembayaran berhasil diverifikasi otomatis oleh Payment Gateway! Akun laundry Anda aktif penuh.');
        }

        return redirect()->route('billing.invoice.show', $invoice)
            ->with('error', 'Pembayaran belum terdeteksi lunas di Payment Gateway. Silakan selesaikan pembayaran terlebih dahulu.');
    }

    /**
     * Process / Simulate automatic gateway payment (for Sandbox/Local or Superadmin).
     */
    public function payInvoice(Request $request, BillingInvoice $invoice)
    {
        $tenant = TenantContext::getTenant();
        if ($invoice->tenant_id !== $tenant?->id && !auth()->user()?->isSuperAdmin()) {
            abort(403, 'Akses ke tagihan ini tidak diizinkan.');
        }

        if ($invoice->status === 'paid') {
            return redirect()->route('billing.index')->with('info', 'Tagihan ini sudah berstatus lunas.');
        }

        $validated = $request->validate([
            'payment_method' => ['required', 'string', 'in:qris,bank_transfer,virtual_account,simulated'],
        ]);

        // In Production mode with Midtrans configured, require real Midtrans API verification for non-superadmin
        if (config('services.midtrans.is_production') && $this->gatewayService->isConfigured() && !auth()->user()?->isSuperAdmin()) {
            if ($this->gatewayService->syncInvoiceStatusFromGateway($invoice)) {
                return redirect()->route('billing.index')
                    ->with('success', 'Pembayaran langganan berhasil diverifikasi otomatis! Akun laundry Anda aktif penuh.');
            }

            return redirect()->route('billing.invoice.show', $invoice)
                ->with('error', 'Silakan selesaikan pembayaran melalui popup Payment Gateway terlebih dahulu.');
        }

        $this->gatewayService->fulfillPaidInvoice(
            $invoice,
            $validated['payment_method'],
            'SIM-' . strtoupper(Str::random(8))
        );

        return redirect()->route('billing.index')
            ->with('success', 'Pembayaran langganan berhasil dikonfirmasi otomatis! Akun laundry Anda aktif penuh.');
    }

    /**
     * Webhook receiver for payment gateways (Midtrans / Xendit).
     */
    public function webhook(Request $request)
    {
        $invoiceNumber = $request->input('order_id') ?? $request->input('external_id');
        $status        = strtolower((string) ($request->input('transaction_status') ?? $request->input('status')));
        $fraudStatus   = strtolower((string) ($request->input('fraud_status') ?? 'accept'));

        if (!$invoiceNumber) {
            return response()->json(['message' => 'Missing invoice reference'], 400);
        }

        if ($request->has('signature_key') && !$this->gatewayService->verifyWebhookSignature($request->all())) {
            return response()->json(['message' => 'Invalid webhook signature'], 403);
        }

        $invoice = BillingInvoice::where('invoice_number', $invoiceNumber)->first();
        if (!$invoice) {
            return response()->json(['message' => 'Invoice not found'], 404);
        }

        $isPaid = in_array($status, ['paid', 'settlement', 'capture', 'success', 'completed'], true)
            && $fraudStatus === 'accept';

        if ($isPaid && $invoice->status !== 'paid') {
            $paymentMethod = (string) ($request->input('payment_type') ?? $request->input('payment_method') ?? 'gateway_webhook');
            $providerRef   = (string) ($request->input('transaction_id') ?? $request->input('id') ?? ('GW-' . time()));

            $this->gatewayService->fulfillPaidInvoice($invoice, $paymentMethod, $providerRef, $request->all());
        } elseif (in_array($status, ['expire', 'expired'], true) && $invoice->status === 'unpaid') {
            $invoice->update(['status' => 'expired']);
        } elseif (in_array($status, ['cancel', 'deny'], true) && $invoice->status === 'unpaid') {
            $invoice->update(['status' => 'cancelled']);
        }

        return response()->json(['message' => 'Webhook processed successfully']);
    }
}
