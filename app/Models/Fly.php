<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fly extends Model
{
    protected $table = 'user_flies';

    protected $fillable = [
        'user_id',
        'name',
        'color',
        'size',
        'type',
    ];

    /**
     * Get the user that owns this fly.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the fishing logs where this fly was used.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function fishingLogs(): HasMany
    {
        return $this->hasMany(FishingLog::class);
    }
}
