<?php

namespace App\Http\Controllers;

use App\Models\BillingInvoice;
use App\Models\ContactMessage;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Setting;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SaasAdminController extends Controller
{
    public function __construct(
        private PaymentGatewayService $gatewayService
    ) {}

    /**
     * SaaS Platform Overview Dashboard.
     */
    public function index()
    {
        $totalTenants   = Tenant::count();
        $activeTenants  = Tenant::where('status', 'active')->count();
        $trialTenants   = Tenant::where('status', 'trial')->count();
        $pastDueTenants = Tenant::whereIn('status', ['past_due', 'grace_period', 'suspended'])->count();

        // Calculate Monthly Recurring Revenue (MRR) from active subscriptions
        $activeSubs = Subscription::with('plan')
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>', now());
            })
            ->get();

        $mrr = 0.0;
        foreach ($activeSubs as $sub) {
            if ($sub->plan) {
                if ($sub->billing_cycle === 'yearly') {
                    $mrr += (float) ($sub->plan->price_yearly / 12);
                } else {
                    $mrr += (float) $sub->plan->price_monthly;
                }
            }
        }

        $totalRevenue        = (float) BillingInvoice::where('status', 'paid')->sum('amount');
        $unpaidInvoicesCount = BillingInvoice::where('status', 'unpaid')->count();
        $totalOrdersPlatform = Order::withoutGlobalScopes()->count();
        $totalPlans          = Plan::where('is_active', true)->count();

        $recentTenants = Tenant::with(['owner', 'activeSubscription.plan'])
            ->withCount(['orders', 'users', 'customers'])
            ->latest()
            ->take(8)
            ->get();

        $recentInvoices = BillingInvoice::with(['tenant', 'plan'])
            ->latest()
            ->take(8)
            ->get();

        $contactMessages     = ContactMessage::latest()->take(8)->get();
        $unreadMessagesCount = ContactMessage::where('is_read', false)->count();

        return view('saas-admin.index', compact(
            'totalTenants',
            'activeTenants',
            'trialTenants',
            'pastDueTenants',
            'mrr',
            'totalRevenue',
            'unpaidInvoicesCount',
            'totalOrdersPlatform',
            'totalPlans',
            'recentTenants',
            'recentInvoices',
            'contactMessages',
            'unreadMessagesCount'
        ));
    }

    /**
     * List all tenants (Active & Soft-Deleted).
     */
    public function tenants(Request $request)
    {
        $tab = $request->input('tab', 'active');
        $trashedCount = Tenant::onlyTrashed()->count();

        $query = ($tab === 'trashed' ? Tenant::onlyTrashed() : Tenant::query())
            ->with(['owner', 'activeSubscription.plan'])
            ->withCount(['orders', 'users', 'customers']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $tenants = $query->latest()->get();
        $plans   = Plan::where('is_active', true)->get();

        return view('saas-admin.tenants', compact('tenants', 'plans', 'search', 'status', 'tab', 'trashedCount'));
    }

    /**
     * Manually activate or extend subscription for a tenant.
     */
    public function extendSubscription(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'plan_id' => ['required', 'exists:plans,id'],
            'months'  => ['required', 'integer', 'min:1', 'max:24'],
        ]);

        $plan = Plan::findOrFail($validated['plan_id']);
        $currentSub = $tenant->activeSubscription;

        if ($currentSub && $currentSub->ends_at && $currentSub->ends_at->isFuture()) {
            $endsAt = $currentSub->ends_at->copy()->addMonths((int) $validated['months']);
        } else {
            $endsAt = now()->addMonths((int) $validated['months']);
        }

        Subscription::create([
            'tenant_id'     => $tenant->id,
            'plan_id'       => $plan->id,
            'billing_cycle' => $validated['months'] >= 12 ? 'yearly' : 'monthly',
            'status'        => 'active',
            'starts_at'     => now(),
            'ends_at'       => $endsAt,
        ]);

        $tenant->update(['status' => 'active']);

        return back()->with('success', "Langganan {$tenant->name} berhasil diperpanjang hingga {$endsAt->format('d M Y')}.");
    }

    /**
     * Toggle suspend status for a tenant.
     */
    public function toggleSuspend(Tenant $tenant)
    {
        if ($tenant->status === 'suspended') {
            $tenant->update(['status' => 'active']);
            $msg = "Akun usaha {$tenant->name} telah diaktifkan kembali.";
        } else {
            $tenant->update(['status' => 'suspended']);
            $msg = "Akun usaha {$tenant->name} telah dinonaktifkan (suspended).";
        }

        return back()->with('success', $msg);
    }

    /**
     * Reset the owner password of a tenant.
     */
    public function resetOwnerPassword(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'new_password' => ['required', 'string', 'min:6'],
        ]);

        $owner = $tenant->owner
            ?? User::withoutGlobalScopes()
                ->where('tenant_id', $tenant->id)
                ->where('role', 'admin')
                ->first();

        if (!$owner) {
            return back()->with('error', "Akun pemilik untuk usaha {$tenant->name} tidak ditemukan.");
        }

        $owner->update([
            'password'           => Hash::make($validated['new_password']),
            'failed_login_count' => 0,
            'locked_until'       => null,
        ]);

        return back()->with('success', "Password akun pemilik {$tenant->name} ({$owner->username}) berhasil direset.");
    }

    /**
     * Soft-delete a tenant.
     */
    public function destroyTenant(Tenant $tenant)
    {
        $name = $tenant->name;
        $tenant->delete();

        return back()->with('success', "Usaha laundry {$name} telah dipindahkan ke tab Terhapus.");
    }

    /**
     * Restore a soft-deleted tenant.
     */
    public function restoreTenant(int $id)
    {
        $tenant = Tenant::onlyTrashed()->findOrFail($id);
        $tenant->restore();

        return redirect()->route('saas-admin.tenants', ['tab' => 'trashed'])
            ->with('success', "Usaha laundry {$tenant->name} berhasil dipulihkan kembali.");
    }

    /**
     * View all billing invoices across the SaaS platform.
     */
    public function invoices(Request $request)
    {
        $query = BillingInvoice::with(['tenant', 'plan', 'payments']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('tenant', fn($tq) => $tq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $invoices    = $query->latest()->get();
        $paidCount   = BillingInvoice::where('status', 'paid')->count();
        $unpaidCount = BillingInvoice::where('status', 'unpaid')->count();

        return view('saas-admin.invoices', compact('invoices', 'search', 'status', 'paidCount', 'unpaidCount'));
    }

    /**
     * Manually mark an unpaid invoice as paid and activate subscription.
     */
    public function markInvoicePaid(Request $request, BillingInvoice $invoice)
    {
        if ($invoice->status === 'paid') {
            return back()->with('info', "Tagihan {$invoice->invoice_number} sudah berstatus lunas.");
        }

        $this->gatewayService->fulfillPaidInvoice(
            $invoice,
            'manual_superadmin',
            'ADM-' . strtoupper(Str::random(8)),
            ['verified_by' => auth()->user()?->username ?? 'superadmin']
        );

        return back()->with('success', "Tagihan {$invoice->invoice_number} berhasil dikonfirmasi lunas & langganan tenant telah diaktifkan.");
    }

    /**
     * List and manage subscription plans (Paket & Harga).
     */
    public function plans()
    {
        $plans = Plan::withCount('subscriptions')->orderBy('price_monthly')->get();

        return view('saas-admin.plans', compact('plans'));
    }

    /**
     * Store a new subscription plan.
     */
    public function storePlan(Request $request)
    {
        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:100'],
            'slug'          => ['nullable', 'string', 'max:100', 'alpha_dash', 'unique:plans,slug'],
            'description'   => ['nullable', 'string', 'max:500'],
            'price_monthly' => ['required', 'numeric', 'min:0'],
            'price_yearly'  => ['required', 'numeric', 'min:0'],
            'max_outlets'   => ['required', 'integer', 'min:1'],
            'max_staff'     => ['required', 'integer', 'min:1'],
            'features_text' => ['nullable', 'string'],
        ]);

        $slug = !empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['name']);
        if (Plan::where('slug', $slug)->exists()) {
            $slug .= '-' . strtolower(Str::random(3));
        }

        $features = collect(preg_split('/\r\n|\r|\n/', (string) ($validated['features_text'] ?? '')))
            ->map(fn($line) => trim($line))
            ->filter()
            ->values()
            ->all();

        Plan::create([
            'name'          => $validated['name'],
            'slug'          => $slug,
            'description'   => $validated['description'] ?? '',
            'price_monthly' => $validated['price_monthly'],
            'price_yearly'  => $validated['price_yearly'],
            'max_outlets'   => $validated['max_outlets'],
            'max_staff'     => $validated['max_staff'],
            'features'      => $features,
            'is_active'     => true,
        ]);

        return redirect()->route('saas-admin.plans')
            ->with('success', "Paket langganan {$validated['name']} berhasil ditambahkan.");
    }

    /**
     * Update an existing subscription plan.
     */
    public function updatePlan(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:100'],
            'description'   => ['nullable', 'string', 'max:500'],
            'price_monthly' => ['required', 'numeric', 'min:0'],
            'price_yearly'  => ['required', 'numeric', 'min:0'],
            'max_outlets'   => ['required', 'integer', 'min:1'],
            'max_staff'     => ['required', 'integer', 'min:1'],
            'features_text' => ['nullable', 'string'],
        ]);

        $features = collect(preg_split('/\r\n|\r|\n/', (string) ($validated['features_text'] ?? '')))
            ->map(fn($line) => trim($line))
            ->filter()
            ->values()
            ->all();

        $plan->update([
            'name'          => $validated['name'],
            'description'   => $validated['description'] ?? '',
            'price_monthly' => $validated['price_monthly'],
            'price_yearly'  => $validated['price_yearly'],
            'max_outlets'   => $validated['max_outlets'],
            'max_staff'     => $validated['max_staff'],
            'features'      => $features,
        ]);

        return redirect()->route('saas-admin.plans')
            ->with('success', "Paket {$plan->name} berhasil diperbarui. Perubahan langsung tampil di halaman Harga & Registrasi.");
    }

    /**
     * Toggle active status of a plan.
     */
    public function togglePlan(Plan $plan)
    {
        $plan->update(['is_active' => !$plan->is_active]);
        $statusLabel = $plan->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Paket {$plan->name} berhasil {$statusLabel}.");
    }

    /**
     * View all contact messages / leads from public website.
     */
    public function messages(Request $request)
    {
        $query = ContactMessage::with('repliedByUser');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('contact', 'like', "%{$search}%")
                  ->orWhere('business_name', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        $filter = $request->input('filter', 'all');
        if ($filter === 'unread') {
            $query->where('is_read', false);
        } elseif ($filter === 'read') {
            $query->where('is_read', true);
        } elseif ($filter === 'unreplied') {
            $query->whereNull('replied_at');
        } elseif ($filter === 'replied') {
            $query->whereNotNull('replied_at');
        }

        $messages       = $query->latest()->paginate(20)->withQueryString();
        $unreadCount    = ContactMessage::where('is_read', false)->count();
        $unrepliedCount = ContactMessage::whereNull('replied_at')->count();
        $repliedCount   = ContactMessage::whereNotNull('replied_at')->count();

        return view('saas-admin.messages', compact(
            'messages',
            'search',
            'filter',
            'unreadCount',
            'unrepliedCount',
            'repliedCount'
        ));
    }

    /**
     * Mark a contact message / lead as read.
     */
    public function markMessageRead(ContactMessage $message)
    {
        $message->update(['is_read' => true]);

        return back()->with('success', 'Pesan dari ' . $message->name . ' telah ditandai sudah dibaca.');
    }

    /**
     * Reply to a public contact message via email and record the reply history.
     */
    public function replyMessage(Request $request, ContactMessage $message)
    {
        $validated = $request->validate([
            'reply_subject' => ['required', 'string', 'max:200'],
            'reply_message' => ['required', 'string', 'max:5000'],
        ], [
            'reply_subject.required' => 'Subjek email balasan wajib diisi.',
            'reply_message.required' => 'Isi pesan balasan wajib diisi.',
        ]);

        $recipientEmail = trim((string) $message->contact);
        if (! filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
            return back()->with('error', "Kontak pengirim ({$recipientEmail}) bukan format alamat email yang valid.");
        }

        $supportEmail = Setting::getPlatform('platform_contact_email', config('mail.from.address', 'support@laundiqo.com'));
        $senderName = config('app.name', 'Laundiqo') . ' Support';
        $escapedReply = nl2br(e($validated['reply_message']));
        $escapedOrig  = nl2br(e($message->message));
        $escapedName  = e($message->name);
        $registerUrl  = route('register');

        $htmlBody = <<<HTML
        <div style="font-family: 'Segoe UI', Arial, sans-serif; max-width: 600px; margin: 0 auto; color: #1e293b; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden;">
            <div style="background: #0B1F3A; padding: 24px 28px; color: #ffffff;">
                <p style="margin: 0; font-size: 11px; letter-spacing: 1.5px; text-transform: uppercase; color: #60a5fa; font-weight: 700;">LAUNDIQO OFFICIAL SUPPORT</p>
                <h2 style="margin: 6px 0 0; font-size: 20px; font-weight: 800;">Halo, {$escapedName}</h2>
            </div>
            <div style="padding: 28px;">
                <div style="font-size: 14px; line-height: 1.7; color: #334155;">
                    {$escapedReply}
                </div>
                <div style="margin-top: 24px; padding-top: 20px; border-top: 1px solid #e2e8f0;">
                    <a href="{$registerUrl}" style="display: inline-block; background: #1E88E5; color: #ffffff; text-decoration: none; font-weight: 700; font-size: 13px; padding: 10px 20px; border-radius: 10px;">
                        Daftar / Coba Laundiqo Sekarang &rarr;
                    </a>
                </div>
                <div style="margin-top: 24px; background: #f8fafc; border-left: 3px solid #94a3b8; padding: 12px 16px; border-radius: 8px; font-size: 12px; color: #64748b;">
                    <p style="margin: 0 0 6px; font-weight: 700; color: #475569;">Pertanyaan Anda sebelumnya:</p>
                    <div>{$escapedOrig}</div>
                </div>
            </div>
            <div style="background: #f1f5f9; padding: 14px 28px; font-size: 11px; color: #64748b; text-align: center;">
                Email ini dikirim oleh Tim Support Laundiqo ({$supportEmail}).
            </div>
        </div>
        HTML;

        try {
            Mail::html($htmlBody, function ($mail) use ($recipientEmail, $message, $validated, $supportEmail, $senderName) {
                $mail->to($recipientEmail, $message->name)
                    ->subject($validated['reply_subject']);

                if (filter_var($supportEmail, FILTER_VALIDATE_EMAIL)) {
                    $mail->replyTo($supportEmail, $senderName);
                }
            });
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal mengirim email ke ' . $recipientEmail . ': ' . $e->getMessage());
        }

        $message->update([
            'is_read'       => true,
            'reply_subject' => $validated['reply_subject'],
            'reply_message' => $validated['reply_message'],
            'replied_at'    => now(),
            'replied_by'    => auth()->id(),
        ]);

        return back()->with('success', "Balasan email berhasil dikirim ke {$message->name} ({$recipientEmail})!");
    }

    /**
     * Delete a contact message.
     */
    public function destroyMessage(ContactMessage $message)
    {
        $sender = $message->name;
        $message->delete();

        return back()->with('success', "Pesan dari {$sender} berhasil dihapus.");
    }

    /**
     * Lightweight Web Profile CMS settings form.
     */
    public function cms()
    {
        $cms = [
            'hero_badge'       => Setting::getPlatform('platform_hero_badge', 'SISTEM MANAJEMEN LAUNDRY MODERN'),
            'hero_title'       => Setting::getPlatform('platform_hero_title', 'Kelola Operasional Laundry Lebih Rapi, Cepat, dan Terukur'),
            'hero_subtitle'    => Setting::getPlatform('platform_hero_subtitle', 'Catat order kasir, pantau posisi rak dan antrean cucian operator, kirim nota WhatsApp, hingga rekap laporan keuangan harian dalam satu sistem berbasis web.'),
            'trial_days'       => Setting::getPlatform('platform_trial_days', '14'),
            'tagline'          => Setting::getPlatform('platform_tagline', 'Laundiqo — Kelola Laundry, Lebih Mudah.'),
            'contact_email'    => Setting::getPlatform('platform_contact_email', 'support@laundiqo.com'),
            'contact_whatsapp' => Setting::getPlatform('platform_contact_whatsapp', '0812-3456-7890'),
            'contact_hours'    => Setting::getPlatform('platform_contact_hours', 'Senin–Jumat, 09.00–17.00 WIB'),
            'contact_address'  => Setting::getPlatform('platform_contact_address', 'Indonesia'),
            'faqs'             => Setting::getPlatformFaqs(),
        ];

        return view('saas-admin.cms', compact('cms'));
    }

    /**
     * Save Web Profile CMS settings.
     */
    public function updateCms(Request $request)
    {
        $validated = $request->validate([
            'hero_badge'          => ['required', 'string', 'max:120'],
            'hero_title'          => ['required', 'string', 'max:255'],
            'hero_subtitle'       => ['required', 'string', 'max:600'],
            'trial_days'          => ['required', 'integer', 'min:1', 'max:90'],
            'tagline'             => ['required', 'string', 'max:255'],
            'contact_email'       => ['nullable', 'string', 'max:120'],
            'contact_whatsapp'    => ['nullable', 'string', 'max:50'],
            'contact_hours'       => ['required', 'string', 'max:150'],
            'contact_address'     => ['nullable', 'string', 'max:255'],
            'faq_questions'       => ['nullable', 'array'],
            'faq_questions.*'     => ['nullable', 'string', 'max:255'],
            'faq_answers'         => ['nullable', 'array'],
            'faq_answers.*'       => ['nullable', 'string', 'max:1000'],
        ]);

        Setting::setPlatform('platform_hero_badge', $validated['hero_badge']);
        Setting::setPlatform('platform_hero_title', $validated['hero_title']);
        Setting::setPlatform('platform_hero_subtitle', $validated['hero_subtitle']);
        Setting::setPlatform('platform_trial_days', (string) $validated['trial_days']);
        Setting::setPlatform('platform_tagline', $validated['tagline']);
        Setting::setPlatform('platform_contact_email', $validated['contact_email'] ?? '');
        Setting::setPlatform('platform_contact_whatsapp', $validated['contact_whatsapp'] ?? '');
        Setting::setPlatform('platform_contact_hours', $validated['contact_hours']);
        Setting::setPlatform('platform_contact_address', $validated['contact_address'] ?? '');

        $faqs = [];
        $questions = $validated['faq_questions'] ?? [];
        $answers   = $validated['faq_answers'] ?? [];

        foreach ($questions as $idx => $qText) {
            $qClean = trim((string) $qText);
            $aClean = trim((string) ($answers[$idx] ?? ''));
            if ($qClean !== '' && $aClean !== '') {
                $faqs[] = [
                    'question' => $qClean,
                    'answer'   => $aClean,
                ];
            }
        }

        if (!empty($faqs)) {
            Setting::setPlatform('platform_faqs', json_encode($faqs, JSON_UNESCAPED_UNICODE));
        }

        return redirect()->route('saas-admin.cms')
            ->with('success', 'Konten Web Profile (Hero, Kontak, Trial & FAQ) berhasil disimpan dan langsung aktif di halaman publik!');
    }
}
