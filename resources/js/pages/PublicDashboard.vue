<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import PremiumFeatureDialog from '@/components/PremiumFeatureDialog.vue';
import DashboardCardHeader from '@/components/dashboard/DashboardCardHeader.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { NativeSelect, NativeSelectOption } from '@/components/ui/native-select';
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Fish, TrendingUp, Award, Target, BarChart3, BarChart2, Calendar, X, Flame, MapPin, Crown, Moon, Sun, Waves } from 'lucide-vue-next';
import { computed, ref, watch, nextTick } from 'vue';

interface Stats {
    totalCatches: number;
    totalTrips: number;
    topFish: string | null;
    topFishCount: number;
    biggestCatch: {
        size: number;
        species: string;
        location: string;
        date: string;
        style: string | null;
        fly: string | null;
        rod: string | null;
        friends: string[];
        notes: string | null;
    } | null;
    secondBiggestCatch: {
        size: number;
        species: string;
        location: string;
        date: string;
        style: string | null;
        fly: string | null;
        rod: string | null;
        friends: string[];
        notes: string | null;
    } | null;
}

interface SpeciesData {
    species: string;
    water_type: string | null;
    total_caught: number;
    biggest_size: number;
    trip_count: number;
}

interface ChartData {
    month: string;
    total: number;
}

interface TopPerformer {
    name: string;
    total: number;
    days?: number;
}

interface BiggestFishFly {
    name: string;
    size: number;
    days: number;
}

interface YearStats {
    daysFished: number;
    daysWithFish: number;
    daysSkunked: number;
    mostInDay: number;
    successRate: number;
}

interface CatchOverTime {
    date: string;
    total: number;
}

interface StreakStats {
    currentStreak: number;
    longestStreak: number;
}

interface FavoriteWeekday {
    day: string;
    count: number;
}

interface UserInfo {
    id: number;
    name: string;
    email: string;
    member_since: string;
}

interface MonthPieData {
    month: string;
    total_caught: number;
}

interface MoonPhaseData {
    moon_phase: string;
    total_caught: number;
}

interface SunPhaseData {
    time_of_day: string;
    total_caught: number;
}

interface FlyTypePerformer {
    type: string;
    total: number;
    days: number;
}

interface FlyColorPerformer {
    color: string;
    total: number;
    days: number;
}

interface BadgeData {
    id: number;
    name: string;
    icon: string;
    description: string;
    rarity: string;
    rarity_colors: {
        bg: string;
        text: string;
        border: string;
    };
    earned_at: string;
}

interface BadgesInfo {
    earned: BadgeData[];
    totalEarned: number;
    totalAvailable: number;
}

interface SpeciesByWaterType {
    freshwater: number;
    saltwater: number;
}

interface QuantityVsQuality {
    high_quantity_avg_size: number | null;
    low_quantity_avg_size: number | null;
}

const props = defineProps<{
    user: UserInfo;
    stats: Stats;
    allSpecies: SpeciesData[];
    topSpeciesBySize: SpeciesData[];
    catchesByMonth: ChartData[];
    catchesByMonthPie: MonthPieData[];
    catchesByMoonPhase: MoonPhaseData[];
    catchesBySunPhase: SunPhaseData[];
    mostSuccessfulFly: TopPerformer | null;
    biggestFishFly: BiggestFishFly | null;
    mostSuccessfulFlyType: FlyTypePerformer | null;
    mostSuccessfulFlyColor: FlyColorPerformer | null;
    yearStats: YearStats;
    catchesOverTime: CatchOverTime[];
    streakStats: StreakStats;
    favoriteWeekday: FavoriteWeekday | null;
    speciesByWaterType: SpeciesByWaterType;
    quantityVsQuality: QuantityVsQuality;
    availableYears: string[];
    selectedYear: string;
    badges: BadgesInfo;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Following',
        href: '/following',
    },
    {
        title: `${props.user.name}'s Dashboard`,
        href: `/users/${props.user.id}/dashboard`,
    },
];

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};

const formatChartDate = (dateString: string) => {
    const date = new Date(dateString);
    // For monthly data (day is 1st), show just month and year
    if (date.getDate() === 1) {
        return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short' });
    }
    // For weekly/daily data, show full date
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};

const formatSize = (size: number) => {
    // Remove decimals if they are .0 or .00
    const num = parseFloat(size.toString());
    return num % 1 === 0 ? Math.floor(num).toString() : num.toString();
};

// Year filter
const selectedYearFilter = ref(props.selectedYear);
const showPremiumDialog = ref(false);
const page = usePage();
const currentYear = new Date().getFullYear().toString();

// Watch for year filter changes and reload data
watch(selectedYearFilter, async (newYear, oldYear) => {
    // Check if user is trying to select a non-current year and is not premium
    // Exception: user_id 1 can be viewed by anyone
    if (!page.props.auth.isPremium && newYear !== currentYear && props.user.id !== 1) {
        // Show premium dialog
        showPremiumDialog.value = true;
        // Revert the selection after showing dialog
        await nextTick();
        selectedYearFilter.value = oldYear;
        return;
    }

    router.get(`/users/${props.user.id}/dashboard`, { year: newYear }, {
        preserveState: true,
        preserveScroll: true,
    });
});

// Display year label
const yearLabel = computed(() => {
    return selectedYearFilter.value === 'lifetime' ? 'Lifetime' : selectedYearFilter.value;
});

// Hovered slice for pie chart
const hoveredSlice = ref<number | null>(null);

// Color palette for pie chart - expanded to support more species
const speciesColors = [
    '#3b82f6', // blue
    '#10b981', // green
    '#f59e0b', // amber
    '#ef4444', // red
    '#8b5cf6', // purple
    '#ec4899', // pink
    '#06b6d4', // cyan
    '#84cc16', // lime
    '#f97316', // orange
    '#6366f1', // indigo
    '#14b8a6', // teal
    '#f43f5e', // rose
    '#a855f7', // violet
    '#22c55e', // emerald
    '#eab308', // yellow
    '#0ea5e9', // sky
    '#d946ef', // fuchsia
    '#64748b', // slate
    '#78716c', // stone
    '#dc2626', // red-600
];

