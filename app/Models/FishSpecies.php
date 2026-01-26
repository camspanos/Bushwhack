<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FishSpecies extends Model
{
    protected $fillable = [
        'species',
        'water_type',
    ];

    /**
     * Get the user fish records that reference this species.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function userFish(): HasMany
    {
        return $this->hasMany(Fish::class, 'fish_species_id');
    }

    /**
     * Get the fishing logs for this species.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function fishingLogs(): HasMany
    {
        return $this->hasMany(FishingLog::class, 'fish_species_id');
    }
}
