<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import PremiumFeatureDialog from '@/components/PremiumFeatureDialog.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { NativeSelect, NativeSelectOption } from '@/components/ui/native-select';
import { dashboard, fishingLog } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Fish, MapPin, Users, TrendingUp, Award, Target, BarChart3, Calendar, X, Flame, Crown } from 'lucide-vue-next';
import { computed, ref, watch, nextTick } from 'vue';

interface Stats {
    totalCatches: number;
    totalTrips: number;
    totalLocations: number;
    totalFriends: number;
    favoriteLocation: string | null;
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

interface LocationData {
    name: string;
    city: string | null;
    state: string | null;
    total: number;
}

interface LocationSizeData {
    name: string;
    city: string | null;
    state: string | null;
    biggest_size: number;
}

interface SpeciesSizeData {
    species: string;
    water_type: string | null;
    biggest_size: number;
    total_caught: number;
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

interface MonthData {
    month: string;
    total_caught: number;
}

interface MoonPhaseData {
    moon_phase: string;
    total_caught: number;
}

const props = defineProps<{
    stats: Stats;
    allSpecies: SpeciesData[];
    catchesByMonth: ChartData[];
    catchesByMonthPie: MonthData[];
    catchesByMoonPhase: MoonPhaseData[];
    topLocations: LocationData[];
    topLocationsBySize: LocationSizeData[];
    topSpeciesBySize: SpeciesSizeData[];
    mostProductiveLocation: TopPerformer | null;
    mostSuccessfulFly: TopPerformer | null;
    biggestFishFly: BiggestFishFly | null;
    mostSuccessfulFlyType: { type: string; total: number; days: number } | null;
    mostSuccessfulFlyColor: { color: string; total: number; days: number } | null;
    yearStats: YearStats;
    catchesOverTime: CatchOverTime[];
    streakStats: StreakStats;
    favoriteWeekday: FavoriteWeekday | null;
    availableYears: string[];
    selectedYear: string;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
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
    if (!page.props.auth.isPremium && newYear !== currentYear) {
        // Show premium dialog
        showPremiumDialog.value = true;
        // Revert the selection after showing dialog
        await nextTick();
        selectedYearFilter.value = oldYear;
        return;
    }

    router.get(dashboard().url, { year: newYear }, {
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

// Month pie chart colors
const monthColors = [
    '#3b82f6', // January - blue
    '#8b5cf6', // February - purple
    '#ec4899', // March - pink
    '#10b981', // April - green
    '#f59e0b', // May - amber
    '#ef4444', // June - red
    '#06b6d4', // July - cyan
    '#84cc16', // August - lime
    '#f97316', // September - orange
    '#6366f1', // October - indigo
    '#14b8a6', // November - teal
    '#0ea5e9', // December - sky
];

const getMonthColor = (index: number) => {
    return monthColors[index % monthColors.length];
};

// Month pie chart slices
const monthPieSlices = computed(() => {
    const total = props.catchesByMonthPie.reduce((sum, month) => sum + Number(month.total_caught), 0);
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
            month: month.month,
            total: caught,
        });

        currentAngle += angle;
    });

    return slices;
});

// Moon phase pie chart colors
const moonPhaseColors: Record<string, string> = {
    'New Moon': '#1e293b',
    'Waxing Crescent': '#475569',
    'First Quarter': '#64748b',
    'Waxing Gibbous': '#94a3b8',
    'Full Moon': '#f1f5f9',
    'Waning Gibbous': '#cbd5e1',
    'Last Quarter': '#94a3b8',
    'Waning Crescent': '#64748b',
};

const getMoonPhaseColor = (phase: string) => {
    return moonPhaseColors[phase] || '#64748b';
};

// Moon phase pie chart slices
const moonPhasePieSlices = computed(() => {
    const total = props.catchesByMoonPhase.reduce((sum, phase) => sum + Number(phase.total_caught), 0);
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
            phase: phase.moon_phase,
            total: caught,
        });

