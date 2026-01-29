<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserWaterCondition extends Model
{
    protected $table = 'user_water_conditions';

    protected $fillable = [
        'user_id',
        'temperature',
        'clarity',
        'level',
        'speed',
        'surface_condition',
        'tide',
    ];

    /**
     * Get the user that owns this water condition record.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the fishing logs associated with this water condition record.
     */
    public function fishingLogs(): HasMany
    {
        return $this->hasMany(FishingLog::class, 'user_water_condition_id');
    }
}

