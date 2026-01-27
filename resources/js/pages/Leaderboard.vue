<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { NativeSelect, NativeSelectOption } from '@/components/ui/native-select';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { Trophy, Award, Fish, TrendingUp, Crown } from 'lucide-vue-next';
import { ref, watch } from 'vue';

interface LeaderboardEntry {
    species: string;
    water_type: string;
    biggest_fish: {
        user_name: string;
        user_id: number;
        size: number | string;
        date: string;
    } | null;
    most_caught: {
        user_name: string;
        user_id: number;
        total: number | string;
    } | null;
}

interface MonthOption {
    value: string;
    label: string;
}

const props = defineProps<{
    leaderboard: LeaderboardEntry[];
    monthOptions: MonthOption[];
    selectedMonth: string;
    selectedWaterType: string;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { label: 'Leaderboard' },
];

const page = usePage();
const selectedMonth = ref(props.selectedMonth);
const selectedWaterType = ref(props.selectedWaterType);

watch([selectedMonth, selectedWaterType], () => {
    router.get('/leaderboard', {
        month: selectedMonth.value,
        water_type: selectedWaterType.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
});

const formatSize = (size: number | string): string => {
    const numSize = typeof size === 'string' ? parseFloat(size) : size;
    return numSize % 1 === 0 ? numSize.toString() : numSize.toFixed(1);
};
</script>

<template>
    <Head title="Leaderboard" />
    
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Filters -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                <div class="flex items-center gap-3 flex-1">
                    <label class="text-sm font-medium text-muted-foreground whitespace-nowrap">Time Period:</label>
                    <NativeSelect v-model="selectedMonth" class="flex-1">
                        <NativeSelectOption
                            v-for="option in monthOptions"
                            :key="option.value"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </NativeSelectOption>
                    </NativeSelect>
                </div>
                <div class="flex items-center gap-3 flex-1">
                    <label class="text-sm font-medium text-muted-foreground whitespace-nowrap">Water Type:</label>
                    <NativeSelect v-model="selectedWaterType" class="flex-1">
                        <NativeSelectOption value="all">All</NativeSelectOption>
                        <NativeSelectOption value="freshwater">Freshwater</NativeSelectOption>
                        <NativeSelectOption value="saltwater">Saltwater</NativeSelectOption>
                    </NativeSelect>
                </div>
            </div>

            <!-- Header -->
            <Card class="bg-gradient-to-br from-amber-50/50 to-transparent dark:from-amber-950/20">
                <CardHeader>
                    <div class="flex items-center gap-3">
                        <div class="rounded-full bg-amber-100 p-2 dark:bg-amber-900/30">
                            <Trophy class="h-6 w-6 text-amber-600 dark:text-amber-400" />
                        </div>
                        <div>
                            <CardTitle class="text-2xl">Leaderboard</CardTitle>
                            <CardDescription>
                                Top anglers by species
                            </CardDescription>
                        </div>
                    </div>
                </CardHeader>
            </Card>

            <!-- Leaderboard Grid with Scroll -->
            <div v-if="leaderboard.length > 0 || !page.props.auth.isPremium" class="flex-1 overflow-auto">
                <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 pb-4">
                <!-- Premium Upsell Card for Free Users -->
                <Card v-if="!page.props.auth.isPremium" class="bg-gradient-to-br from-amber-50/50 to-amber-100/30 dark:from-amber-950/20 dark:to-amber-900/10 border-amber-200/50 dark:border-amber-800/30">
                    <div class="p-2 h-full flex flex-col">
                        <div class="flex items-center gap-2 mb-2">
                            <div class="rounded-full bg-amber-100 p-1 dark:bg-amber-900/30 flex-shrink-0">
                                <Crown class="h-3 w-3 text-amber-600 dark:text-amber-400" />
                            </div>
                            <h3 class="text-sm font-semibold text-amber-900 dark:text-amber-100">Your Stats Compete Here!</h3>
                        </div>
                        <div class="flex-1 space-y-2">
                            <p class="text-xs text-amber-800/90 dark:text-amber-200/90">
                                Upgrade to Premium to see your name on the leaderboard and compete with other anglers!
                            </p>
                            <ul class="text-xs text-amber-800/80 dark:text-amber-200/80 space-y-1">
                                <li class="flex items-start gap-1.5">
                                    <span class="text-amber-600 dark:text-amber-400 mt-0.5">✓</span>
                                    <span>Appear on leaderboards</span>
                                </li>
                                <li class="flex items-start gap-1.5">
                                    <span class="text-amber-600 dark:text-amber-400 mt-0.5">✓</span>
                                    <span>Track all-time records</span>
                                </li>
                                <li class="flex items-start gap-1.5">
                                    <span class="text-amber-600 dark:text-amber-400 mt-0.5">✓</span>
                                    <span>Ad-free experience</span>
                                </li>
                            </ul>
                        </div>
                        <Button class="w-full mt-2 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white text-xs h-7">
                            <Crown class="mr-1 h-3 w-3" />
                            Upgrade Now
                        </Button>
                    </div>
                </Card>

                <Card v-for="entry in leaderboard" :key="entry.species" class="bg-gradient-to-br from-slate-50/30 to-transparent dark:from-slate-950/10">
                    <div class="p-2">
                        <div class="flex items-center gap-2 mb-1">
                            <div class="rounded-full bg-teal-100 p-1 dark:bg-teal-900/30 flex-shrink-0">
                                <Fish class="h-3 w-3 text-teal-600 dark:text-teal-400" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-sm font-semibold truncate">{{ entry.species }}</h3>
                            </div>
                            <p class="capitalize text-xs text-muted-foreground flex-shrink-0">
                                {{ entry.water_type }}
                            </p>
                        </div>
                        <div>
                        <!-- Biggest Fish -->
                        <div v-if="entry.biggest_fish" class="flex items-start gap-2 py-1">
                            <Award class="h-3 w-3 text-amber-600 dark:text-amber-400 mt-0.5 flex-shrink-0" />
                            <div class="flex-1 min-w-0">
                                <div class="flex items-baseline justify-between gap-2">
                                    <span class="text-xs font-medium text-muted-foreground">Biggest Fish</span>
                                    <span class="text-xs font-semibold">{{ formatSize(entry.biggest_fish.size) }}"</span>
                                </div>
                                <div class="font-semibold text-sm truncate">{{ entry.biggest_fish.user_name }}</div>
                            </div>
                        </div>

                        <!-- Separator -->
                        <hr v-if="entry.biggest_fish && entry.most_caught" class="border-border my-1" />

                        <!-- Most Caught -->
                        <div v-if="entry.most_caught" class="flex items-start gap-2 py-1">
                            <TrendingUp class="h-3 w-3 text-green-600 dark:text-green-400 mt-0.5 flex-shrink-0" />
                            <div class="flex-1 min-w-0">
                                <div class="flex items-baseline justify-between gap-2">
                                    <span class="text-xs font-medium text-muted-foreground">Most Caught</span>
                                    <span class="text-xs font-semibold">{{ entry.most_caught.total }}</span>
                                </div>
                                <div class="font-semibold text-sm truncate">{{ entry.most_caught.user_name }}</div>
                            </div>
                        </div>
                        </div>
                    </div>
                </Card>
                </div>
            </div>

            <!-- Empty State -->
            <Card v-else class="bg-gradient-to-br from-slate-50/30 to-transparent dark:from-slate-950/10">
                <CardContent class="flex flex-col items-center justify-center py-12">
                    <Trophy class="h-12 w-12 text-muted-foreground mb-4" />
                    <p class="text-lg font-medium text-muted-foreground">No leaderboard data yet</p>
                    <p class="text-sm text-muted-foreground">Start logging catches to see leaders!</p>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