        currentAngle += angle;
    });

    return slices;
});

// Hover state for pie charts
const hoveredMonthSlice = ref<number | null>(null);
const hoveredMoonSlice = ref<number | null>(null);
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Year Filter -->
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-3xl font-bold tracking-tight">Dashboard</h2>
                    <p class="text-muted-foreground">Your fishing statistics {{ yearLabel === 'Lifetime' ? 'across all time' : 'for ' + yearLabel }}</p>
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

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card class="bg-gradient-to-br from-blue-50/50 to-transparent dark:from-blue-950/20">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-1">
                        <CardTitle class="text-sm font-medium">Total Catches</CardTitle>
                        <div class="rounded-full bg-blue-100 p-2 dark:bg-blue-900/30">
                            <Fish class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                        </div>
                    </CardHeader>
                    <CardContent class="pb-3">
                        <div class="text-2xl font-bold text-blue-700 dark:text-blue-300">{{ stats.totalCatches }}</div>
                        <p class="text-xs text-muted-foreground">Across {{ stats.totalTrips }} trips</p>
                    </CardContent>
                </Card>

                <Card class="bg-gradient-to-br from-emerald-50/50 to-transparent dark:from-emerald-950/20">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-1">
                        <CardTitle class="text-sm font-medium">Favorite Location</CardTitle>
                        <div class="rounded-full bg-emerald-100 p-2 dark:bg-emerald-900/30">
                            <MapPin class="h-4 w-4 text-emerald-600 dark:text-emerald-400" />
                        </div>
                    </CardHeader>
                    <CardContent class="pb-3">
                        <div class="text-2xl font-bold text-emerald-700 dark:text-emerald-300">{{ stats.favoriteLocation || 'N/A' }}</div>
                        <p class="text-xs text-muted-foreground">Most visited spot</p>
                    </CardContent>
                </Card>

                <Card class="bg-gradient-to-br from-amber-50/50 to-transparent dark:from-amber-950/20">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-1">
                        <CardTitle class="text-sm font-medium">Top Species</CardTitle>
                        <div class="rounded-full bg-amber-100 p-2 dark:bg-amber-900/30">
                            <TrendingUp class="h-4 w-4 text-amber-600 dark:text-amber-400" />
                        </div>
                    </CardHeader>
                    <CardContent class="pb-3">
                        <div class="text-2xl font-bold text-amber-700 dark:text-amber-300">{{ stats.topFish || 'N/A' }}</div>
                        <p class="text-xs text-muted-foreground">{{ stats.topFishCount }} caught</p>
                    </CardContent>
                </Card>

                <Card class="bg-gradient-to-br from-purple-50/50 to-transparent dark:from-purple-950/20">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-1">
                        <CardTitle class="text-sm font-medium">Fishing Buddies</CardTitle>
                        <div class="rounded-full bg-purple-100 p-2 dark:bg-purple-900/30">
                            <Users class="h-4 w-4 text-purple-600 dark:text-purple-400" />
                        </div>
                    </CardHeader>
                    <CardContent class="pb-3">
                        <div class="text-2xl font-bold text-purple-700 dark:text-purple-300">{{ stats.totalFriends }}</div>
                        <p class="text-xs text-muted-foreground">Friends fished with</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Biggest Catch & Runner Up & Species Row -->
            <div class="grid gap-4 md:grid-cols-4">
                <!-- Biggest Catch -->
                <Card v-if="stats.biggestCatch" class="bg-gradient-to-br from-yellow-50/30 to-transparent dark:from-yellow-950/10">
                    <CardHeader class="pb-1">
                        <CardTitle class="flex items-center gap-2 text-base">
                            <div class="rounded-full bg-yellow-100 p-1.5 dark:bg-yellow-900/30">
                                <Award class="h-5 w-5 text-yellow-600 dark:text-yellow-400" />
                            </div>
                            Biggest Catch
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="pt-0 pb-3">
                        <div class="space-y-2">
                            <div class="space-y-1">
                                <div class="text-3xl font-bold text-yellow-700 dark:text-yellow-300">{{ formatSize(stats.biggestCatch.size) }}"</div>
                                <div class="text-lg font-medium">{{ stats.biggestCatch.species }}</div>
                            </div>

                            <div class="space-y-1 text-sm">
                                <div class="flex items-start gap-2">
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
                    <CardHeader class="pb-1">
                        <CardTitle class="flex items-center gap-2 text-base">
                            <div class="rounded-full bg-gray-100 p-1.5 dark:bg-gray-800">
                                <Award class="h-5 w-5 text-gray-400" />
                            </div>
                            Biggest Catch
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="pt-0 pb-3">
                        <p class="text-muted-foreground">No catches recorded yet</p>
                    </CardContent>
                </Card>

                <!-- Runner Up / Second Biggest Catch -->
                <Card v-if="stats.secondBiggestCatch" class="bg-gradient-to-br from-orange-50/30 to-transparent dark:from-orange-950/10">
                    <CardHeader class="pb-1">
                        <CardTitle class="flex items-center gap-2 text-base">
                            <div class="rounded-full bg-orange-100 p-1.5 dark:bg-orange-900/30">
                                <Award class="h-5 w-5 text-orange-600 dark:text-orange-400" />
                            </div>
                            Runner Up
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="pt-0 pb-3">
                        <div class="space-y-2">
                            <div class="space-y-1">
                                <div class="text-3xl font-bold text-orange-700 dark:text-orange-300">{{ formatSize(stats.secondBiggestCatch.size) }}"</div>
                                <div class="text-lg font-medium">{{ stats.secondBiggestCatch.species }}</div>
                            </div>

                            <div class="space-y-1 text-sm">
                                <div class="flex items-start gap-2">
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

                <Card v-else class="bg-gradient-to-br from-gray-50/30 to-transparent dark:from-gray-950/10">
                    <CardHeader class="pb-1">
                        <CardTitle class="flex items-center gap-2 text-base">
                            <div class="rounded-full bg-gray-100 p-1.5 dark:bg-gray-800">
                                <Award class="h-5 w-5 text-gray-400" />
                            </div>
                            Runner Up
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="pt-0 pb-3">
                        <p class="text-muted-foreground">No second catch yet</p>
                    </CardContent>
                </Card>

                <!-- Species Distribution Pie Chart -->
                <Card class="md:col-span-2 bg-gradient-to-br from-pink-50/30 to-transparent dark:from-pink-950/10">
                    <CardHeader class="pb-1">
                        <CardTitle class="flex items-center gap-2 text-base">
                            <div class="rounded-full bg-pink-100 p-1.5 dark:bg-pink-900/30">
                                <Fish class="h-5 w-5 text-pink-600 dark:text-pink-400" />
                            </div>
                            Species Caught
                        </CardTitle>
                    </CardHeader>
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
                            <p class="text-muted-foreground text-sm mb-2">No catches recorded yet</p>
                            <Link :href="fishingLog()" class="text-sm text-primary hover:underline">
                                Log your first catch →
                            </Link>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- AdSense & Premium Upgrade Row -->
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
                        <Button class="w-full bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white shadow-md">
                            <Crown class="mr-2 h-4 w-4" />
                            Upgrade Now
                        </Button>
                    </CardContent>
                </Card>
            </div>

            <!-- Year Stats Grid -->
            <div class="grid gap-4 md:grid-cols-3">
                <!-- Favorite Weekday -->
                <Card class="bg-gradient-to-br from-cyan-50/50 to-transparent dark:from-cyan-950/20">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-1">
                        <CardTitle class="text-sm font-medium">Favorite Weekday</CardTitle>
                        <div class="rounded-full bg-cyan-100 p-2 dark:bg-cyan-900/30">
                            <Calendar class="h-4 w-4 text-cyan-600 dark:text-cyan-400" />
                        </div>
                    </CardHeader>
                    <CardContent class="pb-3">
                        <div v-if="favoriteWeekday" class="text-2xl font-bold text-cyan-700 dark:text-cyan-300">{{ favoriteWeekday.day }}</div>
                        <div v-else class="text-2xl font-bold text-muted-foreground">-</div>
                        <p class="text-xs text-muted-foreground">
                            {{ favoriteWeekday ? `${favoriteWeekday.count} trips` : 'No data yet' }}
                        </p>
                    </CardContent>
                </Card>

                <!-- Streak Tracker -->
                <Card class="bg-gradient-to-br from-orange-50/50 to-transparent dark:from-orange-950/20">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-1">
                        <CardTitle class="text-sm font-medium">Longest Streak</CardTitle>
                        <div class="rounded-full bg-orange-100 p-2 dark:bg-orange-900/30">
                            <Flame class="h-4 w-4 text-orange-600 dark:text-orange-400" />
                        </div>
                    </CardHeader>
                    <CardContent class="pb-3">
                        <div class="text-2xl font-bold text-orange-700 dark:text-orange-300">{{ streakStats.longestStreak }}</div>
                        <p class="text-xs text-muted-foreground">
                            Current streak {{ streakStats.currentStreak }}
                            <span v-if="streakStats.currentStreak > 0" class="ml-1">🔥</span>
                        </p>
                    </CardContent>
                </Card>

                <!-- Average per Trip -->
                <Card class="bg-gradient-to-br from-indigo-50/50 to-transparent dark:from-indigo-950/20">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-1">
                        <CardTitle class="text-sm font-medium">Average per Trip</CardTitle>
                        <div class="rounded-full bg-indigo-100 p-2 dark:bg-indigo-900/30">
                            <BarChart3 class="h-4 w-4 text-indigo-600 dark:text-indigo-400" />
                        </div>
                    </CardHeader>
                    <CardContent class="pb-3">
                        <div class="text-2xl font-bold text-indigo-700 dark:text-indigo-300">
                            {{ speciesStats.totalTrips > 0 ? (speciesStats.totalFish / speciesStats.totalTrips).toFixed(1) : '0' }}
                        </div>
                        <p class="text-xs text-muted-foreground">
                            Fish per outing
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Days Stats Grid -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <!-- Days Fished -->
                <Card class="bg-gradient-to-br from-sky-50/50 to-transparent dark:from-sky-950/20">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-1">
                        <CardTitle class="text-sm font-medium">Days Fished</CardTitle>
                        <div class="rounded-full bg-sky-100 p-2 dark:bg-sky-900/30">
                            <Calendar class="h-4 w-4 text-sky-600 dark:text-sky-400" />
                        </div>
                    </CardHeader>
                    <CardContent class="pb-3">
                        <div class="text-2xl font-bold text-sky-700 dark:text-sky-300">{{ yearStats.daysFished }}</div>
                        <p class="text-xs text-muted-foreground">
                            Total days on the water
                        </p>
                    </CardContent>
                </Card>

                <!-- Successful Days -->
                <Card class="bg-gradient-to-br from-green-50/50 to-transparent dark:from-green-950/20">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-1">
                        <CardTitle class="text-sm font-medium">Successful Days</CardTitle>
                        <div class="rounded-full bg-green-100 p-2 dark:bg-green-900/30">
                            <Fish class="h-4 w-4 text-green-600 dark:text-green-400" />
                        </div>
                    </CardHeader>
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
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-1">
                        <CardTitle class="text-sm font-medium">Days Skunked</CardTitle>
                        <div class="rounded-full bg-red-100 p-2 dark:bg-red-900/30">
                            <X class="h-4 w-4 text-red-600 dark:text-red-400" />
                        </div>
                    </CardHeader>
                    <CardContent class="pb-3">
                        <div class="text-2xl font-bold text-red-700 dark:text-red-300">{{ yearStats.daysSkunked }}</div>
                        <p class="text-xs text-muted-foreground">
                            Where the fish at?
                        </p>
                    </CardContent>
                </Card>

                <!-- Most in a Day -->
                <Card class="bg-gradient-to-br from-violet-50/50 to-transparent dark:from-violet-950/20">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-1">
                        <CardTitle class="text-sm font-medium">Most in a Day</CardTitle>
                        <div class="rounded-full bg-violet-100 p-2 dark:bg-violet-900/30">
                            <Award class="h-4 w-4 text-violet-600 dark:text-violet-400" />
                        </div>
                    </CardHeader>
                    <CardContent class="pb-3">
                        <div class="text-2xl font-bold text-violet-700 dark:text-violet-300">{{ yearStats.mostInDay }}</div>
                        <p class="text-xs text-muted-foreground">
                            Personal best day
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Month & Moon Phase Pie Charts -->
            <div class="grid gap-4 md:grid-cols-2">
                <!-- Fish Caught per Month -->
                <Card class="bg-gradient-to-br from-blue-50/30 to-transparent dark:from-blue-950/10">
                    <CardHeader class="pb-1">
                        <CardTitle class="flex items-center gap-2 text-base">
                            <div class="rounded-full bg-blue-100 p-1.5 dark:bg-blue-900/30">
                                <Calendar class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                            </div>
                            Fish Caught per Month
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="pt-0 pb-3">
                        <div v-if="catchesByMonthPie.length > 0" class="flex items-center gap-4">
                            <!-- SVG Pie Chart -->
                            <div class="relative w-44 h-44 flex-shrink-0">
                                <svg class="w-full h-full" viewBox="0 0 200 200">
                                    <!-- Pie slices -->
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
                                        {{ catchesByMonthPie.length }}
                                    </text>
                                    <text
                                        x="100"
                                        y="110"
                                        text-anchor="middle"
                                        class="text-xs text-muted-foreground"
                                        fill="currentColor"
                                    >
                                        Months
                                    </text>
                                </svg>
                            </div>

                            <!-- Legend -->
                            <div class="flex-1 space-y-1 max-h-44 overflow-y-auto">
                                <div
                                    v-for="(month, index) in catchesByMonthPie"
                                    :key="month.month"
                                    class="flex items-center justify-between gap-2 p-1.5 rounded hover:bg-muted/50 cursor-pointer transition-colors"
                                    :class="hoveredMonthSlice === index ? 'bg-muted' : ''"
                                    @mouseenter="hoveredMonthSlice = index"
                                    @mouseleave="hoveredMonthSlice = null"
                                >
                                    <div class="flex items-center gap-2 min-w-0">
                                        <div
                                            class="w-2.5 h-2.5 rounded-full flex-shrink-0"
                                            :style="{ backgroundColor: getMonthColor(index) }"
                                        ></div>
                                        <span class="text-xs font-medium truncate">{{ month.month }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 flex-shrink-0">
                                        <span class="text-xs font-bold">{{ month.total_caught }}</span>
                                        <span class="text-xs text-muted-foreground">
                                            ({{ monthPieSlices[index]?.percentage }}%)
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-4">
                            <p class="text-muted-foreground text-sm mb-2">No catches recorded yet</p>
                            <Link :href="fishingLog()" class="text-sm text-primary hover:underline">
                                Log your first catch →
                            </Link>
                        </div>
                    </CardContent>
                </Card>

                <!-- Fish Caught by Moon Phase -->
                <Card class="bg-gradient-to-br from-slate-50/30 to-transparent dark:from-slate-950/10">
                    <CardHeader class="pb-1">
                        <CardTitle class="flex items-center gap-2 text-base">
                            <div class="rounded-full bg-slate-100 p-1.5 dark:bg-slate-900/30">
                                <span class="text-lg">🌙</span>
                            </div>
                            Fish Caught by Moon Phase
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="pt-0 pb-3">
                        <div v-if="catchesByMoonPhase.length > 0" class="flex items-center gap-4">
                            <!-- SVG Pie Chart -->
                            <div class="relative w-44 h-44 flex-shrink-0">
                                <svg class="w-full h-full" viewBox="0 0 200 200">
                                    <!-- Pie slices -->
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
                                        {{ catchesByMoonPhase.length }}
                                    </text>
                                    <text
                                        x="100"
                                        y="110"
                                        text-anchor="middle"
                                        class="text-xs text-muted-foreground"
                                        fill="currentColor"
                                    >
                                        Phases
                                    </text>
                                </svg>
                            </div>

                            <!-- Legend -->
                            <div class="flex-1 space-y-1 max-h-44 overflow-y-auto">
                                <div
                                    v-for="(phase, index) in catchesByMoonPhase"
                                    :key="phase.moon_phase"
                                    class="flex items-center justify-between gap-2 p-1.5 rounded hover:bg-muted/50 cursor-pointer transition-colors"
                                    :class="hoveredMoonSlice === index ? 'bg-muted' : ''"
                                    @mouseenter="hoveredMoonSlice = index"
                                    @mouseleave="hoveredMoonSlice = null"
                                >
                                    <div class="flex items-center gap-2 min-w-0">
                                        <div
                                            class="w-2.5 h-2.5 rounded-full flex-shrink-0 border border-slate-300 dark:border-slate-700"
                                            :style="{ backgroundColor: getMoonPhaseColor(phase.moon_phase) }"
                                        ></div>
                                        <span class="text-xs font-medium truncate">{{ phase.moon_phase }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 flex-shrink-0">
                                        <span class="text-xs font-bold">{{ phase.total_caught }}</span>
                                        <span class="text-xs text-muted-foreground">
                                            ({{ moonPhasePieSlices[index]?.percentage }}%)
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-4">
                            <p class="text-muted-foreground text-sm mb-2">No moon phase data yet</p>
                            <Link :href="fishingLog()" class="text-sm text-primary hover:underline">
                                Log catches with moon phases →
                            </Link>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Top Performers -->
            <div class="grid gap-4 md:grid-cols-4">
                <!-- Most Successful Fly (by quantity) -->
                <Card class="bg-gradient-to-br from-rose-50/30 to-transparent dark:from-rose-950/10">
                    <CardHeader class="pb-2">
                        <CardTitle class="flex items-center gap-2">
                            <div class="rounded-full bg-rose-100 p-1.5 dark:bg-rose-900/30">
                                <Award class="h-5 w-5 text-rose-600 dark:text-rose-400" />
                            </div>
                            Most Successful Fly
                        </CardTitle>
                        <CardDescription>Most fish caught</CardDescription>
                    </CardHeader>
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
                    <CardHeader class="pb-2">
                        <CardTitle class="flex items-center gap-2">
                            <div class="rounded-full bg-teal-100 p-1.5 dark:bg-teal-900/30">
                                <Award class="h-5 w-5 text-teal-600 dark:text-teal-400" />
                            </div>
                            Biggest Fish Fly
                        </CardTitle>
                        <CardDescription>Largest fish caught</CardDescription>
                    </CardHeader>
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

                <!-- Most Successful Fly Type -->
                <Card class="bg-gradient-to-br from-purple-50/30 to-transparent dark:from-purple-950/10">
                    <CardHeader class="pb-2">
                        <CardTitle class="flex items-center gap-2">
                            <div class="rounded-full bg-purple-100 p-1.5 dark:bg-purple-900/30">
                                <Award class="h-5 w-5 text-purple-600 dark:text-purple-400" />
                            </div>
                            Most Successful Type
                        </CardTitle>
                        <CardDescription>Best fly type</CardDescription>
                    </CardHeader>
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

                <!-- Most Successful Fly Color -->
                <Card class="bg-gradient-to-br from-indigo-50/30 to-transparent dark:from-indigo-950/10">
                    <CardHeader class="pb-2">
                        <CardTitle class="flex items-center gap-2">
                            <div class="rounded-full bg-indigo-100 p-1.5 dark:bg-indigo-900/30">
                                <Award class="h-5 w-5 text-indigo-600 dark:text-indigo-400" />
                            </div>
                            Most Successful Color
                        </CardTitle>
                        <CardDescription>Best fly color</CardDescription>
                    </CardHeader>
                    <CardContent class="pt-0 pb-4">
                        <div v-if="mostSuccessfulFlyColor" class="space-y-2">
                            <div class="text-xl font-bold text-indigo-700 dark:text-indigo-300">{{ mostSuccessfulFlyColor.color }}</div>
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center gap-2 rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-medium text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300">
                                    <span>🎣</span>
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

            <!-- Top Species & Top Locations -->
            <div class="grid gap-4 md:grid-cols-2">
                <!-- Top Species Caught by Count -->
                <Card class="bg-gradient-to-br from-yellow-50/30 to-transparent dark:from-yellow-950/10">
                    <CardHeader class="pb-2">
                        <CardTitle class="flex items-center gap-2">
                            <div class="rounded-full bg-yellow-100 p-1.5 dark:bg-yellow-900/30">
                                <Fish class="h-5 w-5 text-yellow-600 dark:text-yellow-400" />
                            </div>
                            Top Species by Count
                        </CardTitle>
                        <CardDescription>Your most caught species</CardDescription>
                    </CardHeader>
                    <CardContent class="pt-0 pb-4">
                        <div v-if="topSpecies.length > 0" class="space-y-2">
                            <div v-for="(species, index) in topSpecies" :key="species.species" class="flex items-center gap-3 pb-2 border-b last:border-0">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300 text-sm font-bold flex-shrink-0">
                                    {{ index + 1 }}
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="font-medium">{{ species.species }}</span>
                                        <span v-if="species.water_type" class="text-xs px-2 py-0.5 rounded-full bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300">
                                            {{ species.water_type }}
                                        </span>
                                        <span v-if="index === 0">🥇</span>
                                        <span v-else-if="index === 1">🥈</span>
                                        <span v-else-if="index === 2">🥉</span>
                                    </div>
                                    <div class="text-sm text-muted-foreground">{{ species.total_caught }} catches</div>
                                </div>
                                <div v-if="species.biggest_size > 0" class="text-right">
                                    <div class="text-sm font-medium text-yellow-700 dark:text-yellow-300">{{ formatSize(species.biggest_size) }}"</div>
                                    <div class="text-xs text-muted-foreground">biggest</div>
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-muted-foreground">No species data available</p>
                    </CardContent>
                </Card>

                <!-- Top Species by Size -->
                <Card class="bg-gradient-to-br from-orange-50/30 to-transparent dark:from-orange-950/10">
                    <CardHeader class="pb-2">
                        <CardTitle class="flex items-center gap-2">
                            <div class="rounded-full bg-orange-100 p-1.5 dark:bg-orange-900/30">
                                <Award class="h-5 w-5 text-orange-600 dark:text-orange-400" />
                            </div>
                            Top Species by Size
                        </CardTitle>
                        <CardDescription>Biggest fish per species</CardDescription>
                    </CardHeader>
                    <CardContent class="pt-0 pb-4">
                        <div v-if="topSpeciesBySize.length > 0" class="space-y-2">
                            <div v-for="(species, index) in topSpeciesBySize" :key="species.species" class="flex items-center gap-3 pb-2 border-b last:border-0">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300 text-sm font-bold flex-shrink-0">
                                    {{ index + 1 }}
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="font-medium">{{ species.species }}</span>
                                        <span v-if="species.water_type" class="text-xs px-2 py-0.5 rounded-full bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300">
                                            {{ species.water_type }}
                                        </span>
                                        <span v-if="index === 0">🥇</span>
                                        <span v-else-if="index === 1">🥈</span>
                                        <span v-else-if="index === 2">🥉</span>
                                    </div>
                                    <div class="text-sm text-muted-foreground flex items-center gap-2">
                                        <span>Largest catch:</span>
                                        <span class="font-medium text-orange-700 dark:text-orange-300">{{ formatSize(species.biggest_size) }}"</span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm text-muted-foreground">{{ species.total_caught }} catches</div>
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-muted-foreground">No species data available</p>
                    </CardContent>
                </Card>

                <!-- Top Locations by Count -->
                <Card class="bg-gradient-to-br from-lime-50/30 to-transparent dark:from-lime-950/10">
                    <CardHeader class="pb-2">
                        <CardTitle class="flex items-center gap-2">
                            <div class="rounded-full bg-lime-100 p-1.5 dark:bg-lime-900/30">
                                <MapPin class="h-5 w-5 text-lime-600 dark:text-lime-400" />
                            </div>
                            Top Locations by Count
                        </CardTitle>
                        <CardDescription>Your most productive spots</CardDescription>
                    </CardHeader>
                    <CardContent class="pt-0 pb-4">
                        <div v-if="topLocations.length > 0" class="space-y-2">
                            <div v-for="(location, index) in topLocations" :key="location.name" class="flex items-center gap-3 pb-2 border-b last:border-0">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-lime-100 dark:bg-lime-900/30 text-lime-700 dark:text-lime-300 text-sm font-bold flex-shrink-0">
                                    {{ index + 1 }}
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="font-medium">{{ location.name }}</span>
                                        <span v-if="index === 0">🥇</span>
                                        <span v-else-if="index === 1">🥈</span>
                                        <span v-else-if="index === 2">🥉</span>
                                    </div>
                                    <div class="text-sm text-muted-foreground">{{ location.total }} catches</div>
                                </div>
                                <div v-if="location.city || location.state" class="text-right">
                                    <div v-if="location.city" class="text-sm font-medium text-lime-700 dark:text-lime-300">{{ location.city }}</div>
                                    <div v-if="location.state" class="text-xs text-muted-foreground">{{ location.state }}</div>
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-muted-foreground">No location data available</p>
                    </CardContent>
                </Card>

                <!-- Top Locations by Size -->
                <Card class="bg-gradient-to-br from-emerald-50/30 to-transparent dark:from-emerald-950/10">
                    <CardHeader class="pb-2">
                        <CardTitle class="flex items-center gap-2">
                            <div class="rounded-full bg-emerald-100 p-1.5 dark:bg-emerald-900/30">
                                <Award class="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                            </div>
                            Top Locations by Size
                        </CardTitle>
                        <CardDescription>Biggest fish per location</CardDescription>
                    </CardHeader>
                    <CardContent class="pt-0 pb-4">
                        <div v-if="topLocationsBySize.length > 0" class="space-y-2">
                            <div v-for="(location, index) in topLocationsBySize" :key="location.name" class="flex items-center gap-3 pb-2 border-b last:border-0">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 text-sm font-bold flex-shrink-0">
                                    {{ index + 1 }}
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="font-medium">{{ location.name }}</span>
                                        <span v-if="index === 0">🥇</span>
                                        <span v-else-if="index === 1">🥈</span>
                                        <span v-else-if="index === 2">🥉</span>
                                    </div>
                                    <div class="text-sm text-muted-foreground flex items-center gap-2">
                                        <span>Largest catch:</span>
                                        <span class="font-medium text-emerald-700 dark:text-emerald-300">{{ formatSize(location.biggest_size) }}"</span>
                                    </div>
                                </div>
                                <div v-if="location.city || location.state" class="text-right">
                                    <div v-if="location.city" class="text-sm font-medium text-emerald-700 dark:text-emerald-300">{{ location.city }}</div>
                                    <div v-if="location.state" class="text-xs text-muted-foreground">{{ location.state }}</div>
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-muted-foreground">No location data available</p>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
