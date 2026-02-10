<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Award, Trophy, Star, Lock, CheckCircle2, Filter } from 'lucide-vue-next';

interface BadgeProgress {
    current: number;
    required: number;
    percentage: number;
    earned: boolean;
}

interface Badge {
    id: number;
    name: string;
    slug: string;
    icon: string;
    description: string;
    category: string;
    category_label: string;
    rarity: string;
    rarity_colors: {
        bg: string;
        text: string;
        border: string;
    };
    is_earned: boolean;
    earned_at: string | null;
    progress: BadgeProgress;
}

interface RarityStats {
    total: number;
    earned: number;
}

interface Stats {
    total: number;
    earned: number;
    percentage: number;
    byRarity: Record<string, RarityStats>;
}

const props = defineProps<{
    badges: Badge[];
    badgesByCategory: Record<string, Badge[]>;
    categories: Record<string, string>;
    stats: Stats;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Badges', href: '/badges-page' },
];

// Filter state
const selectedCategory = ref<string>('all');
const selectedRarity = ref<string>('all');
const showEarnedOnly = ref<boolean>(false);

// Rarity order for sorting
const rarityOrder = ['common', 'uncommon', 'rare', 'epic', 'legendary'];

// Filtered badges
const filteredBadges = computed(() => {
    let result = props.badges;
    
    if (selectedCategory.value !== 'all') {
        result = result.filter(b => b.category === selectedCategory.value);
    }
    
    if (selectedRarity.value !== 'all') {
        result = result.filter(b => b.rarity === selectedRarity.value);
    }
    
    if (showEarnedOnly.value) {
        result = result.filter(b => b.is_earned);
    }
    
    return result;
});

// Earned badges
const earnedBadges = computed(() => props.badges.filter(b => b.is_earned));

// Format date
const formatDate = (dateStr: string | null) => {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};

// Rarity label
const rarityLabel = (rarity: string) => {
    return rarity.charAt(0).toUpperCase() + rarity.slice(1);
};

// Rarity gradient classes for cards
const rarityGradient = (rarity: string) => {
    const gradients: Record<string, string> = {
        common: 'from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-800',
        uncommon: 'from-green-50 to-green-100 dark:from-green-950 dark:to-green-900',
        rare: 'from-blue-50 to-blue-100 dark:from-blue-950 dark:to-blue-900',
        epic: 'from-purple-50 to-purple-100 dark:from-purple-950 dark:to-purple-900',
        legendary: 'from-amber-50 to-amber-100 dark:from-amber-950 dark:to-amber-900',
    };
    return gradients[rarity] || gradients.common;
};
</script>

