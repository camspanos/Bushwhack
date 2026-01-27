<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { NativeSelect, NativeSelectOption } from '@/components/ui/native-select';
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Fish, TrendingUp, Award, Target, BarChart3, Calendar, X, Flame } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

interface Stats {
    totalCatches: number;
    totalTrips: number;
    topFish: string | null;
    topFishCount: number;
    biggestCatch: {
        size: number;
        species: string;
        date: string;
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

const props = defineProps<{
    user: UserInfo;
    stats: Stats;
    allSpecies: SpeciesData[];
    catchesByMonth: ChartData[];
    mostSuccessfulFly: TopPerformer | null;
    biggestFishFly: BiggestFishFly | null;
    yearStats: YearStats;
    catchesOverTime: CatchOverTime[];
    streakStats: StreakStats;
    favoriteWeekday: FavoriteWeekday | null;
    availableYears: string[];
    selectedYear: string;
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

// Watch for year filter changes and reload data
watch(selectedYearFilter, (newYear) => {
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
        const angle = percentage * 360;

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

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-2">
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
            </div>

            <!-- Biggest Catch & Charts Row -->
            <div class="grid gap-4 md:grid-cols-2">
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
                        <div class="space-y-1">
                            <div class="text-3xl font-bold text-yellow-700 dark:text-yellow-300">{{ formatSize(stats.biggestCatch.size) }}"</div>
                            <div class="text-lg font-medium">{{ stats.biggestCatch.species }}</div>
                            <div class="text-sm text-muted-foreground">
                                <div>{{ stats.biggestCatch.location }}</div>
                                <div>{{ formatDate(stats.biggestCatch.date) }}</div>
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


                <!-- Species Distribution Pie Chart -->
                <Card class="bg-gradient-to-br from-pink-50/30 to-transparent dark:from-pink-950/10">
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
                            <p class="text-muted-foreground text-sm">No catches recorded yet</p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Year Stats Grid -->
            <div class="grid gap-4 md:grid-cols-2">
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
            <div class="grid gap-4 md:grid-cols-2">
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

            <!-- Top Performers -->
            <div class="grid gap-4 md:grid-cols-2">
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
            </div>

            <!-- Top Species -->
            <Card class="bg-gradient-to-br from-yellow-50/30 to-transparent dark:from-yellow-950/10">
                <CardHeader class="pb-2">
                    <CardTitle class="flex items-center gap-2">
                        <div class="rounded-full bg-yellow-100 p-1.5 dark:bg-yellow-900/30">
                            <Fish class="h-5 w-5 text-yellow-600 dark:text-yellow-400" />
                        </div>
                        Top Species Caught
                    </CardTitle>
                    <CardDescription>Most caught species</CardDescription>
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
        </div>
    </AppLayout>
</template>
