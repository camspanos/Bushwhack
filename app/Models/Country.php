<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    use HasFactory;
    public $timestamps = false;

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

    /**
     * Get the users for this country.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
