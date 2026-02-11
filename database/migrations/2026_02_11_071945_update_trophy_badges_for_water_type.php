<?php

use App\Models\Badge;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing size badges to be freshwater-specific (including slugs)
        $sizeBadgeUpdates = [
            ['old_name' => 'Size Matters', 'new_name' => 'Size Matters (Freshwater)', 'new_slug' => 'size-matters-freshwater', 'field' => 'freshwater_max_size'],
            ['old_name' => 'Foot Long', 'new_name' => 'Foot Long (Freshwater)', 'new_slug' => 'foot-long-freshwater', 'field' => 'freshwater_max_size'],
            ['old_name' => 'Nice Catch', 'new_name' => 'Nice Catch (Freshwater)', 'new_slug' => 'nice-catch-freshwater', 'field' => 'freshwater_max_size'],
            ['old_name' => 'Trophy Hunter', 'new_name' => 'Freshwater Trophy Hunter', 'new_slug' => 'freshwater-trophy-hunter', 'field' => 'freshwater_max_size'],
            ['old_name' => 'Monster Fish', 'new_name' => 'Freshwater Monster', 'new_slug' => 'freshwater-monster', 'field' => 'freshwater_max_size'],
            ['old_name' => 'Lunker', 'new_name' => 'Freshwater Lunker', 'new_slug' => 'freshwater-lunker', 'field' => 'freshwater_max_size'],
            ['old_name' => 'Whale of a Fish', 'new_name' => 'Freshwater Whale', 'new_slug' => 'freshwater-whale', 'field' => 'freshwater_max_size'],
            ['old_name' => 'Leviathan', 'new_name' => 'Freshwater Leviathan', 'new_slug' => 'freshwater-leviathan', 'field' => 'freshwater_max_size'],
            ['old_name' => 'Sea Monster', 'new_name' => 'Freshwater Giant', 'new_slug' => 'freshwater-giant', 'field' => 'freshwater_max_size'],
        ];

        foreach ($sizeBadgeUpdates as $update) {
            Badge::where('name', $update['old_name'])
                ->where('category', 'size')
                ->update([
                    'name' => $update['new_name'],
                    'slug' => $update['new_slug'],
                    'requirement_field' => $update['field'],
                ]);
        }

        // Update trophy count badges to freshwater
        Badge::where('name', 'Trophy Collector')
            ->update([
                'name' => 'Freshwater Trophy Collector',
                'slug' => 'freshwater-trophy-collector',
                'description' => 'Catch 5 freshwater trophy fish (20"+)',
                'requirement_field' => 'freshwater_trophy_count',
            ]);

        Badge::where('name', 'Big Game Hunter')
            ->update([
                'name' => 'Freshwater Big Game Hunter',
                'slug' => 'freshwater-big-game-hunter',
                'description' => 'Catch 10 freshwater trophy fish (20"+)',
                'requirement_field' => 'freshwater_trophy_count',
            ]);

        Badge::where('name', 'Trophy Room')
            ->update([
                'name' => 'Freshwater Trophy Room',
                'slug' => 'freshwater-trophy-room',
                'description' => 'Catch 25 freshwater trophy fish (20"+)',
                'requirement_field' => 'freshwater_trophy_count',
            ]);

        // Update daily trophies badges to freshwater
        Badge::where('name', 'Double Header')
            ->update([
                'name' => 'Freshwater Double Header',
                'slug' => 'freshwater-double-header',
                'description' => 'Catch 2 freshwater trophies (20"+) in one day',
                'requirement_field' => 'freshwater_daily_trophies',
            ]);

        Badge::where('name', 'Hat Trick')
            ->update([
                'name' => 'Freshwater Hat Trick',
                'slug' => 'freshwater-hat-trick',
                'description' => 'Catch 3 freshwater trophies (20"+) in one day',
                'requirement_field' => 'freshwater_daily_trophies',
            ]);

        // Create saltwater size badges (larger sizes)
        $saltwaterSizeBadges = [
            [
                'name' => 'Size Matters (Saltwater)',
                'slug' => 'size-matters-saltwater',
                'icon' => '🐟',
                'description' => 'Catch a saltwater fish 12 inches or larger',
                'category' => 'size',
                'rarity' => 'common',
                'requirement_type' => 'count',
                'requirement_field' => 'saltwater_max_size',
                'requirement_operator' => '>=',
                'requirement_value' => 12,
            ],
            [
                'name' => 'Foot Long (Saltwater)',
                'slug' => 'foot-long-saltwater',
                'icon' => '📏',
                'description' => 'Catch a saltwater fish 18 inches or larger',
                'category' => 'size',
                'rarity' => 'common',
                'requirement_type' => 'count',
                'requirement_field' => 'saltwater_max_size',
                'requirement_operator' => '>=',
                'requirement_value' => 18,
            ],
            [
                'name' => 'Nice Catch (Saltwater)',
                'slug' => 'nice-catch-saltwater',
                'icon' => '👍',
                'description' => 'Catch a saltwater fish 24 inches or larger',
                'category' => 'size',
                'rarity' => 'uncommon',
                'requirement_type' => 'count',
                'requirement_field' => 'saltwater_max_size',
                'requirement_operator' => '>=',
                'requirement_value' => 24,
            ],
            [
                'name' => 'Saltwater Trophy Hunter',
                'slug' => 'saltwater-trophy-hunter',
                'icon' => '🏆',
                'description' => 'Catch a saltwater trophy fish (30"+)',
                'category' => 'size',
                'rarity' => 'uncommon',
                'requirement_type' => 'count',
                'requirement_field' => 'saltwater_max_size',
                'requirement_operator' => '>=',
                'requirement_value' => 30,
            ],
            [
                'name' => 'Saltwater Monster',
                'slug' => 'saltwater-monster',
                'icon' => '👹',
                'description' => 'Catch a saltwater fish 36 inches or larger',
                'category' => 'size',
                'rarity' => 'rare',
                'requirement_type' => 'count',
                'requirement_field' => 'saltwater_max_size',
                'requirement_operator' => '>=',
                'requirement_value' => 36,
            ],
            [
                'name' => 'Saltwater Lunker',
                'slug' => 'saltwater-lunker',
                'icon' => '🐋',
                'description' => 'Catch a saltwater fish 48 inches or larger',
                'category' => 'size',
                'rarity' => 'rare',
                'requirement_type' => 'count',
                'requirement_field' => 'saltwater_max_size',
                'requirement_operator' => '>=',
                'requirement_value' => 48,
            ],
            [
                'name' => 'Saltwater Whale',
                'slug' => 'saltwater-whale',
                'icon' => '🐳',
                'description' => 'Catch a saltwater fish 60 inches or larger',
                'category' => 'size',
                'rarity' => 'epic',
                'requirement_type' => 'count',
                'requirement_field' => 'saltwater_max_size',
                'requirement_operator' => '>=',
                'requirement_value' => 60,
            ],
            [
                'name' => 'Saltwater Leviathan',
                'slug' => 'saltwater-leviathan',
                'icon' => '🦑',
                'description' => 'Catch a saltwater fish 72 inches or larger',
                'category' => 'size',
                'rarity' => 'legendary',
                'requirement_type' => 'count',
                'requirement_field' => 'saltwater_max_size',
                'requirement_operator' => '>=',
                'requirement_value' => 72,
            ],
            [
                'name' => 'Saltwater Sea Monster',
                'slug' => 'saltwater-sea-monster',
                'icon' => '🌊',
                'description' => 'Catch a saltwater fish 84 inches or larger',
                'category' => 'size',
                'rarity' => 'legendary',
                'requirement_type' => 'count',
                'requirement_field' => 'saltwater_max_size',
                'requirement_operator' => '>=',
                'requirement_value' => 84,
            ],
        ];

        foreach ($saltwaterSizeBadges as $badge) {
            Badge::create($badge);
        }

        // Create saltwater trophy count badges
        $saltwaterTrophyBadges = [
            [
                'name' => 'Saltwater Trophy Collector',
                'slug' => 'saltwater-trophy-collector',
                'icon' => '🏆',
                'description' => 'Catch 5 saltwater trophy fish (30"+)',
                'category' => 'size',
                'rarity' => 'rare',
                'requirement_type' => 'count',
                'requirement_field' => 'saltwater_trophy_count',
                'requirement_operator' => '>=',
                'requirement_value' => 5,
            ],
            [
                'name' => 'Saltwater Big Game Hunter',
                'slug' => 'saltwater-big-game-hunter',
                'icon' => '🎯',
                'description' => 'Catch 10 saltwater trophy fish (30"+)',
                'category' => 'size',
                'rarity' => 'epic',
                'requirement_type' => 'count',
                'requirement_field' => 'saltwater_trophy_count',
                'requirement_operator' => '>=',
                'requirement_value' => 10,
            ],
            [
                'name' => 'Saltwater Trophy Room',
                'slug' => 'saltwater-trophy-room',
                'icon' => '🏛️',
                'description' => 'Catch 25 saltwater trophy fish (30"+)',
                'category' => 'size',
                'rarity' => 'legendary',
                'requirement_type' => 'count',
                'requirement_field' => 'saltwater_trophy_count',
                'requirement_operator' => '>=',
                'requirement_value' => 25,
            ],
        ];

        foreach ($saltwaterTrophyBadges as $badge) {
            Badge::create($badge);
        }

        // Create saltwater daily trophy badges
        $saltwaterDailyBadges = [
            [
                'name' => 'Saltwater Double Header',
                'slug' => 'saltwater-double-header',
                'icon' => '✌️',
                'description' => 'Catch 2 saltwater trophies (30"+) in one day',
                'category' => 'challenge',
                'rarity' => 'rare',
                'requirement_type' => 'count',
                'requirement_field' => 'saltwater_daily_trophies',
                'requirement_operator' => '>=',
                'requirement_value' => 2,
            ],
            [
                'name' => 'Saltwater Hat Trick',
                'slug' => 'saltwater-hat-trick',
                'icon' => '🎩',
                'description' => 'Catch 3 saltwater trophies (30"+) in one day',
                'category' => 'challenge',
                'rarity' => 'epic',
                'requirement_type' => 'count',
                'requirement_field' => 'saltwater_daily_trophies',
                'requirement_operator' => '>=',
                'requirement_value' => 3,
            ],
        ];

        foreach ($saltwaterDailyBadges as $badge) {
            Badge::create($badge);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Delete saltwater badges
        Badge::where('name', 'like', 'Saltwater%')->delete();
        Badge::whereIn('name', [
            'Size Matters (Saltwater)',
            'Foot Long (Saltwater)',
            'Nice Catch (Saltwater)',
        ])->delete();

        // Revert freshwater badges to original names and slugs
        $revertUpdates = [
            ['new_name' => 'Size Matters (Freshwater)', 'old_name' => 'Size Matters', 'old_slug' => 'size-matters', 'field' => 'max_size'],
            ['new_name' => 'Foot Long (Freshwater)', 'old_name' => 'Foot Long', 'old_slug' => 'foot-long', 'field' => 'max_size'],
            ['new_name' => 'Nice Catch (Freshwater)', 'old_name' => 'Nice Catch', 'old_slug' => 'nice-catch', 'field' => 'max_size'],
            ['new_name' => 'Freshwater Trophy Hunter', 'old_name' => 'Trophy Hunter', 'old_slug' => 'trophy-hunter', 'field' => 'max_size'],
            ['new_name' => 'Freshwater Monster', 'old_name' => 'Monster Fish', 'old_slug' => 'monster-fish', 'field' => 'max_size'],
            ['new_name' => 'Freshwater Lunker', 'old_name' => 'Lunker', 'old_slug' => 'lunker', 'field' => 'max_size'],
            ['new_name' => 'Freshwater Whale', 'old_name' => 'Whale of a Fish', 'old_slug' => 'whale-of-a-fish', 'field' => 'max_size'],
            ['new_name' => 'Freshwater Leviathan', 'old_name' => 'Leviathan', 'old_slug' => 'leviathan', 'field' => 'max_size'],
            ['new_name' => 'Freshwater Giant', 'old_name' => 'Sea Monster', 'old_slug' => 'sea-monster', 'field' => 'max_size'],
            ['new_name' => 'Freshwater Trophy Collector', 'old_name' => 'Trophy Collector', 'old_slug' => 'trophy-collector', 'field' => 'trophy_count'],
            ['new_name' => 'Freshwater Big Game Hunter', 'old_name' => 'Big Game Hunter', 'old_slug' => 'big-game-hunter', 'field' => 'trophy_count'],
            ['new_name' => 'Freshwater Trophy Room', 'old_name' => 'Trophy Room', 'old_slug' => 'trophy-room', 'field' => 'trophy_count'],
            ['new_name' => 'Freshwater Double Header', 'old_name' => 'Double Header', 'old_slug' => 'double-header', 'field' => 'daily_trophies'],
            ['new_name' => 'Freshwater Hat Trick', 'old_name' => 'Hat Trick', 'old_slug' => 'hat-trick', 'field' => 'daily_trophies'],
        ];

        foreach ($revertUpdates as $update) {
            Badge::where('name', $update['new_name'])
                ->update([
                    'name' => $update['old_name'],
                    'slug' => $update['old_slug'],
                    'requirement_field' => $update['field'],
                ]);
        }
    }
};
