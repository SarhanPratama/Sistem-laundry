<?php

namespace App\Http\Controllers;

use App\Models\TenantRole;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TenantRoleController extends Controller
{
    public const PERMISSIONS = [
        'view-dashboard' => 'Melihat dashboard',
        'manage-customers' => 'Mengelola pelanggan',
        'manage-orders' => 'Mengelola order',
        'manage-payments' => 'Mengelola pembayaran',
        'update-order-status' => 'Memperbarui status order',
        'manage-racks' => 'Mengelola rak',
        'manage-services' => 'Mengelola layanan dan kategori',
        'view-reports' => 'Melihat dan mengekspor laporan',
        'manage-users' => 'Mengelola pengguna',
        'manage-settings' => 'Mengelola pengaturan usaha',
        'export-data' => 'Mengekspor data usaha',
    ];

    public static function ensureDefaults(int $tenantId): void
    {
        $defaults = [
            'kasir' => ['Kasir', ['view-dashboard', 'manage-customers', 'manage-orders', 'manage-payments', 'update-order-status', 'export-data']],
            'operator' => ['Operator', ['view-dashboard', 'manage-orders', 'update-order-status', 'manage-racks']],
        ];

        foreach ($defaults as $slug => [$name, $permissions]) {
            TenantRole::firstOrCreate(
                ['tenant_id' => $tenantId, 'slug' => $slug],
                ['name' => $name, 'permissions' => $permissions, 'is_system' => true],
            );
        }
    }

    public function index()
    {
        $roles = TenantRole::where('tenant_id', TenantContext::getTenantId())->orderBy('name')->get();

        return view('tenant-roles.index', [
            'roles' => $roles,
            'permissions' => self::PERMISSIONS,
        ]);
    }

    public function create()
    {
        return view('tenant-roles.form', [
            'role' => null,
            'permissions' => self::PERMISSIONS,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateRole($request);
        $tenantId = TenantContext::getTenantId();

        TenantRole::create([
            'tenant_id' => $tenantId,
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'permissions' => $validated['permissions'] ?? [],
        ]);

        return redirect()->route('tenant-roles.index')->with('success', 'Role berhasil dibuat.');
    }

    public function edit(TenantRole $tenantRole)
    {
        $this->ensureCurrentTenant($tenantRole);

        return view('tenant-roles.form', [
            'role' => $tenantRole,
            'permissions' => self::PERMISSIONS,
        ]);
    }

    public function update(Request $request, TenantRole $tenantRole)
    {
        $this->ensureCurrentTenant($tenantRole);

        $validated = $this->validateRole($request);
        $tenantRole->update([
            'name' => $validated['name'],
            'permissions' => $validated['permissions'] ?? [],
        ]);

        return redirect()->route('tenant-roles.index')->with('success', 'Role berhasil diperbarui.');
    }

    public function destroy(TenantRole $tenantRole)
    {
        $this->ensureCurrentTenant($tenantRole);
        abort_if($tenantRole->is_system, 403, 'Role bawaan tidak dapat dihapus.');

        if ($tenantRole->tenant->users()->where('role', $tenantRole->slug)->exists()) {
            return back()->with('error', 'Role masih digunakan oleh pengguna.');
        }

        $tenantRole->delete();

        return redirect()->route('tenant-roles.index')->with('success', 'Role berhasil dihapus.');
    }

    private function validateRole(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'in:' . implode(',', array_keys(self::PERMISSIONS))],
        ]);
    }

    private function ensureCurrentTenant(TenantRole $role): void
    {
        abort_unless($role->tenant_id === TenantContext::getTenantId(), 404);
    }
}
