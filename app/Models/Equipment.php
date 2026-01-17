<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Equipment extends Model
{
    protected $fillable = [
        'user_id',
        'rod_name',
        'rod_weight',
        'lure',
        'line',
        'tippet',
    ];

    /**
     * Get the user that owns this equipment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the fishing logs that used this equipment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function fishingLogs(): HasMany
    {
        return $this->hasMany(FishingLog::class);
    }
}
