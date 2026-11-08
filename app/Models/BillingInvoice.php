<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BillingInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'tenant_id',
        'subscription_id',
        'plan_id',
        'billing_cycle',
        'amount',
        'status', // unpaid, paid, expired, cancelled
        'due_at',
        'paid_at',
        'payment_method',
        'snap_token',
        'payment_url',
    ];

    protected function casts(): array
    {
        return [
            'amount'  => 'decimal:2',
            'due_at'  => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class)->withTrashed();
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(BillingPayment::class, 'invoice_id');
    }
}

