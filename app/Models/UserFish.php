<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserFish extends Model
{
    protected $table = 'user_fish';

    protected $fillable = [
        'user_id',
        'species',
        'water_type',
        'fish_species_id',
    ];

    /**
     * Get the user that owns this fish species record.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the fishing logs where this fish species was caught.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function fishingLogs(): HasMany
    {
        return $this->hasMany(FishingLog::class, 'user_fish_id');
    }

    /**
     * Get the global fish species this user fish belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function fishSpecies(): BelongsTo
    {
        return $this->belongsTo(FishSpecies::class);
    }
}