const getSpeciesColor = (index: number) => {
    return speciesColors[index % speciesColors.length];
};

// Helper function to create pie chart slices
const pieSlices = computed(() => {
    const total = speciesStats.value.totalFish;
    if (total === 0) return [];

    // Show all species in pie chart
    const slices = [];
    let currentAngle = -90; // Start at top

    props.allSpecies.forEach((species, index) => {
        const caught = Number(species.total_caught);
        const percentage = caught / total;
        // For a single item (100%), use 359.99 degrees to avoid full circle rendering issue
        const angle = percentage >= 0.9999 ? 359.99 : percentage * 360;

        slices.push({
            path: createPieSlice(100, 100, 70, currentAngle, currentAngle + angle),
            color: getSpeciesColor(index),
            percentage: Math.round(percentage * 100),
            species: species.species,
        });

        currentAngle += angle;
    });

    return slices;
});

// Create SVG path for pie slice
const createPieSlice = (cx: number, cy: number, radius: number, startAngle: number, endAngle: number) => {
    const start = polarToCartesian(cx, cy, radius, endAngle);
    const end = polarToCartesian(cx, cy, radius, startAngle);
    const largeArcFlag = endAngle - startAngle <= 180 ? '0' : '1';

    return [
        'M', cx, cy,
        'L', start.x, start.y,
        'A', radius, radius, 0, largeArcFlag, 0, end.x, end.y,
        'Z'
    ].join(' ');
};

// Convert polar coordinates to cartesian
const polarToCartesian = (cx: number, cy: number, radius: number, angleInDegrees: number) => {
    const angleInRadians = (angleInDegrees * Math.PI) / 180.0;
    return {
        x: cx + (radius * Math.cos(angleInRadians)),
        y: cy + (radius * Math.sin(angleInRadians))
    };
};

const speciesStats = computed(() => {
    const currentYearLogs = props.allSpecies;

    // Calculate total fish caught this year from all species
    const totalFish = currentYearLogs.reduce((sum: number, species: SpeciesData) => sum + Number(species.total_caught), 0);

    // Calculate total trips this year from all species
    const totalTrips = currentYearLogs.reduce((sum: number, species: SpeciesData) => sum + Number(species.trip_count), 0);

    // Find biggest fish this year
    const biggestFish = Math.max(0, ...currentYearLogs.map((species: SpeciesData) => Number(species.biggest_size)));

    return {
        totalFish,
        totalTrips,
        biggestFish,
    };
});

// Top 7 species for the list display
const topSpecies = computed(() => {
    return props.allSpecies.slice(0, 7);
});

// Hovered slices for additional pie charts
const hoveredMonthSlice = ref<number | null>(null);
const hoveredMoonSlice = ref<number | null>(null);
const hoveredSunSlice = ref<number | null>(null);

// Month colors for pie chart
const monthColors = [
    '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899',
    '#06b6d4', '#84cc16', '#f97316', '#6366f1', '#14b8a6', '#f43f5e',
];
const getMonthColor = (index: number) => monthColors[index % monthColors.length];

// Moon phase colors
const getMoonPhaseColor = (phase: string) => {
    const colors: Record<string, string> = {
        'New Moon': '#1e293b',
        'Waxing Crescent': '#475569',
        'First Quarter': '#64748b',
        'Waxing Gibbous': '#94a3b8',
        'Full Moon': '#f8fafc',
        'Waning Gibbous': '#cbd5e1',
        'Last Quarter': '#94a3b8',
        'Waning Crescent': '#64748b',
    };
    return colors[phase] || '#64748b';
};

// Sun phase colors
const getSunPhaseColor = (phase: string) => {
    const colors: Record<string, string> = {
        'Pre-dawn': '#1e3a5f',
        'Morning': '#fbbf24',
        'Midday': '#f59e0b',
        'Afternoon': '#ea580c',
        'Evening': '#dc2626',
        'Night': '#1e293b',
    };
    return colors[phase] || '#64748b';
};

// Month pie slices
const monthPieSlices = computed(() => {
    const total = props.catchesByMonthPie?.reduce((sum, m) => sum + Number(m.total_caught), 0) || 0;
    if (total === 0) return [];

    const slices = [];
    let currentAngle = -90;

    props.catchesByMonthPie.forEach((month, index) => {
        const caught = Number(month.total_caught);
        const percentage = caught / total;
        // For a single item (100%), use 359.99 degrees to avoid full circle rendering issue
        const angle = percentage >= 0.9999 ? 359.99 : percentage * 360;

        slices.push({
            path: createPieSlice(100, 100, 70, currentAngle, currentAngle + angle),
            color: getMonthColor(index),
            percentage: Math.round(percentage * 100),
        });

        currentAngle += angle;
    });

    return slices;
});

// Moon phase pie slices
const moonPhasePieSlices = computed(() => {
    const total = props.catchesByMoonPhase?.reduce((sum, p) => sum + Number(p.total_caught), 0) || 0;
    if (total === 0) return [];

    const slices = [];
    let currentAngle = -90;

    props.catchesByMoonPhase.forEach((phase) => {
        const caught = Number(phase.total_caught);
        const percentage = caught / total;
        // For a single item (100%), use 359.99 degrees to avoid full circle rendering issue
        const angle = percentage >= 0.9999 ? 359.99 : percentage * 360;

        slices.push({
            path: createPieSlice(100, 100, 70, currentAngle, currentAngle + angle),
            color: getMoonPhaseColor(phase.moon_phase),
            percentage: Math.round(percentage * 100),
        });

        currentAngle += angle;
    });

    return slices;
});

