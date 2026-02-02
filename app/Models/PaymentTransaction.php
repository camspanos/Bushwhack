<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    use HasFactory;

    const TYPE_CHARGE = 'charge';
    const TYPE_REFUND = 'refund';

    const STATUS_PENDING = 'pending';
    const STATUS_SUCCEEDED = 'succeeded';
    const STATUS_FAILED = 'failed';
    const STATUS_REFUNDED = 'refunded';

    protected $fillable = [
        'user_id',
        'subscription_id',
        'type',
        'amount',
        'currency',
        'status',
        'payment_method',
        'external_id',
        'external_payment_method_id',
        'gateway_response',
        'failure_reason',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'gateway_response' => 'array',
        ];
    }

    /**
     * Get the user that owns the transaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the subscription for this transaction.
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Get the formatted amount (in dollars).
     */
    public function getFormattedAmountAttribute(): string
    {
        return '$' . number_format($this->amount / 100, 2);
    }

    /**
     * Check if the transaction succeeded.
     */
    public function isSuccessful(): bool
    {
        return $this->status === self::STATUS_SUCCEEDED;
    }

    /**
     * Check if the transaction failed.
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Scope to get successful transactions.
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', self::STATUS_SUCCEEDED);
    }

    /**
     * Scope to get failed transactions.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }
}

