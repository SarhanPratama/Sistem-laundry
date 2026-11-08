<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Services\TenantContext;
use App\Models\TenantRole;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = TenantContext::getTenantId();
        $showTrashed = $request->boolean('trashed');
        $trashedCount = User::onlyTrashed()->where('tenant_id', $tenantId)->count();

        $query = $showTrashed ? User::onlyTrashed() : User::query();
        $users = $query->where('tenant_id', $tenantId)
            ->latest($showTrashed ? 'deleted_at' : 'created_at')
            ->get();

        $tenantRoles = TenantRole::where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get();

        return view('users.index', compact('users', 'tenantRoles', 'showTrashed', 'trashedCount'));
    }

    public function create()
    {
        return view('users.create', [
            'tenantRoles' => TenantRole::where('tenant_id', TenantContext::getTenantId())->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        if ($tenant = TenantContext::getTenant()) {
            $maxStaff = $tenant->currentPlan()?->max_staff ?? 5;
            $currentStaffCount = User::where('tenant_id', $tenant->id)
                ->when($tenant->owner_id, fn ($q) => $q->where('id', '!=', $tenant->owner_id))
                ->count();

            if ($currentStaffCount >= $maxStaff) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', "Kuota akun staf untuk paket Anda ({$maxStaff} akun staf) telah penuh. Silakan upgrade paket langganan Anda.");
            }
        }

        $request->validate([
            'username' => 'required|unique:users',
            'password' => 'required|min:8|confirmed',
            'role'     => ['required', 'string', function ($attribute, $value, $fail) {
                if ($value !== 'admin' && !TenantRole::where('tenant_id', TenantContext::getTenantId())->where('slug', $value)->exists()) {
                    $fail('Role yang dipilih tidak tersedia di usaha ini.');
                }
            }],
        ]);

        $user = User::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
            'tenant_id' => TenantContext::getTenantId(),
        ]);

        if (in_array($request->role, ['admin', 'kasir', 'operator'], true)) {
            $user->assignRole($request->role);
        }

        return redirect()->route('users.index')
            ->with('success', 'Akun pengguna berhasil dibuat.');
    }

    public function edit(User $user)
    {
        $this->ensureSameTenant($user);

        return view('users.edit', [
            'user' => $user,
            'tenantRoles' => TenantRole::where('tenant_id', TenantContext::getTenantId())->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $this->ensureSameTenant($user);

        $request->validate([
            'username' => 'required|unique:users,username,' . $user->id,
            'role'     => ['required', 'string', function ($attribute, $value, $fail) {
                if ($value !== 'admin' && !TenantRole::where('tenant_id', TenantContext::getTenantId())->where('slug', $value)->exists()) {
                    $fail('Role yang dipilih tidak tersedia di usaha ini.');
                }
            }],
            'password' => 'nullable|min:8|confirmed',
        ]);

        $user->username = $request->username;
        $user->role = $request->role;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();
        if (in_array($request->role, ['admin', 'kasir', 'operator'], true)) {
            $user->syncRoles([$request->role]);
        } else {
            $user->syncRoles([]);
        }

        return redirect()->route('users.index')
            ->with('success', 'Akun pengguna berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        $this->ensureSameTenant($user);

        if ($user->id === auth()->id()) {
            return redirect()->back()
                ->with('error', 'Tidak dapat menghapus akun sendiri.');
        }

        DB::table('sessions')
            ->where('user_id', $user->id)
            ->delete();

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Akun pengguna berhasil dipindahkan ke tab Terhapus.');
    }

    public function restore($id)
    {
        $tenantId = TenantContext::getTenantId();
        $user = User::onlyTrashed()
            ->where('tenant_id', $tenantId)
            ->findOrFail($id);

        if ($tenant = TenantContext::getTenant()) {
            $maxStaff = $tenant->currentPlan()?->max_staff ?? 5;
            $currentStaffCount = User::where('tenant_id', $tenant->id)
                ->when($tenant->owner_id, fn ($q) => $q->where('id', '!=', $tenant->owner_id))
                ->count();

            if ($currentStaffCount >= $maxStaff) {
                return redirect()->back()
                    ->with('error', "Tidak dapat memulihkan akun staf karena kuota paket Anda ({$maxStaff} akun staf) telah penuh.");
            }
        }

        if (User::where('username', $user->username)->exists()) {
            return redirect()->back()
                ->with('error', "Tidak dapat memulihkan akun karena username \"{$user->username}\" sudah digunakan oleh pengguna aktif lain.");
        }

        $user->restore();

        return redirect()->route('users.index', ['trashed' => 1])
            ->with('success', "Akun pengguna {$user->username} berhasil dipulihkan.");
    }

    public function deactivate(User $user)
    {
        $this->ensureSameTenant($user);

        if ($user->id === auth()->id()) {
            return redirect()->back()
                ->with('error', 'Tidak dapat menonaktifkan akun sendiri.');
        }

        $user->is_active = false;
        $user->save();

        DB::table('sessions')
            ->where('user_id', $user->id)
            ->delete();

        return redirect()->route('users.index')
            ->with('success', 'Akun pengguna berhasil dinonaktifkan.');
    }

    private function ensureSameTenant(User $user): void
    {
        if ($user->tenant_id !== TenantContext::getTenantId()) {
            abort(404, 'Pengguna tidak ditemukan pada usaha Anda.');
        }
    }
}
