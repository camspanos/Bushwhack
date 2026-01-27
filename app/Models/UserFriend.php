<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class UserFriend extends Model
{
    protected $table = 'user_friends';

    protected $fillable = [
        'user_id',
        'name',
    ];

    /**
     * Get the user that owns this friend record.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the fishing logs that this friend was present for.
     *
     * This is a many-to-many relationship using the fishing_log_user_friend pivot table.
     * A friend can be associated with multiple fishing logs, and a fishing log can
     * have multiple friends.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function fishingLogs(): BelongsToMany
    {
        return $this->belongsToMany(FishingLog::class, 'fishing_log_user_friend', 'user_friend_id', 'fishing_log_id');
    }
}
