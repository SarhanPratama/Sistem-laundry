<?php

namespace App\Http\Controllers;

use App\Models\BillingInvoice;
use App\Models\Plan;
use App\Models\Rack;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Setting;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use App\Http\Controllers\TenantRoleController;
use App\Services\PaymentGatewayService;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    public function __construct(
        private PaymentGatewayService $gatewayService
    ) {}

    public function showRegistrationForm(Request $request)
    {
        $plans = Plan::where('is_active', true)->get();
        $selectedPlan = $request->query('plan', 'business');
        $selectedCycle = in_array($request->query('cycle'), ['monthly', 'yearly'], true)
            ? $request->query('cycle')
            : 'monthly';

        return view('auth.register', compact('plans', 'selectedPlan', 'selectedCycle'));
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'username'        => ['required', 'string', 'max:50', 'alpha_dash', 'unique:users,username'],
            'email'           => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone'           => ['required', 'string', 'max:20'],
            'password'        => ['required', 'string', 'min:6', 'confirmed'],
            'laundry_name'    => ['required', 'string', 'max:255'],
            'laundry_address' => ['nullable', 'string', 'max:500'],
            'plan'              => ['nullable', 'string', 'exists:plans,slug'],
            'billing_cycle'     => ['nullable', 'in:monthly,yearly'],
            'subscription_mode' => ['nullable', 'in:trial,direct_pay'],
        ], [
            'username.unique' => 'Username ini sudah digunakan, silakan pilih username lain.',
            'email.unique'    => 'Email ini sudah terdaftar dalam sistem.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min'    => 'Password minimal 6 karakter.',
        ]);

        $createdInvoice = null;
        $chosenPlanName = 'Business';

        $user = DB::transaction(function () use ($validated, &$createdInvoice, &$chosenPlanName) {
            // 1. Generate unique slug for the tenant
            $baseSlug = Str::slug($validated['laundry_name']);
            if (empty($baseSlug)) {
                $baseSlug = 'laundry';
            }
            $slug = $baseSlug;
            $counter = 1;
            while (Tenant::withTrashed()->where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . (++$counter);
            }

            $isDirectPay = ($validated['subscription_mode'] ?? 'trial') === 'direct_pay';

            // 2. Create Tenant
            $tenant = Tenant::create([
                'name'          => $validated['laundry_name'],
                'slug'          => $slug,
                'phone'         => $validated['phone'],
                'address'       => $validated['laundry_address'],
                'status'        => $isDirectPay ? 'past_due' : 'trial',
                'trial_ends_at' => $isDirectPay ? null : now()->addDays(14),
            ]);

            // 3. Create Owner User
            $user = User::create([
                'tenant_id'          => $tenant->id,
                'name'               => $validated['name'],
                'username'           => $validated['username'],
                'email'              => $validated['email'],
                'password'           => Hash::make($validated['password']),
                'role'               => 'admin',
                'is_superadmin'      => false,
                'is_active'          => true,
                'failed_login_count' => 0,
            ]);

            // Assign Spatie admin role
            $user->assignRole('admin');
            TenantRoleController::ensureDefaults($tenant->id);

            // Set tenant owner
            $tenant->update(['owner_id' => $user->id]);

            // 4. Create Subscription (Trial or Pending Direct Payment)
            $planSlug = $validated['plan'] ?? 'business';
            $chosenPlan = Plan::where('slug', $planSlug)->first()
                ?? Plan::where('slug', 'business')->first()
                ?? Plan::first();
            $billingCycle = $validated['billing_cycle'] ?? 'monthly';
            $subscription = null;

            if ($chosenPlan) {
                $chosenPlanName = $chosenPlan->name;
                $subscription = Subscription::create([
                    'tenant_id'     => $tenant->id,
                    'plan_id'       => $chosenPlan->id,
                    'billing_cycle' => $billingCycle,
                    'status'        => $isDirectPay ? 'past_due' : 'trial',
                    'starts_at'     => now(),
                    'trial_ends_at' => $isDirectPay ? null : now()->addDays(14),
                    'ends_at'       => $isDirectPay ? null : now()->addDays(14),
                ]);

                if ($isDirectPay) {
                    $amount = $billingCycle === 'yearly' ? $chosenPlan->price_yearly : $chosenPlan->price_monthly;
                    $createdInvoice = BillingInvoice::create([
                        'invoice_number'  => 'INV-' . date('Ymd') . '-' . strtoupper(Str::random(5)),
                        'tenant_id'       => $tenant->id,
                        'subscription_id' => $subscription->id,
                        'plan_id'         => $chosenPlan->id,
                        'billing_cycle'   => $billingCycle,
                        'amount'          => $amount,
                        'status'          => 'unpaid',
                        'due_at'          => now()->addDays(3),
                    ]);
                }
            }

            // 5. Seed Starter Data for this Tenant
            TenantContext::setTenant($tenant);

            $catKiloan = ServiceCategory::create([
                'tenant_id'   => $tenant->id,
                'name'        => 'Kiloan',
                'description' => 'Layanan cuci berdasarkan berat pakaian (kg)',
                'is_active'   => true,
            ]);

            $catSatuan = ServiceCategory::create([
                'tenant_id'   => $tenant->id,
                'name'        => 'Satuan',
                'description' => 'Layanan cuci satuan / per helai pakaian',
                'is_active'   => true,
            ]);

            Service::create([
                'tenant_id'      => $tenant->id,
                'category_id'    => $catKiloan->id,
                'name'           => 'Cuci Komplit Reguler',
                'unit'           => 'kg',
                'price_per_unit' => 7000,
                'is_active'      => true,
            ]);

            Service::create([
                'tenant_id'      => $tenant->id,
                'category_id'    => $catKiloan->id,
                'name'           => 'Cuci Kering Lipat',
                'unit'           => 'kg',
                'price_per_unit' => 5000,
                'is_active'      => true,
            ]);

            Service::create([
                'tenant_id'      => $tenant->id,
                'category_id'    => $catSatuan->id,
                'name'           => 'Bed Cover Sedang',
                'unit'           => 'item',
                'price_per_unit' => 25000,
                'is_active'      => true,
            ]);

            Rack::create([
                'tenant_id'            => $tenant->id,
                'name'                 => 'A1',
                'location_description' => 'Rak A Baris 1',
                'is_active'            => true,
            ]);

            Rack::create([
                'tenant_id'            => $tenant->id,
                'name'                 => 'A2',
                'location_description' => 'Rak A Baris 2',
                'is_active'            => true,
            ]);

            Setting::set('store_name', $tenant->name);
            Setting::set('store_phone', $tenant->phone ?? '');
            Setting::set('store_address', $tenant->address ?? '');

            TenantContext::clear();

            return $user;
        });

        // 6. Log the user in
        auth()->login($user);

        if ($createdInvoice) {
            $this->gatewayService->ensureSnapToken($createdInvoice);

            return redirect()->route('billing.invoice.show', $createdInvoice)
                ->with('success', "Pendaftaran berhasil! Silakan selesaikan pembayaran Paket {$chosenPlanName} melalui Payment Gateway otomatis.");
        }

        return redirect()->route('dashboard')
            ->with('success', "Pendaftaran berhasil! Selamat datang di Laundiqo. Akun usaha Anda aktif dengan 14 Hari Trial Gratis Paket {$chosenPlanName}.");
    }
}
