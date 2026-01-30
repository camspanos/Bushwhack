<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FishSpecies extends Model
{
    use HasFactory;
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
        return $this->hasMany(UserFish::class, 'fish_species_id');
    }
}
