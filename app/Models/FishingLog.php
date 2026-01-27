<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FishingLog extends Model
{
    protected $fillable = [
        'user_id',
        'location_id',
        'equipment_id',
        'fish_id',
        'fish_species_id',
        'fly_id',
        'date',
        'time',
        'time_of_day',
        'quantity',
        'max_size',
        'style',
        'moon_phase',
        'barometric_pressure',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'max_size' => 'decimal:2',
    ];

    /**
     * Get the user that owns the fishing log.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the location where the fishing took place.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Get the rod used for this fishing log.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function rod(): BelongsTo
    {
        return $this->belongsTo(Rod::class, 'equipment_id');
    }

    /**
     * Alias for rod() to maintain backward compatibility.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function equipment(): BelongsTo
    {
        return $this->rod();
    }

    /**
     * Get the fish species caught in this fishing log.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function fish(): BelongsTo
    {
        return $this->belongsTo(Fish::class);
    }

    /**
     * Get the fly used for this fishing log.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function fly(): BelongsTo
    {
        return $this->belongsTo(Fly::class);
    }

    /**
     * Get the friends that were present during this fishing trip.
     *
     * This is a many-to-many relationship using the fishing_log_user_friend pivot table.
     * A fishing log can have multiple friends, and a friend can be associated with
     * multiple fishing logs.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function friends(): BelongsToMany
    {
        return $this->belongsToMany(Friend::class, 'fishing_log_user_friend');
    }

    /**
     * Get the global fish species for this fishing log.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function fishSpecies(): BelongsTo
    {
        return $this->belongsTo(FishSpecies::class);
    }
}
