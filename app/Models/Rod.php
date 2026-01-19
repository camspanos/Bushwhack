<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rod extends Model
{
    protected $fillable = [
        'user_id',
        'rod_name',
        'rod_weight',
        'rod_length',
        'reel',
        'line',
    ];

    /**
     * Get the user that owns this rod.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the fishing logs that used this rod.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function fishingLogs(): HasMany
    {
        return $this->hasMany(FishingLog::class, 'equipment_id');
    }
}

