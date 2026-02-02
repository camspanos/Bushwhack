<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentBatchItem extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'pending';
    const STATUS_SUCCEEDED = 'succeeded';
    const STATUS_FAILED = 'failed';

    protected $fillable = [
        'payment_batch_id',
        'user_id',
        'subscription_id',
        'payment_transaction_id',
        'amount',
        'status',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
        ];
    }

    /**
     * Get the payment batch.
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(PaymentBatch::class, 'payment_batch_id');
    }

    /**
     * Get the user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the subscription.
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Get the payment transaction.
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(PaymentTransaction::class, 'payment_transaction_id');
    }

    /**
     * Mark the item as successful.
     */
    public function markSuccessful(PaymentTransaction $transaction): void
    {
        $this->update([
            'status' => self::STATUS_SUCCEEDED,
            'payment_transaction_id' => $transaction->id,
        ]);
    }

    /**
     * Mark the item as failed.
     */
    public function markFailed(string $errorMessage, ?PaymentTransaction $transaction = null): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $errorMessage,
            'payment_transaction_id' => $transaction?->id,
        ]);
    }
}

