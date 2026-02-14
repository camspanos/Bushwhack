<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
        'birthday',
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
            'birthday' => 'date',
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
     * Get all subscriptions for this user.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Get the active subscription for this user.
     */
    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)
            ->where('status', Subscription::STATUS_ACTIVE);
    }

    /**
     * Get all payment transactions for this user.
     */
    public function paymentTransactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    /**
     * Check if user has premium subscription.
     * Checks for active subscription first, falls back to is_premium boolean.
     */
    public function isPremium(): bool
    {
        // Check for active subscription
        if ($this->activeSubscription()->exists()) {
            return true;
        }

        // Fall back to legacy is_premium field for backwards compatibility
        return $this->is_premium;
    }

    /**
     * Check if user can follow a specific user.
     */
    public function canFollow(User $user): bool
    {
        // Premium users can follow anyone
        if ($this->isPremium()) {
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
        return $this->isPremium();
    }

    /**
     * Get the badges earned by this user.
     */
    public function badges(): BelongsToMany
    {
        return $this->belongsToMany(Badge::class, 'user_badges')
            ->withPivot(['earned_at', 'earned_data', 'is_notified'])
            ->withTimestamps();
    }

    /**
     * Check if user has earned a specific badge.
     */
    public function hasBadge(Badge $badge): bool
    {
        return $this->badges()->where('badge_id', $badge->id)->exists();
    }

    /**
     * Check if user has earned a badge by slug.
     */
    public function hasBadgeBySlug(string $slug): bool
    {
        return $this->badges()->where('slug', $slug)->exists();
    }
}
