<?php

namespace App\Models;

use App\Services\TenantContext;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'key', 'value'];

    /**
     * Ambil nilai setting berdasarkan key dengan fallback default
     */
    public static function get(string $key, ?string $default = null): ?string
    {
        $tenantId = TenantContext::getTenantId() ?? 0;
        try {
            return Cache::remember("tenant_{$tenantId}_setting_{$key}", 3600, function () use ($key, $default) {
                $setting = static::where('key', $key)->first();
                return $setting ? $setting->value : $default;
            });
        } catch (\Throwable $e) {
            return $default;
        }
    }

    /**
     * Simpan / perbarui nilai setting
     */
    public static function set(string $key, ?string $value): void
    {
        $tenantId = TenantContext::getTenantId();
        $cachePrefix = $tenantId ?? 0;
        static::updateOrCreate(
            ['tenant_id' => $tenantId, 'key' => $key],
            ['value' => $value]
        );
        Cache::forget("tenant_{$cachePrefix}_setting_{$key}");
    }

    /**
     * Ambil nilai setting global platform (CMS Web Profile) dengan fallback default
     */
    public static function getPlatform(string $key, ?string $default = null): ?string
    {
        try {
            return Cache::remember("platform_setting_{$key}", 3600, function () use ($key, $default) {
                $setting = static::withoutGlobalScopes()
                    ->whereNull('tenant_id')
                    ->where('key', $key)
                    ->first();

                return ($setting && $setting->value !== null && $setting->value !== '')
                    ? $setting->value
                    : $default;
            });
        } catch (\Throwable $e) {
            return $default;
        }
    }

    /**
     * Simpan / perbarui nilai setting global platform (CMS Web Profile)
     */
    public static function setPlatform(string $key, ?string $value): void
    {
        static::withoutGlobalScopes()->updateOrCreate(
            ['tenant_id' => null, 'key' => $key],
            ['value' => $value]
        );
        Cache::forget("platform_setting_{$key}");
    }

    /**
     * Ambil daftar FAQ untuk halaman publik dengan fallback default
     */
    public static function getPlatformFaqs(): array
    {
        $defaultFaqs = [
            ['question' => 'Apa itu Laundiqo?', 'answer' => 'Laundiqo adalah aplikasi online untuk mengelola operasional usaha laundry, mulai dari pencatatan order hingga laporan.'],
            ['question' => 'Apakah saya perlu menginstal aplikasi?', 'answer' => 'Laundiqo dapat diakses melalui browser pada perangkat yang terhubung internet.'],
            ['question' => 'Apakah bisa digunakan oleh kasir dan operator?', 'answer' => 'Ya, Laundiqo memiliki pembagian akses berdasarkan peran pengguna, dengan ketentuan sesuai paket.'],
            ['question' => 'Apakah pelanggan bisa mengecek status cucian?', 'answer' => 'Tersedia halaman tracking menggunakan nomor order dengan informasi yang dibatasi sesuai pengaturan sistem.'],
            ['question' => 'Bagaimana cara pembayaran langganan?', 'answer' => 'Pilihan paket dan pembayaran otomatis via Payment Gateway (QRIS / Virtual Account) tersedia langsung saat mendaftar maupun di menu Langganan.'],
            ['question' => 'Apa yang terjadi jika langganan habis?', 'answer' => 'Akses mengikuti status langganan dan kebijakan yang diterapkan pada akun usaha. Anda tetap dapat menggunakan jalur ekspor data yang tersedia.'],
            ['question' => 'Bagaimana jika butuh bantuan?', 'answer' => 'Gunakan formulir kontak Laundiqo atau hubungi tim kami untuk menyampaikan pertanyaan dan kebutuhan usaha Anda.'],
        ];

        $raw = static::getPlatform('platform_faqs');
        if (!$raw) {
            return $defaultFaqs;
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded) || empty($decoded)) {
            return $defaultFaqs;
        }

        return $decoded;
    }
}