<template>
    <Head title="Badges" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="w-full">
                <!-- Stats Overview -->
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4 mb-6">
                    <!-- Total Progress -->
                    <Card class="col-span-2">
                        <CardHeader class="pb-2">
                            <CardTitle class="text-sm font-medium flex items-center gap-2">
                                <Trophy class="h-4 w-4 text-amber-500" />
                                Badge Progress
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">{{ stats.earned }} / {{ stats.total }}</div>
                            <div class="mt-2 h-2 bg-muted rounded-full overflow-hidden">
                                <div
                                    class="h-full bg-amber-500 transition-all duration-500"
                                    :style="{ width: `${stats.percentage}%` }"
                                ></div>
                            </div>
                            <p class="text-xs text-muted-foreground mt-1">{{ stats.percentage }}% complete</p>
                        </CardContent>
                    </Card>
                    
                    <!-- Rarity Stats -->
                    <Card v-for="(label, rarity) in { common: 'Common', uncommon: 'Uncommon', rare: 'Rare', epic: 'Epic', legendary: 'Legendary' }" :key="rarity">
                        <CardHeader class="pb-2">
                            <CardTitle class="text-xs font-medium" :class="{
                                'text-slate-600 dark:text-slate-400': rarity === 'common',
                                'text-green-600 dark:text-green-400': rarity === 'uncommon',
                                'text-blue-600 dark:text-blue-400': rarity === 'rare',
                                'text-purple-600 dark:text-purple-400': rarity === 'epic',
                                'text-amber-600 dark:text-amber-400': rarity === 'legendary',
                            }">{{ label }}</CardTitle>
                        </CardHeader>
                        <CardContent class="pt-0">
                            <div class="text-lg font-bold">{{ stats.byRarity[rarity]?.earned || 0 }} / {{ stats.byRarity[rarity]?.total || 0 }}</div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Tabs -->
                <Tabs default-value="earned" class="w-full">
                    <TabsList class="grid w-full grid-cols-2 mb-4">
                        <TabsTrigger value="earned" class="flex items-center gap-2">
                            <CheckCircle2 class="h-4 w-4" />
                            My Badges ({{ earnedBadges.length }})
                        </TabsTrigger>
                        <TabsTrigger value="all" class="flex items-center gap-2">
                            <Award class="h-4 w-4" />
                            All Badges
                        </TabsTrigger>
                    </TabsList>

                    <!-- Earned Badges Tab (My Badges) -->
                    <TabsContent value="earned">
                        <div v-if="earnedBadges.length === 0" class="text-center py-12 text-muted-foreground">
                            <Lock class="h-12 w-12 mx-auto mb-4 opacity-50" />
                            <p>You haven't earned any badges yet.</p>
                            <p class="text-sm mt-2">Keep fishing to unlock achievements!</p>
                        </div>

                        <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                            <Card
                                v-for="badge in earnedBadges"
                                :key="badge.id"
                                :class="[
                                    'relative overflow-hidden ring-2 ring-offset-2',
                                    badge.rarity === 'common' && 'ring-slate-400',
                                    badge.rarity === 'uncommon' && 'ring-green-400',
                                    badge.rarity === 'rare' && 'ring-blue-400',
                                    badge.rarity === 'epic' && 'ring-purple-400',
                                    badge.rarity === 'legendary' && 'ring-amber-400',
                                ]"
                            >
                                <div :class="['absolute inset-0 bg-gradient-to-br opacity-50', rarityGradient(badge.rarity)]"></div>
                                <CardHeader class="relative pb-2">
                                    <div class="flex items-start justify-between">
                                        <span class="text-3xl">{{ badge.icon }}</span>
                                        <span :class="[
                                            'text-xs px-2 py-0.5 rounded-full font-medium',
                                            badge.rarity_colors.bg,
                                            badge.rarity_colors.text,
                                        ]">
                                            {{ rarityLabel(badge.rarity) }}
                                        </span>
                                    </div>
                                    <CardTitle class="text-sm mt-2">{{ badge.name }}</CardTitle>
                                    <CardDescription class="text-xs">{{ badge.description }}</CardDescription>
                                </CardHeader>
                                <CardContent class="relative pt-0">
                                    <div class="text-xs text-muted-foreground mb-2">{{ badge.category_label }}</div>
                                    <div class="flex items-center gap-1 text-green-600 dark:text-green-400">
                                        <CheckCircle2 class="h-4 w-4" />
                                        <span class="text-xs">Earned {{ formatDate(badge.earned_at) }}</span>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                    </TabsContent>

                    <!-- All Badges Tab -->
                    <TabsContent value="all">
                        <!-- Filters -->
                        <div class="flex flex-wrap gap-4 mb-4">
                            <div class="flex items-center gap-2">
                                <Filter class="h-4 w-4 text-muted-foreground" />
                                <select v-model="selectedCategory" class="text-sm border rounded-md px-2 py-1 bg-background">
                                    <option value="all">All Categories</option>
                                    <option v-for="(label, key) in categories" :key="key" :value="key">{{ label }}</option>
                                </select>
                            </div>
                            <select v-model="selectedRarity" class="text-sm border rounded-md px-2 py-1 bg-background">
                                <option value="all">All Rarities</option>
                                <option value="common">Common</option>
                                <option value="uncommon">Uncommon</option>
                                <option value="rare">Rare</option>
                                <option value="epic">Epic</option>
                                <option value="legendary">Legendary</option>
                            </select>
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" v-model="showEarnedOnly" class="rounded" />
                                Show earned only
                            </label>
                        </div>

                        <!-- Badge Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                            <Card
                                v-for="badge in filteredBadges"
                                :key="badge.id"
                                :class="[
                                    'relative overflow-hidden transition-all duration-200',
                                    badge.is_earned ? 'ring-2 ring-offset-2' : 'opacity-75 grayscale hover:opacity-100 hover:grayscale-0',
                                    badge.is_earned && badge.rarity === 'common' && 'ring-slate-400',
                                    badge.is_earned && badge.rarity === 'uncommon' && 'ring-green-400',
                                    badge.is_earned && badge.rarity === 'rare' && 'ring-blue-400',
                                    badge.is_earned && badge.rarity === 'epic' && 'ring-purple-400',
                                    badge.is_earned && badge.rarity === 'legendary' && 'ring-amber-400',
                                ]"
                            >
                                <div :class="['absolute inset-0 bg-gradient-to-br opacity-50', rarityGradient(badge.rarity)]"></div>
                                <CardHeader class="relative pb-2">
                                    <div class="flex items-start justify-between">
                                        <span class="text-3xl">{{ badge.icon }}</span>
                                        <span :class="[
                                            'text-xs px-2 py-0.5 rounded-full font-medium',
                                            badge.rarity_colors.bg,
                                            badge.rarity_colors.text,
                                        ]">
                                            {{ rarityLabel(badge.rarity) }}
                                        </span>
                                    </div>
                                    <CardTitle class="text-sm mt-2">{{ badge.name }}</CardTitle>
                                    <CardDescription class="text-xs">{{ badge.description }}</CardDescription>
                                </CardHeader>
                                <CardContent class="relative pt-0">
                                    <div class="text-xs text-muted-foreground mb-2">{{ badge.category_label }}</div>

                                    <!-- Progress or Earned Status -->
                                    <div v-if="badge.is_earned" class="flex items-center gap-1 text-green-600 dark:text-green-400">
                                        <CheckCircle2 class="h-4 w-4" />
                                        <span class="text-xs">Earned {{ formatDate(badge.earned_at) }}</span>
                                    </div>
                                    <div v-else>
                                        <div class="flex items-center justify-between text-xs mb-1">
                                            <span class="flex items-center gap-1 text-muted-foreground">
                                                <Lock class="h-3 w-3" />
                                                Progress
                                            </span>
                                            <span>{{ badge.progress.current }} / {{ badge.progress.required }}</span>
                                        </div>
                                        <div class="h-1.5 bg-muted rounded-full overflow-hidden">
                                            <div
                                                class="h-full bg-primary transition-all duration-500"
                                                :style="{ width: `${badge.progress.percentage}%` }"
                                            ></div>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>

                        <div v-if="filteredBadges.length === 0" class="text-center py-12 text-muted-foreground">
                            No badges match your filters.
                        </div>
                    </TabsContent>
                </Tabs>
            </div>
        </div>
    </AppLayout>
</template>

