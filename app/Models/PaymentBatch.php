<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentBatch extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'pending';
    const STATUS_RUNNING = 'running';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    protected $fillable = [
        'batch_id',
        'run_date',
        'total_processed',
        'total_successful',
        'total_failed',
        'total_amount_processed',
        'total_amount_successful',
        'status',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'run_date' => 'date',
            'total_processed' => 'integer',
            'total_successful' => 'integer',
            'total_failed' => 'integer',
            'total_amount_processed' => 'integer',
            'total_amount_successful' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * Get the batch items.
     */
    public function items(): HasMany
    {
        return $this->hasMany(PaymentBatchItem::class);
    }

    /**
     * Start the batch processing.
     */
    public function start(): void
    {
        $this->update([
            'status' => self::STATUS_RUNNING,
            'started_at' => now(),
        ]);
    }

    /**
     * Complete the batch processing.
     */
    public function complete(): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark the batch as failed.
     */
    public function fail(): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'completed_at' => now(),
        ]);
    }

    /**
     * Update the batch statistics.
     */
    public function updateStats(): void
    {
        $this->update([
            'total_processed' => $this->items()->count(),
            'total_successful' => $this->items()->where('status', 'succeeded')->count(),
            'total_failed' => $this->items()->where('status', 'failed')->count(),
            'total_amount_processed' => $this->items()->sum('amount'),
            'total_amount_successful' => $this->items()->where('status', 'succeeded')->sum('amount'),
        ]);
    }

    /**
     * Generate a unique batch ID.
     */
    public static function generateBatchId(): string
    {
        return 'batch_' . now()->format('Ymd_His') . '_' . substr(uniqid(), -6);
    }
}

