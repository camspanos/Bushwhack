<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fly extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'color',
        'size',
        'type',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fishingLogs(): HasMany
    {
        return $this->hasMany(FishingLog::class);
    }
}
