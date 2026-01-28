<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    protected $fillable = [
        'name',
        'code',
    ];

    /**
     * Get the user locations for this country.
     */
    public function userLocations(): HasMany
    {
        return $this->hasMany(UserLocation::class);
    }
}
