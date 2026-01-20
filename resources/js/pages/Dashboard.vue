<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { NativeSelect, NativeSelectOption } from '@/components/ui/native-select';
import { dashboard, fishingLog } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Fish, MapPin, Users, TrendingUp, Award, Target, BarChart3, Calendar, X, Flame } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

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
    total: number;
}

interface TopPerformer {
    name: string;
    total: number;
    days?: number;
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

const props = defineProps<{
    stats: Stats;
    allSpecies: SpeciesData[];
    catchesByMonth: ChartData[];
    topLocations: LocationData[];
    mostProductiveLocation: TopPerformer | null;
    mostSuccessfulFly: TopPerformer | null;
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

// Watch for year filter changes and reload data
watch(selectedYearFilter, (newYear) => {
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

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-1">
                        <CardTitle class="text-sm font-medium">Total Catches</CardTitle>
                        <Fish class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent class="pb-3">
                        <div class="text-2xl font-bold">{{ stats.totalCatches }}</div>
                        <p class="text-xs text-muted-foreground">Across {{ stats.totalTrips }} trips</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-1">
                        <CardTitle class="text-sm font-medium">Favorite Location</CardTitle>
                        <MapPin class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent class="pb-3">
                        <div class="text-2xl font-bold">{{ stats.favoriteLocation || 'N/A' }}</div>
                        <p class="text-xs text-muted-foreground">Most visited spot</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-1">
                        <CardTitle class="text-sm font-medium">Top Species</CardTitle>
                        <TrendingUp class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent class="pb-3">
                        <div class="text-2xl font-bold">{{ stats.topFish || 'N/A' }}</div>
                        <p class="text-xs text-muted-foreground">{{ stats.topFishCount }} caught</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-1">
                        <CardTitle class="text-sm font-medium">Fishing Buddies</CardTitle>
                        <Users class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent class="pb-3">
                        <div class="text-2xl font-bold">{{ stats.totalFriends }}</div>
                        <p class="text-xs text-muted-foreground">Friends in your network</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Biggest Catch & Charts Row -->
            <div class="grid gap-4 md:grid-cols-2">
                <!-- Biggest Catch -->
                <Card v-if="stats.biggestCatch">
                    <CardHeader class="pb-1">
                        <CardTitle class="flex items-center gap-2 text-base">
                            <Award class="h-5 w-5" />
                            Biggest Catch
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="pt-0 pb-3">
                        <div class="space-y-1">
                            <div class="text-3xl font-bold">{{ formatSize(stats.biggestCatch.size) }}"</div>
                            <div class="text-lg font-medium">{{ stats.biggestCatch.species }}</div>
                            <div class="text-sm text-muted-foreground">
                                <div>{{ stats.biggestCatch.location }}</div>
                                <div>{{ formatDate(stats.biggestCatch.date) }}</div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card v-else>
                    <CardHeader class="pb-1">
                        <CardTitle class="flex items-center gap-2 text-base">
                            <Award class="h-5 w-5" />
                            Biggest Catch
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="pt-0 pb-3">
                        <p class="text-muted-foreground">No catches recorded yet</p>
                    </CardContent>
                </Card>


                <!-- Species Distribution Pie Chart -->
                <Card>
                    <CardHeader class="pb-1">
                        <CardTitle class="flex items-center gap-2 text-base">
                            <Fish class="h-5 w-5" />
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

            <!-- Year Stats Grid -->
            <div class="grid gap-4 md:grid-cols-3">
                <!-- Favorite Weekday -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-1">
                        <CardTitle class="text-sm font-medium">Favorite Weekday</CardTitle>
                        <Calendar class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent class="pb-3">
                        <div v-if="favoriteWeekday" class="text-2xl font-bold">{{ favoriteWeekday.day }}</div>
                        <div v-else class="text-2xl font-bold text-muted-foreground">-</div>
                        <p class="text-xs text-muted-foreground">
                            {{ favoriteWeekday ? `${favoriteWeekday.count} trips` : 'No data yet' }}
                        </p>
                    </CardContent>
                </Card>

                <!-- Streak Tracker -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-1">
                        <CardTitle class="text-sm font-medium">Longest Streak</CardTitle>
                        <Flame class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent class="pb-3">
                        <div class="text-2xl font-bold">{{ streakStats.longestStreak }}</div>
                        <p class="text-xs text-muted-foreground">
                            Current streak {{ streakStats.currentStreak }}
                        </p>
                    </CardContent>
                </Card>

                <!-- Average per Trip -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-1">
                        <CardTitle class="text-sm font-medium">Average per Trip</CardTitle>
                        <BarChart3 class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent class="pb-3">
                        <div class="text-2xl font-bold">
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
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-1">
                        <CardTitle class="text-sm font-medium">Days Fished</CardTitle>
                        <Calendar class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent class="pb-3">
                        <div class="text-2xl font-bold">{{ yearStats.daysFished }}</div>
                        <p class="text-xs text-muted-foreground">
                            Total days on the water
                        </p>
                    </CardContent>
                </Card>

                <!-- Successful Days -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-1">
                        <CardTitle class="text-sm font-medium">Successful Days</CardTitle>
                        <Fish class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent class="pb-3">
                        <div class="text-2xl font-bold">{{ yearStats.daysWithFish }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ yearStats.successRate }}% success rate
                        </p>
                    </CardContent>
                </Card>

                <!-- Days Skunked -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-1">
                        <CardTitle class="text-sm font-medium">Days Skunked</CardTitle>
                        <X class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent class="pb-3">
                        <div class="text-2xl font-bold">{{ yearStats.daysSkunked }}</div>
                        <p class="text-xs text-muted-foreground">
                            Where the fish at?
                        </p>
                    </CardContent>
                </Card>

                <!-- Most in a Day -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-1">
                        <CardTitle class="text-sm font-medium">Most in a Day</CardTitle>
                        <Award class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent class="pb-3">
                        <div class="text-2xl font-bold">{{ yearStats.mostInDay }}</div>
                        <p class="text-xs text-muted-foreground">
                            Personal best day
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Top Performers -->
            <div class="grid gap-4 md:grid-cols-2">
                <!-- Most Productive Location -->
                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="flex items-center gap-2">
                            <MapPin class="h-5 w-5" />
                            Most Productive Location
                        </CardTitle>
                        <CardDescription>Best fishing spot</CardDescription>
                    </CardHeader>
                    <CardContent class="pt-0 pb-4">
                        <div v-if="mostProductiveLocation" class="space-y-2">
                            <div class="text-xl font-bold">{{ mostProductiveLocation.name }}</div>
                            <p class="text-sm text-muted-foreground">
                                {{ mostProductiveLocation.total }} fish caught
                            </p>
                        </div>
                        <div v-else class="text-muted-foreground">
                            No data yet
                        </div>
                    </CardContent>
                </Card>

                <!-- Most Successful Fly -->
                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="flex items-center gap-2">
                            <Award class="h-5 w-5" />
                            Most Successful Fly
                        </CardTitle>
                        <CardDescription>Top performer</CardDescription>
                    </CardHeader>
                    <CardContent class="pt-0 pb-4">
                        <div v-if="mostSuccessfulFly" class="space-y-2">
                            <div class="text-xl font-bold">{{ mostSuccessfulFly.name }}</div>
                            <p class="text-sm text-muted-foreground">
                                {{ mostSuccessfulFly.total }} fish caught • {{ mostSuccessfulFly.days }} days used
                            </p>
                        </div>
                        <div v-else class="text-muted-foreground">
                            No data yet
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- All Species & Top Locations -->
            <div class="grid gap-4 md:grid-cols-2">
                <!-- All Species Caught -->
                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="flex items-center gap-2">
                            <Fish class="h-5 w-5" />
                            All Species Caught
                        </CardTitle>
                        <CardDescription>Complete list of species you've caught</CardDescription>
                    </CardHeader>
                    <CardContent class="pt-0 pb-4">
                        <div v-if="allSpecies.length > 0" class="space-y-3">
                            <div v-for="species in allSpecies" :key="species.species" class="flex items-start gap-3 pb-3 border-b last:border-0">
                                <div class="flex-1 space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span class="font-medium">{{ species.species }}</span>
                                        <span v-if="species.water_type" class="text-xs px-2 py-0.5 rounded-full bg-primary/10 text-primary">
                                            {{ species.water_type }}
                                        </span>
                                    </div>
                                    <div class="text-sm text-muted-foreground">
                                        {{ species.total_caught }} caught across {{ species.trip_count }} trips
                                    </div>
                                </div>
                                <div v-if="species.biggest_size > 0" class="text-right">
                                    <div class="text-sm font-medium">{{ formatSize(species.biggest_size) }}"</div>
                                    <div class="text-xs text-muted-foreground">biggest</div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-8">
                            <p class="text-muted-foreground mb-4">No species caught yet</p>
                            <Link :href="fishingLog()" class="text-sm text-primary hover:underline">
                                Log your first catch →
                            </Link>
                        </div>
                    </CardContent>
                </Card>

                <!-- Top Locations -->
                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="flex items-center gap-2">
                            <MapPin class="h-5 w-5" />
                            Top Locations
                        </CardTitle>
                        <CardDescription>Your most productive spots</CardDescription>
                    </CardHeader>
                    <CardContent class="pt-0 pb-4">
                        <div v-if="topLocations.length > 0" class="space-y-3">
                            <div v-for="(location, index) in topLocations" :key="location.name" class="flex items-center gap-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10 text-sm font-medium">
                                    {{ index + 1 }}
                                </div>
                                <div class="flex-1">
                                    <div class="font-medium">{{ location.name }}</div>
                                    <div class="text-sm text-muted-foreground">{{ location.total }} catches</div>
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
