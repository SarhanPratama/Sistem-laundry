<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'owner_id',
        'status', // trial, active, past_due, grace_period, suspended
        'phone',
        'address',
        'trial_ends_at',
    ];

    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'datetime',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    public function currentPlan(): ?Plan
    {
        return $this->activeSubscription?->plan;
    }

    public function billingInvoices(): HasMany
    {
        return $this->hasMany(BillingInvoice::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    /**
     * Generate a unique, human-readable order number prefix for this tenant.
     * Example: "Berkah Laundry" (ID #2) -> "BER02", "Laundry Mawar" (ID #1) -> "MAW01"
     */
    public function getOrderPrefixAttribute(): string
    {
        $cleaned = preg_replace('/\blaundry\b/i', '', (string) $this->name);
        $lettersOnly = strtoupper(preg_replace('/[^a-zA-Z]/', '', $cleaned ?: (string) $this->name));
        $code = substr(str_pad($lettersOnly ?: 'LDR', 3, 'X'), 0, 3);
        $idPart = str_pad((string) ($this->id ?? 0), 2, '0', STR_PAD_LEFT);

        return $code . $idPart;
    }

    /**
     * Check if tenant is currently in active trial.
     */
    public function isInTrial(): bool
    {
        return $this->status === 'trial' && $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    /**
     * Check if tenant has an active paid subscription.
     */
    public function hasActivePaidSubscription(): bool
    {
        $sub = $this->activeSubscription;
        return $sub && $sub->status === 'active' && ($sub->ends_at === null || $sub->ends_at->isFuture());
    }

    /**
     * Check if tenant is within 7-day grace period.
     */
    public function isInGracePeriod(): bool
    {
        $expiry = $this->activeSubscription?->ends_at ?? $this->trial_ends_at;
        if (!$expiry) {
            return false;
        }

        // Grace period is up to 7 days after expiry
        return $expiry->isPast() && $expiry->copy()->addDays(7)->isFuture();
    }

    /**
     * Get the unpaid initial invoice if the tenant registered with direct_pay
     * and has not yet completed their first payment or started a trial.
     */
    public function pendingInitialInvoice(): ?BillingInvoice
    {
        if ($this->hasActivePaidSubscription() || $this->isInTrial()) {
            return null;
        }

        // Only lock to the invoice checkout if the tenant has never paid an invoice before
        // (i.e. initial onboarding direct_pay awaiting payment).
        if ($this->billingInvoices()->where('status', 'paid')->exists()) {
            return null;
        }

        return $this->billingInvoices()
            ->where('status', 'unpaid')
            ->latest('id')
            ->first();
    }

    /**
     * Check if tenant is past grace period (enters read-only mode).
     */
    public function isReadOnly(): bool
    {
        if ($this->hasActivePaidSubscription()) {
            return false;
        }

        if ($this->isInTrial()) {
            return false;
        }

        // If beyond grace period, it's read-only
        return true;
    }

    /**
     * Days remaining for trial or active subscription.
     */
    public function daysRemaining(): int
    {
        $expiry = $this->activeSubscription?->ends_at ?? $this->trial_ends_at;
        if (!$expiry || $expiry->isPast()) {
            return 0;
        }

        return (int) now()->diffInDays($expiry, false);
    }
}

