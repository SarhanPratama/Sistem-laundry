<?php

namespace App\Services;

use App\Models\BillingInvoice;
use App\Models\BillingPayment;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentGatewayService
{
    /**
     * Check whether Midtrans API keys are configured.
     */
    public function isConfigured(): bool
    {
        $serverKey = trim((string) config('services.midtrans.server_key'));
        $clientKey = trim((string) config('services.midtrans.client_key'));

        return $serverKey !== '' && $clientKey !== '';
    }

    public function isProduction(): bool
    {
        return (bool) config('services.midtrans.is_production', false);
    }

    public function getClientKey(): string
    {
        return (string) config('services.midtrans.client_key', '');
    }

    public function getSnapJsUrl(): string
    {
        return $this->isProduction()
            ? 'https://app.midtrans.com/snap/snap.js'
            : 'https://app.sandbox.midtrans.com/snap/snap.js';
    }

    protected function getSnapApiUrl(): string
    {
        return $this->isProduction()
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';
    }

    protected function getCoreApiBaseUrl(): string
    {
        return $this->isProduction()
            ? 'https://api.midtrans.com/v2'
            : 'https://api.sandbox.midtrans.com/v2';
    }

    /**
     * Generate or retrieve existing Midtrans Snap Token & Redirect URL for an invoice.
     */
    public function ensureSnapToken(BillingInvoice $invoice): ?string
    {
        if (!$this->isConfigured() || $invoice->status === 'paid') {
            return $invoice->snap_token;
        }

        if ($invoice->snap_token) {
            return $invoice->snap_token;
        }

        $invoice->loadMissing(['tenant.owner', 'plan']);
        $tenant = $invoice->tenant;
        $owner  = $tenant?->owner;
        $plan   = $invoice->plan;

        $grossAmount = (int) round((float) $invoice->amount);
        $cycleLabel  = $invoice->billing_cycle === 'yearly' ? 'Tahunan (1 Tahun)' : 'Bulanan (1 Bulan)';

        $payload = [
            'transaction_details' => [
                'order_id'     => $invoice->invoice_number,
                'gross_amount' => $grossAmount,
            ],
            'item_details' => [
                [
                    'id'       => 'PLAN-' . ($plan?->slug ?? 'business') . '-' . $invoice->billing_cycle,
                    'price'    => $grossAmount,
                    'quantity' => 1,
                    'name'     => substr("Langganan Laundiqo Paket " . ($plan?->name ?? 'Business') . " - {$cycleLabel}", 0, 50),
                ],
            ],
            'customer_details' => [
                'first_name' => $owner?->name ?: ($tenant?->name ?: 'Pemilik Laundry'),
                'email'      => $owner?->email ?: 'owner@laundiqo.test',
                'phone'      => $tenant?->phone ?: '',
            ],
            'callbacks' => [
                'finish' => route('billing.invoice.show', $invoice),
            ],
        ];

        try {
            $serverKey = (string) config('services.midtrans.server_key');
            $response = Http::withBasicAuth($serverKey, '')
                ->acceptJson()
                ->timeout(15)
                ->post($this->getSnapApiUrl(), $payload);

            if ($response->successful()) {
                $data = $response->json();
                $invoice->update([
                    'snap_token'  => $data['token'] ?? null,
                    'payment_url' => $data['redirect_url'] ?? null,
                ]);

                return $invoice->snap_token;
            }

            Log::warning('Midtrans Snap token creation failed', [
                'invoice' => $invoice->invoice_number,
                'status'  => $response->status(),
                'body'    => $response->json(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Midtrans Snap API exception: ' . $e->getMessage(), [
                'invoice' => $invoice->invoice_number,
            ]);
        }

        return null;
    }

    /**
     * Query Midtrans Core API directly to verify real-time transaction status
     * and auto-activate subscription if paid/settled.
     */
    public function syncInvoiceStatusFromGateway(
        BillingInvoice $invoice,
        ?string $transactionId = null,
        ?string $gatewayOrderId = null,
        array $snapResult = []
    ): bool {
        if ($invoice->status === 'paid') {
            return true;
        }

        if (!$this->isConfigured()) {
            return false;
        }

        $serverKey = (string) config('services.midtrans.server_key');
        $lookupIds = array_values(array_unique(array_filter([
            $transactionId,
            $gatewayOrderId,
            $invoice->invoice_number,
        ])));

        foreach ($lookupIds as $lookupId) {
            try {
                $url = $this->getCoreApiBaseUrl() . '/' . urlencode((string) $lookupId) . '/status';

                $response = Http::withBasicAuth($serverKey, '')
                    ->acceptJson()
                    ->timeout(15)
                    ->get($url);

                if (!$response->successful()) {
                    continue;
                }

                $data = $response->json();
                $txStatus = strtolower((string) ($data['transaction_status'] ?? ''));
                $fraudStatus = strtolower((string) ($data['fraud_status'] ?? 'accept'));

                $isPaid = in_array($txStatus, ['settlement', 'capture'], true) && $fraudStatus === 'accept';

                if ($isPaid) {
                    $paymentType = (string) ($data['payment_type'] ?? 'midtrans_gateway');
                    $txId        = (string) ($data['transaction_id'] ?? ('MT-' . time()));

                    $this->fulfillPaidInvoice($invoice, $paymentType, $txId, $data);
                    return true;
                }
            } catch (\Throwable $e) {
                Log::error('Midtrans status check exception: ' . $e->getMessage(), [
                    'invoice'   => $invoice->invoice_number,
                    'lookup_id' => $lookupId,
                ]);
            }
        }

        // In Sandbox mode, some redirect simulators (like DANA/Indomaret sandbox) resolve inside Snap
        // and pass settlement/200 to Snap's onSuccess/finish callback without persisting in Core API v2 status.
        if (!$this->isProduction() && !empty($snapResult)) {
            $snapTxStatus   = strtolower((string) ($snapResult['transaction_status'] ?? ''));
            $snapStatusCode = (string) ($snapResult['status_code'] ?? '');

            if (in_array($snapTxStatus, ['settlement', 'capture', 'success'], true) || $snapStatusCode === '200') {
                $paymentType = (string) ($snapResult['payment_type'] ?? 'midtrans_sandbox');
                $txId        = (string) ($snapResult['transaction_id'] ?? ('SB-' . strtoupper(Str::random(8))));

                $this->fulfillPaidInvoice($invoice, $paymentType, $txId, $snapResult);
                return true;
            }
        }

        return false;
    }

    /**
     * Verify Midtrans webhook SHA-512 signature_key when server_key is configured.
     */
    public function verifyWebhookSignature(array $payload): bool
    {
        if (!$this->isConfigured()) {
            return true;
        }

        $signatureKey = (string) ($payload['signature_key'] ?? '');
        $orderId      = (string) ($payload['order_id'] ?? '');
        $statusCode   = (string) ($payload['status_code'] ?? '');
        $grossAmount  = (string) ($payload['gross_amount'] ?? '');
        $serverKey    = (string) config('services.midtrans.server_key');

        if ($signatureKey === '' || $orderId === '' || $statusCode === '' || $grossAmount === '') {
            return false;
        }

        $expected = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        return hash_equals($expected, $signatureKey);
    }

    /**
     * Atomically mark invoice as paid, record payment, and activate/extend tenant subscription.
     */
    public function fulfillPaidInvoice(
        BillingInvoice $invoice,
        string $paymentMethod,
        ?string $providerRef = null,
        array $payload = []
    ): void {
        if ($invoice->status === 'paid') {
            return;
        }

        DB::transaction(function () use ($invoice, $paymentMethod, $providerRef, $payload) {
            $lockedInvoice = BillingInvoice::where('id', $invoice->id)->lockForUpdate()->first();
            if (!$lockedInvoice || $lockedInvoice->status === 'paid') {
                return;
            }

            $lockedInvoice->update([
                'status'         => 'paid',
                'paid_at'        => now(),
                'payment_method' => $paymentMethod,
            ]);

            BillingPayment::create([
                'invoice_id'     => $lockedInvoice->id,
                'tenant_id'      => $lockedInvoice->tenant_id,
                'provider_ref'   => $providerRef ?: ('GW-' . strtoupper(Str::random(8))),
                'payment_method' => $paymentMethod,
                'amount'         => $lockedInvoice->amount,
                'status'         => 'success',
                'payload'        => !empty($payload) ? $payload : null,
                'paid_at'        => now(),
            ]);

            $targetTenant   = $lockedInvoice->tenant;
            $currentSub     = $targetTenant->activeSubscription;
            $durationMonths = $lockedInvoice->billing_cycle === 'yearly' ? 12 : 1;

            if ($currentSub && $currentSub->status === 'active' && $currentSub->ends_at && $currentSub->ends_at->isFuture()) {
                $startsAt = $currentSub->starts_at;
                $endsAt   = $currentSub->ends_at->copy()->addMonths($durationMonths);
            } else {
                $startsAt = now();
                $endsAt   = now()->addMonths($durationMonths);
            }

            Subscription::create([
                'tenant_id'     => $targetTenant->id,
                'plan_id'       => $lockedInvoice->plan_id,
                'billing_cycle' => $lockedInvoice->billing_cycle,
                'status'        => 'active',
                'starts_at'     => $startsAt,
                'ends_at'       => $endsAt,
            ]);

            $targetTenant->update([
                'status' => 'active',
            ]);
        });
    }
}

