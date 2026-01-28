<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'city',
        'state',
        'country_id',
        'metric',
        'allow_followers',
        'is_premium',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'allow_followers' => 'boolean',
            'is_premium' => 'boolean',
            'metric' => 'boolean',
        ];
    }

    /**
     * Get the country for this user.
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get the users that this user is following.
     */
    public function following()
    {
        return $this->belongsToMany(User::class, 'user_follows', 'follower_id', 'following_id')
            ->withTimestamps();
    }

    /**
     * Get the users who are following this user.
     */
    public function followers()
    {
        return $this->belongsToMany(User::class, 'user_follows', 'following_id', 'follower_id')
            ->withTimestamps();
    }

    /**
     * Check if this user is following another user.
     */
    public function isFollowing(User $user): bool
    {
        return $this->following()->where('following_id', $user->id)->exists();
    }

    /**
     * Follow another user.
     */
    public function follow(User $user): void
    {
        // Free users can only follow user_id 1
        if (!$this->is_premium && $user->id !== 1) {
            return;
        }

        if (!$this->isFollowing($user) && $this->id !== $user->id && $user->allow_followers) {
            $this->following()->attach($user->id);
        }
    }

    /**
     * Unfollow another user.
     */
    public function unfollow(User $user): void
    {
        $this->following()->detach($user->id);
    }

    /**
     * Check if user has premium subscription.
     */
    public function isPremium(): bool
    {
        return $this->is_premium;
    }

    /**
     * Check if user can follow a specific user.
     */
    public function canFollow(User $user): bool
    {
        // Premium users can follow anyone
        if ($this->is_premium) {
            return true;
        }

        // Free users can only follow user_id 1
        return $user->id === 1;
    }

    /**
     * Check if user can access year filtering.
     */
    public function canFilterByYear(): bool
    {
        return $this->is_premium;
    }
}
