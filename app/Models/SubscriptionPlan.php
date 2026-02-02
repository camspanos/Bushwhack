<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'billing_interval',
        'billing_interval_count',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'billing_interval_count' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Get the subscriptions for this plan.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Get the formatted price (in dollars).
     */
    public function getFormattedPriceAttribute(): string
    {
        return '$' . number_format($this->price / 100, 2);
    }

    /**
     * Check if this is a lifetime plan.
     */
    public function isLifetime(): bool
    {
        return $this->billing_interval === 'lifetime';
    }

    /**
     * Get the billing period description.
     */
    public function getBillingPeriodAttribute(): string
    {
        return match ($this->billing_interval) {
            'month' => 'per month',
            'year' => 'per year',
            'lifetime' => 'one-time',
            default => $this->billing_interval,
        };
    }

    /**
     * Scope to get only active plans.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}

