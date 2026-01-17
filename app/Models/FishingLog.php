<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FishingLog extends Model
{
    protected $fillable = [
        'user_id',
        'location_id',
        'equipment_id',
        'fish_id',
        'fly_id',
        'friend_id',
        'date',
        'quantity',
        'max_size',
        'style',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'max_size' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function fish(): BelongsTo
    {
        return $this->belongsTo(Fish::class);
    }

    public function fly(): BelongsTo
    {
        return $this->belongsTo(Fly::class);
    }

    public function friend(): BelongsTo
    {
        return $this->belongsTo(Friend::class);
    }
}
