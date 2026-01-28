<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserLocation extends Model
{
    protected $table = 'user_locations';

    protected $fillable = [
        'user_id',
        'name',
        'city',
        'state',
        'country_id',
        'latitude',
        'longitude',
    ];

    /**
     * Get the user that owns this location.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the country that this location belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get the fishing logs associated with this location.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function fishingLogs(): HasMany
    {
        return $this->hasMany(FishingLog::class, 'user_location_id');
    }
}
