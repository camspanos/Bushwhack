<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    use HasFactory;

    const STATUS_ACTIVE = 'active';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_EXPIRED = 'expired';
    const STATUS_PAST_DUE = 'past_due';

    protected $fillable = [
        'user_id',
        'subscription_plan_id',
        'status',
        'starts_at',
        'ends_at',
        'next_billing_date',
        'cancelled_at',
        'cancellation_reason',
        'external_id',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'next_billing_date' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the subscription.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the subscription plan.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    /**
     * Get the payment transactions for this subscription.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    /**
     * Check if the subscription is active.
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if the subscription is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Check if the subscription is a lifetime subscription.
     */
    public function isLifetime(): bool
    {
        return $this->plan->isLifetime();
    }

    /**
     * Check if the subscription is due for billing.
     */
    public function isDueForBilling(): bool
    {
        if ($this->isLifetime()) {
            return false;
        }

        return $this->next_billing_date && $this->next_billing_date->lte(now());
    }

    /**
     * Cancel the subscription.
     */
    public function cancel(?string $reason = null): void
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);
    }

    /**
     * Scope to get active subscriptions.
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope to get subscriptions due for billing.
     */
    public function scopeDueForBilling($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)
            ->whereNotNull('next_billing_date')
            ->where('next_billing_date', '<=', now());
    }
}

