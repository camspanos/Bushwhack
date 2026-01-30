<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserWeather extends Model
{
    use HasFactory;
    protected $table = 'user_weather';

    protected $fillable = [
        'user_id',
        'temperature',
        'cloud',
        'wind',
        'precipitation',
        'barometric_pressure',
    ];

    /**
     * Get the user that owns this weather record.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the fishing logs associated with this weather record.
     */
    public function fishingLogs(): HasMany
    {
        return $this->hasMany(FishingLog::class, 'user_weather_id');
    }
}