// Sun phase pie slices
const sunPhasePieSlices = computed(() => {
    const total = props.catchesBySunPhase?.reduce((sum, p) => sum + Number(p.total_caught), 0) || 0;
    if (total === 0) return [];

    const slices = [];
    let currentAngle = -90;

    props.catchesBySunPhase.forEach((phase) => {
        const caught = Number(phase.total_caught);
        const percentage = caught / total;
        // For a single item (100%), use 359.99 degrees to avoid full circle rendering issue
        const angle = percentage >= 0.9999 ? 359.99 : percentage * 360;

        slices.push({
            path: createPieSlice(100, 100, 70, currentAngle, currentAngle + angle),
            color: getSunPhaseColor(phase.time_of_day),
            percentage: Math.round(percentage * 100),
        });

        currentAngle += angle;
    });

    return slices;
});
</script>

<template>
    <Head :title="`${user.name}'s Dashboard`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Navigation Tabs -->
            <Tabs :model-value="`/users/${user.id}/dashboard`" class="w-full">
                <TabsList class="grid w-full grid-cols-4">
                    <TabsTrigger
                        :value="`/users/${user.id}/dashboard`"
                        as-child
                    >
                        <Link :href="`/users/${user.id}/dashboard`">
                            Dashboard
                        </Link>
                    </TabsTrigger>
                    <TabsTrigger
                        :value="`/users/${user.id}/rods`"
                        as-child
                    >
                        <Link :href="`/users/${user.id}/rods`">
                            Rods
                        </Link>
                    </TabsTrigger>
                    <TabsTrigger
                        :value="`/users/${user.id}/fish`"
                        as-child
                    >
                        <Link :href="`/users/${user.id}/fish`">
                            Fish
                        </Link>
                    </TabsTrigger>
                    <TabsTrigger
                        :value="`/users/${user.id}/flies`"
                        as-child
                    >
                        <Link :href="`/users/${user.id}/flies`">
                            Flies
                        </Link>
                    </TabsTrigger>
                </TabsList>
            </Tabs>

            <!-- User Info Header -->
            <Card class="bg-gradient-to-br from-teal-50/50 to-transparent dark:from-teal-950/20">
                <CardHeader>
                    <CardTitle class="text-2xl">{{ user.name }}'s Dashboard</CardTitle>
                    <CardDescription>
                        {{ user.email }} • Member since {{ user.member_since }}
                    </CardDescription>
                </CardHeader>
            </Card>

            <!-- Year Filter -->
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-3xl font-bold tracking-tight">Fishing Statistics</h2>
                    <p class="text-muted-foreground">{{ yearLabel === 'Lifetime' ? 'Across all time' : 'For ' + yearLabel }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <label for="year-filter" class="text-sm font-medium">Filter by:</label>
                    <NativeSelect v-model="selectedYearFilter" id="year-filter" class="w-[140px]">
                        <NativeSelectOption value="lifetime">Lifetime</NativeSelectOption>
                        <NativeSelectOption v-for="year in availableYears" :key="year" :value="year">
                            {{ year }}
                        </NativeSelectOption>
                    </NativeSelect>
                </div>
            </div>

            <!-- Premium Feature Dialog -->
            <PremiumFeatureDialog
                v-model:open="showPremiumDialog"
                title="Year Filtering is a Premium Feature"
                description="Access to historical data and year filtering is only available to premium users. Upgrade to premium to view your fishing statistics from previous years and lifetime totals."
            />

            <!-- AdSense & Premium Upgrade Row (shown at top for non-premium users) -->
            <div v-if="!page.props.auth.isPremium" class="grid gap-4 md:grid-cols-2">
                <!-- Google AdSense Card -->
                <Card class="bg-gradient-to-br from-slate-50/50 to-transparent dark:from-slate-950/20">
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">Advertisement</CardTitle>
                    </CardHeader>
                    <CardContent class="pt-0 pb-4">
                        <div class="flex items-center justify-center min-h-[200px] bg-muted/30 rounded-lg border-2 border-dashed border-muted-foreground/20">
                            <p class="text-sm text-muted-foreground">Google AdSense Placeholder</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Premium Upgrade Card -->
                <Card class="bg-gradient-to-br from-amber-50/50 to-amber-100/30 dark:from-amber-950/20 dark:to-amber-900/10 border-amber-200/50 dark:border-amber-800/30">
                    <CardHeader class="pb-2">
                        <CardTitle class="flex items-center gap-2 text-lg">
                            <div class="rounded-full bg-amber-100 p-2 dark:bg-amber-900/30">
                                <Crown class="h-5 w-5 text-amber-600 dark:text-amber-400" />
                            </div>
                            <span class="text-amber-900 dark:text-amber-100">Upgrade to Premium</span>
                        </CardTitle>
                        <CardDescription class="text-amber-700/80 dark:text-amber-300/80">
                            Unlock the full Bushwhack experience
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="pt-0 pb-4 space-y-4">
                        <div class="space-y-2">
                            <p class="text-sm text-amber-900/90 dark:text-amber-100/90 font-medium">
                                Remove all advertisements and enjoy:
                            </p>
                            <ul class="text-sm text-amber-800/80 dark:text-amber-200/80 space-y-1 ml-4">
                                <li class="flex items-start gap-2">
                                    <span class="text-amber-600 dark:text-amber-400 mt-0.5">✓</span>
                                    <span>Ad-free experience across all pages</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-amber-600 dark:text-amber-400 mt-0.5">✓</span>
                                    <span>Access to historical data and year filtering</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-amber-600 dark:text-amber-400 mt-0.5">✓</span>
                                    <span>Advanced analytics and insights</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-amber-600 dark:text-amber-400 mt-0.5">✓</span>
                                    <span>Priority support</span>
                                </li>
                            </ul>
                        </div>
                        <Button
                            class="w-full bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white shadow-md"
                            @click="router.visit('/settings/subscription')"
                        >
                            <Crown class="mr-2 h-4 w-4" />
                            Upgrade Now
                        </Button>
                    </CardContent>
                </Card>
            </div>

            <!-- Row 1: Total Catches, Average per Trip, Quantity vs Quality, Species by Water Type -->
            <div class="grid gap-4 grid-cols-1 md:grid-cols-2 lg:grid-cols-4">
                <!-- Total Catches -->
                <Card class="bg-gradient-to-br from-blue-50/50 to-transparent dark:from-blue-950/20">
                    <DashboardCardHeader title="Total Catches" emoji="🐟" color="blue">
                        <template #subtitle>Across {{ stats.totalTrips }} trips</template>
                    </DashboardCardHeader>
                    <CardContent class="pb-3">
                        <div class="text-2xl font-bold text-blue-700 dark:text-blue-300">{{ stats.totalCatches }}</div>
                    </CardContent>
                </Card>

                <!-- Average per Trip -->
                <Card class="bg-gradient-to-br from-indigo-50/50 to-transparent dark:from-indigo-950/20">
                    <DashboardCardHeader title="Average per Trip" subtitle="Your catch rate efficiency" :icon="BarChart3" color="indigo" />
                    <CardContent class="pb-4 pt-1">
                        <div class="text-3xl font-bold text-indigo-700 dark:text-indigo-300">
                            {{ speciesStats.totalTrips > 0 ? (speciesStats.totalFish / speciesStats.totalTrips).toFixed(1) : '0' }}
                        </div>
                        <p class="text-sm text-muted-foreground mt-1">
                            Fish per outing
                        </p>
                    </CardContent>
                </Card>

                <!-- Quantity vs Quality -->
                <Card class="bg-gradient-to-br from-teal-50/30 to-cyan-50/30 dark:from-teal-950/10 dark:to-cyan-950/10">
                    <DashboardCardHeader title="Quantity vs Quality" subtitle="High catch days vs big fish" :icon="BarChart2" color="teal" gradientTo="cyan" />
                    <CardContent class="pt-0 pb-4">
                        <div v-if="quantityVsQuality.high_quantity_avg_size || quantityVsQuality.low_quantity_avg_size" class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-sm">High catch days (5+)</span>
                                <span class="font-medium text-teal-600 dark:text-teal-400">{{ quantityVsQuality.high_quantity_avg_size || 'N/A' }}" avg</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm">Low catch days (&lt;5)</span>
                                <span class="font-medium text-cyan-600 dark:text-cyan-400">{{ quantityVsQuality.low_quantity_avg_size || 'N/A' }}" avg</span>
                            </div>
                        </div>
                        <p v-else class="text-muted-foreground">No size data yet</p>
                    </CardContent>
                </Card>

                <!-- Species by Water Type -->
                <Card class="bg-gradient-to-br from-violet-50/30 to-purple-50/30 dark:from-violet-950/10 dark:to-purple-950/10">
                    <DashboardCardHeader title="Species by Water Type" subtitle="Unique species per water type" :icon="Waves" color="violet" gradientTo="purple" />
                    <CardContent class="pt-0 pb-4">
                        <div v-if="speciesByWaterType.freshwater > 0 || speciesByWaterType.saltwater > 0" class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-sm">Freshwater</span>
                                <span class="font-bold text-violet-600 dark:text-violet-400">{{ speciesByWaterType.freshwater }} species</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm">Saltwater</span>
                                <span class="font-bold text-purple-600 dark:text-purple-400">{{ speciesByWaterType.saltwater }} species</span>
                            </div>
                        </div>
                        <p v-else class="text-muted-foreground">No water type data yet</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Row 2: Biggest Catch, Runner Up, Species Pie Chart -->
            <div class="grid gap-4 grid-cols-1 md:grid-cols-2 lg:grid-cols-4">
                <!-- Biggest Catch -->
                <Card v-if="stats.biggestCatch" class="bg-gradient-to-br from-yellow-50/30 to-transparent dark:from-yellow-950/10">
                    <DashboardCardHeader title="Biggest Catch" subtitle="Largest fish caught" emoji="🏆" color="yellow" />
                    <CardContent class="pt-0 pb-3">
                        <div class="space-y-2">
                            <div class="space-y-1">
                                <div class="text-3xl font-bold text-yellow-700 dark:text-yellow-300">{{ formatSize(stats.biggestCatch.size) }}"</div>
                                <div class="text-lg font-medium">{{ stats.biggestCatch.species }}</div>
                            </div>

                            <div class="space-y-1 text-sm">
                                <div v-if="stats.biggestCatch.location" class="flex items-start gap-2">
                                    <MapPin class="h-4 w-4 text-muted-foreground mt-0.5 flex-shrink-0" />
                                    <span class="text-muted-foreground">{{ stats.biggestCatch.location }}</span>
                                </div>
                                <div class="flex items-start gap-2">
                                    <Calendar class="h-4 w-4 text-muted-foreground mt-0.5 flex-shrink-0" />
                                    <span class="text-muted-foreground">{{ formatDate(stats.biggestCatch.date) }}</span>
                                </div>
                                <div v-if="stats.biggestCatch.rod" class="flex items-start gap-2">
                                    <TrendingUp class="h-4 w-4 text-muted-foreground mt-0.5 flex-shrink-0" />
                                    <span class="text-muted-foreground">{{ stats.biggestCatch.rod }}</span>
                                </div>
                                <div v-if="stats.biggestCatch.fly" class="flex items-start gap-2">
                                    <Target class="h-4 w-4 text-muted-foreground mt-0.5 flex-shrink-0" />
                                    <span class="text-muted-foreground">{{ stats.biggestCatch.fly }}</span>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card v-else class="bg-gradient-to-br from-gray-50/30 to-transparent dark:from-gray-950/10">
                    <DashboardCardHeader title="Biggest Catch" subtitle="Largest fish caught" emoji="🏆" color="gray" />
                    <CardContent class="pt-0 pb-3">
                        <p class="text-muted-foreground">No catches recorded yet</p>
                    </CardContent>
                </Card>

                <!-- Runner Up / Second Biggest Catch -->
                <Card v-if="stats.secondBiggestCatch" class="bg-gradient-to-br from-orange-50/30 to-transparent dark:from-orange-950/10">
                    <DashboardCardHeader title="Runner Up" subtitle="Second largest catch" emoji="🥈" color="orange" />
                    <CardContent class="pt-0 pb-3">
                        <div class="space-y-2">
                            <div class="space-y-1">
                                <div class="text-3xl font-bold text-orange-700 dark:text-orange-300">{{ formatSize(stats.secondBiggestCatch.size) }}"</div>
                                <div class="text-lg font-medium">{{ stats.secondBiggestCatch.species }}</div>
                            </div>
                            <div class="space-y-1 text-sm">
                                <div v-if="stats.secondBiggestCatch.location" class="flex items-start gap-2">
                                    <MapPin class="h-4 w-4 text-muted-foreground mt-0.5 flex-shrink-0" />
                                    <span class="text-muted-foreground">{{ stats.secondBiggestCatch.location }}</span>
                                </div>
                                <div class="flex items-start gap-2">
                                    <Calendar class="h-4 w-4 text-muted-foreground mt-0.5 flex-shrink-0" />
                                    <span class="text-muted-foreground">{{ formatDate(stats.secondBiggestCatch.date) }}</span>
                                </div>
                                <div v-if="stats.secondBiggestCatch.rod" class="flex items-start gap-2">
                                    <TrendingUp class="h-4 w-4 text-muted-foreground mt-0.5 flex-shrink-0" />
                                    <span class="text-muted-foreground">{{ stats.secondBiggestCatch.rod }}</span>
                                </div>
                                <div v-if="stats.secondBiggestCatch.fly" class="flex items-start gap-2">
                                    <Target class="h-4 w-4 text-muted-foreground mt-0.5 flex-shrink-0" />
                                    <span class="text-muted-foreground">{{ stats.secondBiggestCatch.fly }}</span>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Species Distribution Pie Chart -->
                <Card class="md:col-span-2 bg-gradient-to-br from-pink-50/30 to-transparent dark:from-pink-950/10">
                    <DashboardCardHeader title="Species Caught" subtitle="Variety of fish caught" emoji="🐠" color="pink" />
                    <CardContent class="pt-0 pb-3">
                        <div v-if="allSpecies.length > 0 && speciesStats.totalFish > 0" class="flex items-center gap-4">
                            <!-- SVG Pie Chart -->
                            <div class="relative w-44 h-44 flex-shrink-0">
                                <svg class="w-full h-full" viewBox="0 0 200 200">
                                    <!-- Pie slices -->
                                    <g v-for="(slice, index) in pieSlices" :key="`slice-${index}`">
                                        <path
                                            :d="slice.path"
                                            :fill="slice.color"
                                            :class="hoveredSlice === index ? 'opacity-100' : 'opacity-80'"
                                            class="cursor-pointer transition-all hover:opacity-100"
                                            @mouseenter="hoveredSlice = index"
                                            @mouseleave="hoveredSlice = null"
                                        />
                                    </g>

                                    <!-- Center circle for donut effect -->
                                    <circle
                                        cx="100"
                                        cy="100"
                                        r="50"
                                        fill="currentColor"
                                        class="text-background"
                                    />

                                    <!-- Center text -->
                                    <text
                                        x="100"
                                        y="95"
                                        text-anchor="middle"
                                        class="text-2xl font-bold"
                                        fill="currentColor"
                                    >
                                        {{ allSpecies.length }}
                                    </text>
                                    <text
                                        x="100"
                                        y="110"
                                        text-anchor="middle"
                                        class="text-xs text-muted-foreground"
                                        fill="currentColor"
                                    >
                                        Species
                                    </text>
                                </svg>
                            </div>

                            <!-- Legend -->
                            <div class="flex-1 space-y-1 max-h-44 overflow-y-auto">
                                <div
                                    v-for="(species, index) in allSpecies"
                                    :key="species.species"
                                    class="flex items-center justify-between gap-2 p-1.5 rounded hover:bg-muted/50 cursor-pointer transition-colors"
                                    :class="hoveredSlice === index ? 'bg-muted' : ''"
                                    @mouseenter="hoveredSlice = index"
                                    @mouseleave="hoveredSlice = null"
                                >
                                    <div class="flex items-center gap-2 min-w-0">
                                        <div
                                            class="w-2.5 h-2.5 rounded-full flex-shrink-0"
                                            :style="{ backgroundColor: getSpeciesColor(index) }"
                                        ></div>
                                        <span class="text-xs font-medium truncate">{{ species.species }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 flex-shrink-0">
                                        <span class="text-xs font-bold">{{ species.total_caught }}</span>
                                        <span class="text-xs text-muted-foreground">
                                            ({{ Math.round((species.total_caught / speciesStats.totalFish) * 100) }}%)
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-4">
                            <p class="text-muted-foreground text-sm">No catches recorded yet</p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Row 3: Days Fished, Successful Days, Days Skunked, Most in a Day -->
            <div class="grid gap-4 grid-cols-1 md:grid-cols-2 lg:grid-cols-4">
                <!-- Days Fished -->
                <Card class="bg-gradient-to-br from-sky-50/50 to-transparent dark:from-sky-950/20">
                    <DashboardCardHeader title="Days Fished" subtitle="Total days on the water" :icon="Calendar" color="sky" />
                    <CardContent class="pb-3">
                        <div class="text-2xl font-bold text-sky-700 dark:text-sky-300">{{ yearStats.daysFished }}</div>
                        <p class="text-xs text-muted-foreground">
                            Total days on the water
                        </p>
                    </CardContent>
                </Card>

                <!-- Successful Days -->
                <Card class="bg-gradient-to-br from-green-50/50 to-transparent dark:from-green-950/20">
                    <DashboardCardHeader title="Successful Days" subtitle="Days with at least one catch" emoji="🐡" color="green" />
                    <CardContent class="pb-3">
                        <div class="text-2xl font-bold text-green-700 dark:text-green-300">{{ yearStats.daysWithFish }}</div>
                        <div class="mt-1 flex items-center gap-2">
                            <div class="flex-1 h-2 bg-muted rounded-full overflow-hidden">
                                <div
                                    class="h-full bg-green-500 transition-all duration-500"
                                    :style="{ width: `${yearStats.successRate}%` }"
                                ></div>
                            </div>
                            <span class="text-xs font-medium text-green-600 dark:text-green-400">{{ yearStats.successRate }}%</span>
                        </div>
                    </CardContent>
                </Card>

                <!-- Days Skunked -->
                <Card class="bg-gradient-to-br from-red-50/50 to-transparent dark:from-red-950/20">
                    <DashboardCardHeader title="Days Skunked" subtitle="Days without a catch" emoji="🦨" color="red" />
                    <CardContent class="pb-3">
                        <div class="text-2xl font-bold text-red-700 dark:text-red-300">{{ yearStats.daysSkunked }}</div>
                        <p class="text-xs text-muted-foreground">
                            Where the fish at?
                        </p>
                    </CardContent>
                </Card>

                <!-- Most in a Day -->
                <Card class="bg-gradient-to-br from-violet-50/50 to-transparent dark:from-violet-950/20">
                    <DashboardCardHeader title="Most in a Day" subtitle="Personal best day" emoji="🎉" color="violet" />
                    <CardContent class="pb-3">
                        <div class="text-2xl font-bold text-violet-700 dark:text-violet-300">{{ yearStats.mostInDay }}</div>
                        <p class="text-xs text-muted-foreground">
                            Personal best day
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Row 4: Most Successful Fly, Biggest Fish Fly, Most Successful Fly Type, Most Successful Fly Color -->
            <div class="grid gap-4 md:grid-cols-4">
                <!-- Most Successful Fly (by quantity) -->
                <Card class="bg-gradient-to-br from-rose-50/30 to-transparent dark:from-rose-950/10">
                    <DashboardCardHeader title="Most Successful Fly" subtitle="Your top producing fly" emoji="🪝" color="rose" />
                    <CardContent class="pt-0 pb-4">
                        <div v-if="mostSuccessfulFly" class="space-y-2">
                            <div class="text-xl font-bold text-rose-700 dark:text-rose-300">{{ mostSuccessfulFly.name }}</div>
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center gap-2 rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-medium text-rose-800 dark:bg-rose-900/30 dark:text-rose-300">
                                    <span>🎣</span>
                                    <span>{{ mostSuccessfulFly.total }} fish</span>
                                </span>
                                <span class="text-xs text-muted-foreground">
                                    {{ mostSuccessfulFly.days }} days used
                                </span>
                            </div>
                        </div>
                        <div v-else class="text-muted-foreground">
                            No data yet
                        </div>
                    </CardContent>
                </Card>

                <!-- Biggest Fish Fly -->
                <Card class="bg-gradient-to-br from-teal-50/30 to-transparent dark:from-teal-950/10">
                    <DashboardCardHeader title="Biggest Fish Fly" subtitle="Fly for trophy catches" emoji="🏆" color="teal" />
                    <CardContent class="pt-0 pb-4">
                        <div v-if="biggestFishFly" class="space-y-2">
                            <div class="text-xl font-bold text-teal-700 dark:text-teal-300">{{ biggestFishFly.name }}</div>
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center gap-2 rounded-full bg-teal-100 px-2.5 py-0.5 text-xs font-medium text-teal-800 dark:bg-teal-900/30 dark:text-teal-300">
                                    <span>🏆</span>
                                    <span>{{ formatSize(biggestFishFly.size) }}" fish</span>
                                </span>
                                <span class="text-xs text-muted-foreground">
                                    {{ biggestFishFly.days }} days used
                                </span>
                            </div>
                        </div>
                        <div v-else class="text-muted-foreground">
                            No data yet
                        </div>
                    </CardContent>
                </Card>

                <!-- Most Successful Type -->
                <Card class="bg-gradient-to-br from-purple-50/30 to-transparent dark:from-purple-950/10">
                    <DashboardCardHeader title="Most Successful Type" subtitle="Best fly type" emoji="⭐" color="purple" />
                    <CardContent class="pt-0 pb-4">
                        <div v-if="mostSuccessfulFlyType" class="space-y-2">
                            <div class="text-xl font-bold text-purple-700 dark:text-purple-300">{{ mostSuccessfulFlyType.type }}</div>
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center gap-2 rounded-full bg-purple-100 px-2.5 py-0.5 text-xs font-medium text-purple-800 dark:bg-purple-900/30 dark:text-purple-300">
                                    <span>🎣</span>
                                    <span>{{ mostSuccessfulFlyType.total }} fish</span>
                                </span>
                                <span class="text-xs text-muted-foreground">
                                    {{ mostSuccessfulFlyType.days }} days used
                                </span>
                            </div>
                        </div>
                        <div v-else class="text-muted-foreground">
                            No data yet
                        </div>
                    </CardContent>
                </Card>

                <!-- Most Successful Color -->
                <Card class="bg-gradient-to-br from-indigo-50/30 to-transparent dark:from-indigo-950/10">
                    <DashboardCardHeader title="Most Successful Color" subtitle="Best fly color" emoji="🎨" color="indigo" />
                    <CardContent class="pt-0 pb-4">
                        <div v-if="mostSuccessfulFlyColor" class="space-y-2">
                            <div class="text-xl font-bold text-indigo-700 dark:text-indigo-300">{{ mostSuccessfulFlyColor.color }}</div>
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center gap-2 rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-medium text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300">
                                    <span>🎨</span>
                                    <span>{{ mostSuccessfulFlyColor.total }} fish</span>
                                </span>
                                <span class="text-xs text-muted-foreground">
                                    {{ mostSuccessfulFlyColor.days }} days used
                                </span>
                            </div>
                        </div>
                        <div v-else class="text-muted-foreground">
                            No data yet
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Row 5: Catches by Month, Catches by Moon Phase, Catches by Sun Phase -->
            <div class="grid gap-4 md:grid-cols-3">
                <!-- Fish Caught per Month (Pie Chart) -->
                <Card class="bg-gradient-to-br from-blue-50/30 to-transparent dark:from-blue-950/10">
                    <DashboardCardHeader title="Fish Caught per Month" subtitle="Monthly catch distribution" :icon="Calendar" color="blue" />
                    <CardContent class="pt-0 pb-3">
                        <div v-if="catchesByMonthPie && catchesByMonthPie.length > 0" class="flex items-center gap-4">
                            <div class="relative w-44 h-44 flex-shrink-0">
                                <svg class="w-full h-full" viewBox="0 0 200 200">
                                    <g v-for="(slice, index) in monthPieSlices" :key="`month-slice-${index}`">
                                        <path
                                            :d="slice.path"
                                            :fill="slice.color"
                                            :class="hoveredMonthSlice === index ? 'opacity-100' : 'opacity-80'"
                                            class="cursor-pointer transition-all hover:opacity-100"
                                            @mouseenter="hoveredMonthSlice = index"
                                            @mouseleave="hoveredMonthSlice = null"
                                        />
                                    </g>
                                    <circle cx="100" cy="100" r="50" fill="currentColor" class="text-background" />
                                    <text x="100" y="95" text-anchor="middle" class="text-2xl font-bold" fill="currentColor">{{ catchesByMonthPie.length }}</text>
                                    <text x="100" y="110" text-anchor="middle" class="text-xs text-muted-foreground" fill="currentColor">Months</text>
                                </svg>
                            </div>
                            <div class="flex-1 space-y-1 max-h-44 overflow-y-auto">
                                <div v-for="(month, index) in catchesByMonthPie" :key="month.month"
                                    class="flex items-center justify-between gap-2 p-1.5 rounded hover:bg-muted/50 cursor-pointer transition-colors"
                                    :class="hoveredMonthSlice === index ? 'bg-muted' : ''"
                                    @mouseenter="hoveredMonthSlice = index" @mouseleave="hoveredMonthSlice = null">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <div class="w-2.5 h-2.5 rounded-full flex-shrink-0" :style="{ backgroundColor: getMonthColor(index) }"></div>
                                        <span class="text-xs font-medium truncate">{{ month.month }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 flex-shrink-0">
                                        <span class="text-xs font-bold">{{ month.total_caught }}</span>
                                        <span class="text-xs text-muted-foreground">({{ monthPieSlices[index]?.percentage }}%)</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-4">
                            <p class="text-muted-foreground text-sm">No catches recorded yet</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Fish Caught by Moon Phase -->
                <Card class="bg-gradient-to-br from-slate-50/30 to-transparent dark:from-slate-950/10">
                    <DashboardCardHeader title="Fish Caught by Moon Phase" subtitle="Lunar influence on catches" :icon="Moon" color="slate" />
                    <CardContent class="pt-0 pb-3">
                        <div v-if="catchesByMoonPhase && catchesByMoonPhase.length > 0" class="flex items-center gap-4">
                            <div class="relative w-44 h-44 flex-shrink-0">
                                <svg class="w-full h-full" viewBox="0 0 200 200">
                                    <g v-for="(slice, index) in moonPhasePieSlices" :key="`moon-slice-${index}`">
                                        <path
                                            :d="slice.path"
                                            :fill="slice.color"
                                            :class="hoveredMoonSlice === index ? 'opacity-100' : 'opacity-80'"
                                            class="cursor-pointer transition-all hover:opacity-100 stroke-slate-300 dark:stroke-slate-700"
                                            stroke-width="1"
                                            @mouseenter="hoveredMoonSlice = index"
                                            @mouseleave="hoveredMoonSlice = null"
                                        />
                                    </g>
                                    <circle cx="100" cy="100" r="50" fill="currentColor" class="text-background" />
                                    <text x="100" y="95" text-anchor="middle" class="text-2xl font-bold" fill="currentColor">{{ catchesByMoonPhase.length }}</text>
                                    <text x="100" y="110" text-anchor="middle" class="text-xs text-muted-foreground" fill="currentColor">Phases</text>
                                </svg>
                            </div>
                            <div class="flex-1 space-y-1 max-h-44 overflow-y-auto">
                                <div v-for="(phase, index) in catchesByMoonPhase" :key="phase.moon_phase"
                                    class="flex items-center justify-between gap-2 p-1.5 rounded hover:bg-muted/50 cursor-pointer transition-colors"
                                    :class="hoveredMoonSlice === index ? 'bg-muted' : ''"
                                    @mouseenter="hoveredMoonSlice = index" @mouseleave="hoveredMoonSlice = null">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <div class="w-2.5 h-2.5 rounded-full flex-shrink-0 border border-slate-300 dark:border-slate-700" :style="{ backgroundColor: getMoonPhaseColor(phase.moon_phase) }"></div>
                                        <span class="text-xs font-medium truncate">{{ phase.moon_phase }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 flex-shrink-0">
                                        <span class="text-xs font-bold">{{ phase.total_caught }}</span>
                                        <span class="text-xs text-muted-foreground">({{ moonPhasePieSlices[index]?.percentage }}%)</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-4">
                            <p class="text-muted-foreground text-sm">No moon phase data yet</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Fish Caught by Sun Phase -->
                <Card class="bg-gradient-to-br from-amber-50/30 to-transparent dark:from-amber-950/10">
                    <DashboardCardHeader title="Fish Caught by Sun Phase" subtitle="Time of day distribution" :icon="Sun" color="amber" />
                    <CardContent class="pt-0 pb-3">
                        <div v-if="catchesBySunPhase && catchesBySunPhase.length > 0" class="flex items-center gap-4">
                            <div class="relative w-44 h-44 flex-shrink-0">
                                <svg class="w-full h-full" viewBox="0 0 200 200">
                                    <g v-for="(slice, index) in sunPhasePieSlices" :key="`sun-slice-${index}`">
                                        <path
                                            :d="slice.path"
                                            :fill="slice.color"
                                            :class="hoveredSunSlice === index ? 'opacity-100' : 'opacity-80'"
                                            class="cursor-pointer transition-all hover:opacity-100"
                                            @mouseenter="hoveredSunSlice = index"
                                            @mouseleave="hoveredSunSlice = null"
                                        />
                                    </g>
                                    <circle cx="100" cy="100" r="50" fill="currentColor" class="text-background" />
                                    <text x="100" y="95" text-anchor="middle" class="text-2xl font-bold" fill="currentColor">{{ catchesBySunPhase.length }}</text>
                                    <text x="100" y="110" text-anchor="middle" class="text-xs text-muted-foreground" fill="currentColor">Phases</text>
                                </svg>
                            </div>
                            <div class="flex-1 space-y-1 max-h-44 overflow-y-auto">
                                <div v-for="(phase, index) in catchesBySunPhase" :key="phase.time_of_day"
                                    class="flex items-center justify-between gap-2 p-1.5 rounded hover:bg-muted/50 cursor-pointer transition-colors"
                                    :class="hoveredSunSlice === index ? 'bg-muted' : ''"
                                    @mouseenter="hoveredSunSlice = index" @mouseleave="hoveredSunSlice = null">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <div class="w-2.5 h-2.5 rounded-full flex-shrink-0" :style="{ backgroundColor: getSunPhaseColor(phase.time_of_day) }"></div>
                                        <span class="text-xs font-medium truncate">{{ phase.time_of_day }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 flex-shrink-0">
                                        <span class="text-xs font-bold">{{ phase.total_caught }}</span>
                                        <span class="text-xs text-muted-foreground">({{ sunPhasePieSlices[index]?.percentage }}%)</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-4">
                            <p class="text-muted-foreground text-sm">No sun phase data yet</p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Top Species Cards -->
            <div class="grid gap-4 md:grid-cols-2">
                <!-- Top Species by Size -->
                <Card class="bg-gradient-to-br from-orange-50/30 to-transparent dark:from-orange-950/10">
                    <DashboardCardHeader title="Top Species by Size" subtitle="Biggest fish per species" emoji="📏" color="orange" />
                    <CardContent class="pt-0 pb-1">
                        <div v-if="topSpeciesBySize && topSpeciesBySize.length > 0" class="space-y-2">
                            <div v-for="(species, index) in topSpeciesBySize.slice(0, 5)" :key="species.species" class="flex items-center gap-3 pb-2 border-b last:border-0">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300 text-sm font-bold flex-shrink-0">
                                    {{ index + 1 }}
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-medium">{{ species.species }}</span>
                                        <span v-if="index === 0">🥇</span>
                                        <span v-else-if="index === 1">🥈</span>
                                        <span v-else-if="index === 2">🥉</span>
                                    </div>
                                    <div class="text-xs text-muted-foreground flex items-center gap-2">
                                        <span>Largest catch:</span>
                                        <span class="font-medium text-orange-700 dark:text-orange-300">{{ formatSize(species.biggest_size) }}"</span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs text-muted-foreground">{{ species.total_caught }} catches</div>
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-muted-foreground">No species data available</p>
                    </CardContent>
                </Card>

                <!-- Top Species by Count -->
                <Card class="bg-gradient-to-br from-yellow-50/30 to-transparent dark:from-yellow-950/10">
                    <DashboardCardHeader title="Top Species by Count" subtitle="Your most caught species" emoji="🐠" color="yellow" />
                    <CardContent class="pt-0 pb-1">
                        <div v-if="topSpecies.length > 0" class="space-y-2">
                            <div v-for="(species, index) in topSpecies" :key="species.species" class="flex items-center gap-3 pb-2 border-b last:border-0">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300 text-sm font-bold flex-shrink-0">
                                    {{ index + 1 }}
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-medium">{{ species.species }}</span>
                                        <span v-if="index === 0">🥇</span>
                                        <span v-else-if="index === 1">🥈</span>
                                        <span v-else-if="index === 2">🥉</span>
                                    </div>
                                    <div class="text-xs text-muted-foreground">{{ species.total_caught }} catches</div>
                                </div>
                                <div v-if="species.biggest_size > 0" class="text-right">
                                    <div class="text-xs font-medium text-yellow-700 dark:text-yellow-300">{{ formatSize(species.biggest_size) }}"</div>
                                    <div class="text-xs text-muted-foreground">biggest</div>
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-muted-foreground">No species data available</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Badges Section -->
            <Card v-if="badges && badges.earned.length > 0" class="bg-gradient-to-br from-purple-50/30 to-pink-50/30 dark:from-purple-950/10 dark:to-pink-950/10">
                <DashboardCardHeader title="Achievement Badges" emoji="🏅" color="purple" gradientTo="pink">
                    <template #subtitle>{{ badges.totalEarned }} of {{ badges.totalAvailable }} badges earned</template>
                </DashboardCardHeader>
                <CardContent class="pt-0 pb-4">
                    <div class="flex flex-wrap gap-1">
                        <div
                            v-for="badge in badges.earned"
                            :key="badge.id"
                            class="flex items-center gap-0.5 px-1.5 py-0.5 bg-purple-100/50 dark:bg-purple-900/20 rounded-full"
                            :title="badge.description"
                        >
                            <span class="text-sm">{{ badge.icon }}</span>
                            <span class="text-xs font-medium">{{ badge.name }}</span>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
