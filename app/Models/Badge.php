<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Badge extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'description',
        'category',
        'rarity',
        'requirement_type',
        'requirement_field',
        'requirement_operator',
        'requirement_value',
        'requirement_value2',
        'requirement_extra',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'requirement_value' => 'decimal:2',
        'requirement_value2' => 'decimal:2',
        'requirement_extra' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Rarity colors for display
     */
    public const RARITY_COLORS = [
        'common' => ['bg' => 'bg-slate-100', 'text' => 'text-slate-600', 'border' => 'border-slate-300'],
        'uncommon' => ['bg' => 'bg-green-100', 'text' => 'text-green-600', 'border' => 'border-green-300'],
        'rare' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-600', 'border' => 'border-blue-300'],
        'epic' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-600', 'border' => 'border-purple-300'],
        'legendary' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-600', 'border' => 'border-amber-300'],
    ];

    /**
     * Categories for badges
     */
    public const CATEGORIES = [
        'quantity' => 'Catch Quantity',
        'size' => 'Fish Size',
        'time' => 'Time of Day',
        'weather' => 'Weather Conditions',
        'moon' => 'Moon Phases',
        'location' => 'Locations',
        'species' => 'Species',
        'streak' => 'Streaks',
        'social' => 'Social',
        'seasonal' => 'Seasonal',
        'rod' => 'Rods & Gear',
        'fly' => 'Flies & Lures',
        'weight' => 'Fish Weight',
        'combo' => 'Combinations',
        'milestone' => 'Milestones',
        'challenge' => 'Challenges',
    ];

    /**
     * Users who have earned this badge
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_badges')
            ->withPivot(['earned_at', 'earned_data', 'is_notified'])
            ->withTimestamps();
    }

    /**
     * Scope for active badges
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for category
     */
    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope for rarity
     */
    public function scopeRarity($query, string $rarity)
    {
        return $query->where('rarity', $rarity);
    }

    /**
     * Get rarity color classes
     */
    public function getRarityColorsAttribute(): array
    {
        return self::RARITY_COLORS[$this->rarity] ?? self::RARITY_COLORS['common'];
    }

    /**
     * Get category label
     */
    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst($this->category);
    }
}

