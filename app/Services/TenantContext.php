<?php

namespace App\Services;

use App\Models\Tenant;

class TenantContext
{
    private static ?Tenant $tenant = null;
    private static ?int $tenantId = null;

    public static function setTenant(?Tenant $tenant): void
    {
        self::$tenant = $tenant;
        self::$tenantId = $tenant?->id;
    }

    public static function setTenantId(?int $tenantId): void
    {
        self::$tenantId = $tenantId;
        self::$tenant = null; // Always reset cached instance so fresh record is loaded
    }

    public static function getTenant(): ?Tenant
    {
        $id = self::getTenantId();
        if ($id !== null) {
            if (self::$tenant === null || self::$tenant->id !== $id) {
                self::$tenant = Tenant::find($id);
            }
        } else {
            self::$tenant = null;
        }

        return self::$tenant;
    }

    public static function getTenantId(): ?int
    {
        if (self::$tenantId === null && auth()->check() && auth()->user()->tenant_id) {
            self::$tenantId = (int) auth()->user()->tenant_id;
        }
        return self::$tenantId;
    }

    public static function hasTenant(): bool
    {
        return self::getTenantId() !== null;
    }

    public static function clear(): void
    {
        self::$tenant = null;
        self::$tenantId = null;
    }
}
