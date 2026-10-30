<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'customer_id',
        'created_by',
        'status',
        'payment_status',
        'rack_id',
        'total_cost',
        'amount_paid',
        'received_date',
        'estimated_done_date',
        'ready_at',
        'completed_at',
        'completed_by',
        'is_cancelled',
        'cancelled_at',
        'cancelled_by',
    ];

    protected function casts(): array
    {
        return [
            'received_date' => 'date',
            'estimated_done_date' => 'date',
            'ready_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'total_cost' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'is_cancelled' => 'boolean',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(OrderStatusLog::class)->orderBy('changed_at');
    }

    public function rack(): BelongsTo
    {
        return $this->belongsTo(Rack::class, 'rack_id');
    }

    /**
     * Total cash handed over by the customer across all payments.
     */
    public function getTotalTenderedAttribute(): float
    {
        return (float) $this->payments->sum('amount');
    }

    /**
     * Total change given back across all payments.
     */
    public function getTotalChangeAttribute(): float
    {
        return (float) $this->payments->sum('change_amount');
    }

    /**
     * Outstanding balance, never negative.
     */
    public function getRemainingAmountAttribute(): float
    {
        return max(0.0, (float) $this->total_cost - (float) $this->amount_paid);
    }
}
