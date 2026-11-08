<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'plan_id',
        'billing_cycle',
        'status', // trial, active, past_due, cancelled
        'starts_at',
        'ends_at',
        'trial_ends_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'starts_at'     => 'datetime',
            'ends_at'       => 'datetime',
            'trial_ends_at' => 'datetime',
            'cancelled_at'  => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(BillingInvoice::class);
    }
}

