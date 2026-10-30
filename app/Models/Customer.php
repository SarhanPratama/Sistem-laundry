<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'phone_number',
        'address',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Sensor nama pelanggan untuk tampilan publik (contoh: "Budi Santoso" -> "Bu** Sa****")
     */
    public function getMaskedNameAttribute(): string
    {
        $name = trim($this->full_name ?? '');
        if (empty($name)) {
            return '-';
        }

        $parts = explode(' ', $name);
        $maskedParts = array_map(function ($part) {
            $len = mb_strlen($part);
            if ($len <= 2) {
                return $part;
            }
            $visible = mb_substr($part, 0, 2);
            return $visible . str_repeat('*', max(2, $len - 2));
        }, $parts);

        return implode(' ', $maskedParts);
    }

    /**
     * Sensor nomor telepon untuk tampilan publik (contoh: "081234567890" -> "0812-****-7890")
     */
    public function getMaskedPhoneAttribute(): string
    {
        $phone = preg_replace('/[^0-9]/', '', (string)$this->phone_number);
        $len = strlen($phone);

        if ($len < 7) {
            return str_repeat('*', $len);
        }

        $start = substr($phone, 0, 4);
        $end = substr($phone, -4);
        return $start . '-****-' . $end;
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
