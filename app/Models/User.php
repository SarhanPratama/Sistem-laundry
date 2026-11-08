<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'username',
        'password',
        'role',
        'is_superadmin',
        'is_active',
        'last_login',
        'failed_login_count',
        'locked_until',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_superadmin' => 'boolean',
            'is_active' => 'boolean',
            'last_login' => 'datetime',
            'locked_until' => 'datetime',
        ];
    }

    public function tenant(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function isSuperAdmin(): bool
    {
        return (bool) $this->is_superadmin;
    }

    public function hasTenantPermission(string $permission): bool
    {
        if ($this->isSuperAdmin()) {
            return false;
        }

        if ($this->role === 'admin') {
            return true;
        }

        $role = TenantRole::query()
            ->where('tenant_id', $this->tenant_id)
            ->where('slug', $this->role)
            ->first();

        if ($role) {
            return in_array($permission, $role->permissions ?? [], true);
        }

        $legacyPermissions = [
            'kasir' => ['view-dashboard', 'manage-customers', 'manage-orders', 'manage-payments', 'update-order-status', 'export-data'],
            'operator' => ['view-dashboard', 'manage-orders', 'update-order-status', 'manage-racks'],
        ];

        return in_array($permission, $legacyPermissions[$this->role] ?? [], true);
    }

    public function tenantRole(): ?TenantRole
    {
        return TenantRole::query()
            ->where('tenant_id', $this->tenant_id)
            ->where('slug', $this->role)
            ->first();
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'created_by');
    }

    public function completedOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'completed_by');
    }
}
