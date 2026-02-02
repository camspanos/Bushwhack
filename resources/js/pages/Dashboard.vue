<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import PremiumFeatureDialog from '@/components/PremiumFeatureDialog.vue';
import DashboardCard from '@/components/dashboard/DashboardCard.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { NativeSelect, NativeSelectOption } from '@/components/ui/native-select';
import { dashboard, fishingLog } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Fish, MapPin, Users, TrendingUp, Award, Target, BarChart3, Calendar, X, Flame, Crown, Moon, Sun, Settings, Check, RotateCcw } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

interface CardPreference {
    card_id: string;
    order: number;
    is_visible: boolean;
    size: number;
}

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
    country: string | null;
    total: number;
}

interface LocationSizeData {
    name: string;
    city: string | null;
    state: string | null;
    country: string | null;
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

interface SunPhaseData {
    time_of_day: string;
    total_caught: number;
}

const props = defineProps<{
    stats: Stats;
    allSpecies: SpeciesData[];
    catchesByMonth: ChartData[];
    catchesByMonthPie: MonthData[];
    catchesByMoonPhase: MoonPhaseData[];
    catchesBySunPhase: SunPhaseData[];
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
    dashboardPreferences: CardPreference[];
    cardDisplayNames: Record<string, string>;
}>();

// Edit mode state
const isEditMode = ref(false);
const isSaving = ref(false);
const cardPreferences = ref<CardPreference[]>([...props.dashboardPreferences]);

// Get visible cards sorted by order (for determining first/last)
const visibleCardsSorted = computed(() => {
    return [...cardPreferences.value]
        .filter(c => c.is_visible)
        .sort((a, b) => a.order - b.order);
});

// Check if a card is visible (in normal mode, hidden cards are not shown; in edit mode, all cards are shown)
const isCardVisible = (cardId: string) => {
    if (isEditMode.value) return true; // Show all cards in edit mode
    const pref = cardPreferences.value.find(c => c.card_id === cardId);
    return pref?.is_visible ?? true;
};

// Check if a card is hidden (for styling in edit mode)
const isCardHidden = (cardId: string) => {
    const pref = cardPreferences.value.find(c => c.card_id === cardId);
    return !(pref?.is_visible ?? true);
};

// Get the CSS order for a card (for grid ordering)
// Hidden cards get a high order to appear at the bottom in edit mode
const getCardOrder = (cardId: string) => {
    const pref = cardPreferences.value.find(c => c.card_id === cardId);
    if (!pref) return 999;
    // In edit mode, hidden cards go to the bottom
    if (isEditMode.value && !pref.is_visible) {
        return 1000 + pref.order;
    }
    return pref.order;
};

// Get the size for a card (using 12-column grid: 3=1/4, 4=1/3, 6=1/2, 8=2/3, 9=3/4, 12=Full)
const getCardSize = (cardId: string) => {
    const pref = cardPreferences.value.find(c => c.card_id === cardId);
    return pref?.size ?? 3; // Default to 1/4 (3 columns)
};

// Check if a card is first among visible cards
const isCardFirst = (cardId: string) => {
    const visible = visibleCardsSorted.value;
    return visible.length > 0 && visible[0].card_id === cardId;
};

// Check if a card is last among visible cards
const isCardLast = (cardId: string) => {
    const visible = visibleCardsSorted.value;
    return visible.length > 0 && visible[visible.length - 1].card_id === cardId;
};

// Get the 1-based display position of a card among visible cards
const getCardDisplayPosition = (cardId: string) => {
    const visible = visibleCardsSorted.value;
    const index = visible.findIndex(c => c.card_id === cardId);
    return index >= 0 ? index + 1 : 0;
};

// Get total number of visible cards
const getTotalVisibleCards = computed(() => visibleCardsSorted.value.length);

// Toggle edit mode
const toggleEditMode = async () => {
    if (isEditMode.value) {
        // Exiting edit mode - save preferences and reload
        await savePreferences();
        window.location.reload();
        return;
    }
    // Check if user is premium before allowing customization
    if (!page.props.auth.isPremium) {
        showCustomizePremiumDialog.value = true;
        return;
    }
    isEditMode.value = true;
};

// Hide a card (called from DashboardCard component)
const hideCard = (cardId: string) => {
    const pref = cardPreferences.value.find(c => c.card_id === cardId);
    if (pref) {
        pref.is_visible = false;
    }
};

// Show a card (called from DashboardCard component)
const showCard = (cardId: string) => {
    const pref = cardPreferences.value.find(c => c.card_id === cardId);
    if (pref) {
        pref.is_visible = true;
    }
};

// Resize a card (called from DashboardCard component)
const resizeCard = (cardId: string, size: number) => {
    const pref = cardPreferences.value.find(c => c.card_id === cardId);
    if (pref) {
        pref.size = size;
    }
};

// Move a card up in order (swap with previous visible card)
const moveCardUp = (cardId: string) => {
    const visible = visibleCardsSorted.value;
    const currentIndex = visible.findIndex(c => c.card_id === cardId);
    if (currentIndex <= 0) return; // Already first or not found

    const currentCard = cardPreferences.value.find(c => c.card_id === cardId);
    const prevCard = cardPreferences.value.find(c => c.card_id === visible[currentIndex - 1].card_id);

    if (currentCard && prevCard) {
        // Swap orders
        const tempOrder = currentCard.order;
        currentCard.order = prevCard.order;
        prevCard.order = tempOrder;
    }
};

// Move a card down in order (swap with next visible card)
const moveCardDown = (cardId: string) => {
    const visible = visibleCardsSorted.value;
    const currentIndex = visible.findIndex(c => c.card_id === cardId);
    if (currentIndex < 0 || currentIndex >= visible.length - 1) return; // Already last or not found

    const currentCard = cardPreferences.value.find(c => c.card_id === cardId);
    const nextCard = cardPreferences.value.find(c => c.card_id === visible[currentIndex + 1].card_id);

    if (currentCard && nextCard) {
        // Swap orders
        const tempOrder = currentCard.order;
        currentCard.order = nextCard.order;
        nextCard.order = tempOrder;
    }
};

// Jump a card to a specific position (1-based)
const jumpCardToPosition = (cardId: string, newPosition: number) => {
    const visible = visibleCardsSorted.value;
    const currentIndex = visible.findIndex(c => c.card_id === cardId);
    if (currentIndex < 0) return;

    // Convert to 0-based index
    const targetIndex = newPosition - 1;
    if (targetIndex === currentIndex) return; // No change needed

    // Clamp target index to valid range
    const clampedTargetIndex = Math.max(0, Math.min(visible.length - 1, targetIndex));
    if (clampedTargetIndex === currentIndex) return;

    // Capture all order values before making any changes
    const orderValues = visible.map(c => c.order);

    // Get the moving card's original order value
    const movingCardOrder = orderValues[currentIndex];

    if (clampedTargetIndex < currentIndex) {
        // Moving up: shift cards between target and current-1 down by one position
        // Each card takes the order of the card before it
        for (let i = currentIndex - 1; i >= clampedTargetIndex; i--) {
            const card = cardPreferences.value.find(c => c.card_id === visible[i].card_id);
            if (card) {
                // This card moves down one position, so it gets the order of the next card
                card.order = orderValues[i + 1];
            }
        }
        // Set the moving card to the target's original order
        const movingCard = cardPreferences.value.find(c => c.card_id === cardId);
        if (movingCard) {
            movingCard.order = orderValues[clampedTargetIndex];
        }
    } else {
        // Moving down: shift cards between current+1 and target up by one position
        // Each card takes the order of the card after it
        for (let i = currentIndex + 1; i <= clampedTargetIndex; i++) {
            const card = cardPreferences.value.find(c => c.card_id === visible[i].card_id);
            if (card) {
                // This card moves up one position, so it gets the order of the previous card
                card.order = orderValues[i - 1];
            }
        }
        // Set the moving card to the target's original order
        const movingCard = cardPreferences.value.find(c => c.card_id === cardId);
        if (movingCard) {
            movingCard.order = orderValues[clampedTargetIndex];
        }
    }
};

// Save preferences to backend
const savePreferences = async () => {
    isSaving.value = true;
    try {
        await fetch('/api/dashboard-preferences', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({ preferences: cardPreferences.value }),
        });
    } catch (error) {
        console.error('Failed to save preferences:', error);
    } finally {
        isSaving.value = false;
    }
};

// Reset preferences to defaults
const resetPreferences = async () => {
    isSaving.value = true;
    try {
        const response = await fetch('/api/dashboard-preferences/reset', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        });
        const data = await response.json();
        cardPreferences.value = data.preferences;
        isEditMode.value = false;
        // Reload the page to get fresh data from the server
        window.location.reload();
    } catch (error) {
        console.error('Failed to reset preferences:', error);
    } finally {
        isSaving.value = false;
    }
};

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
const showCustomizePremiumDialog = ref(false);
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

// Sun phase pie chart colors
const sunPhaseColors: Record<string, string> = {
    'Pre-dawn': '#7c3aed', // violet
    'Morning': '#f59e0b', // amber
    'Midday': '#eab308', // yellow
    'Afternoon': '#f97316', // orange
    'Evening': '#ec4899', // pink
    'Night': '#1e293b', // slate-800
};

const getSunPhaseColor = (phase: string) => {
    return sunPhaseColors[phase] || '#64748b';
};

// Sun phase pie chart slices
const sunPhasePieSlices = computed(() => {
    const total = props.catchesBySunPhase.reduce((sum, phase) => sum + Number(phase.total_caught), 0);
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
            phase: phase.time_of_day,
            total: caught,
        });

        currentAngle += angle;
    });

    return slices;
});

// Hover state for pie charts
const hoveredMonthSlice = ref<number | null>(null);
const hoveredMoonSlice = ref<number | null>(null);
const hoveredSunSlice = ref<number | null>(null);
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Year Filter & Edit Mode Toggle -->
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-3xl font-bold tracking-tight">Dashboard</h2>
                    <p class="text-muted-foreground">Your fishing statistics {{ yearLabel === 'Lifetime' ? 'across all time' : 'for ' + yearLabel }}</p>
                </div>
                <div class="flex items-center gap-4">
                    <!-- Edit Mode Controls -->
                    <div class="flex items-center gap-2">
                        <Button
                            v-if="isEditMode"
                            variant="outline"
                            size="sm"
                            @click="resetPreferences"
                            :disabled="isSaving"
                        >
                            <RotateCcw class="h-4 w-4 mr-1" />
                            Reset
                        </Button>
                        <Button
                            :variant="isEditMode ? 'default' : 'outline'"
                            size="sm"
                            @click="toggleEditMode"
                            :disabled="isSaving"
                        >
                            <template v-if="isEditMode">
                                <Check class="h-4 w-4 mr-1" />
                                Done
                            </template>
                            <template v-else>
                                <Settings class="h-4 w-4 mr-1" />
                                Customize
                            </template>
                        </Button>
                    </div>
                    <!-- Year Filter -->
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
            </div>

            <!-- Premium Feature Dialog for Year Filtering -->
            <PremiumFeatureDialog
                v-model:open="showPremiumDialog"
                title="Year Filtering is a Premium Feature"
                description="Access to historical data and year filtering is only available to premium users. Upgrade to premium to view your fishing statistics from previous years and lifetime totals."
            />

            <!-- Premium Feature Dialog for Dashboard Customization -->
            <PremiumFeatureDialog
                v-model:open="showCustomizePremiumDialog"
                title="Dashboard Customization is a Premium Feature"
                description="Customize your dashboard layout, hide cards you don't need, and resize widgets to create your perfect fishing dashboard. Upgrade to premium to unlock full customization."
            />

            <!-- All Dashboard Cards in a single unified 12-column grid -->
            <div class="grid gap-4 grid-cols-1 md:grid-cols-6 lg:grid-cols-12">
                <!-- Stats: Total Catches -->
                <DashboardCard
                    v-if="isCardVisible('stats_total_catches')"
                    card-id="stats_total_catches"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('stats_total_catches')"
                    :order="getCardOrder('stats_total_catches')"
                    :size="getCardSize('stats_total_catches')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('stats_total_catches')"
                    :is-last="isCardLast('stats_total_catches')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('stats_total_catches')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-blue-50/50 to-transparent dark:from-blue-950/20"
                >
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
                </DashboardCard>

                <!-- Stats: Favorite Location -->
                <DashboardCard
                    v-if="isCardVisible('stats_favorite_location')"
                    card-id="stats_favorite_location"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('stats_favorite_location')"
                    :order="getCardOrder('stats_favorite_location')"
                    :size="getCardSize('stats_favorite_location')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('stats_favorite_location')"
                    :is-last="isCardLast('stats_favorite_location')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('stats_favorite_location')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-emerald-50/50 to-transparent dark:from-emerald-950/20"
                >
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
                </DashboardCard>

                <!-- Stats: Top Species -->
                <DashboardCard
                    v-if="isCardVisible('stats_top_species')"
                    card-id="stats_top_species"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('stats_top_species')"
                    :order="getCardOrder('stats_top_species')"
                    :size="getCardSize('stats_top_species')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('stats_top_species')"
                    :is-last="isCardLast('stats_top_species')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('stats_top_species')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-amber-50/50 to-transparent dark:from-amber-950/20"
                >
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
                </DashboardCard>

                <!-- Stats: Fishing Buddies -->
                <DashboardCard
                    v-if="isCardVisible('stats_fishing_buddies')"
                    card-id="stats_fishing_buddies"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('stats_fishing_buddies')"
                    :order="getCardOrder('stats_fishing_buddies')"
                    :size="getCardSize('stats_fishing_buddies')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('stats_fishing_buddies')"
                    :is-last="isCardLast('stats_fishing_buddies')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('stats_fishing_buddies')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-purple-50/50 to-transparent dark:from-purple-950/20"
                >
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
                </DashboardCard>
                <!-- Biggest Catch -->
                <DashboardCard
                    v-if="isCardVisible('biggest_catch') && stats.biggestCatch"
                    card-id="biggest_catch"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('biggest_catch')"
                    :order="getCardOrder('biggest_catch')"
                    :size="getCardSize('biggest_catch')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('biggest_catch')"
                    :is-last="isCardLast('biggest_catch')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('biggest_catch')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-yellow-50/30 to-transparent dark:from-yellow-950/10"
                >
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
                </DashboardCard>

                <DashboardCard
                    v-else-if="isCardVisible('biggest_catch')"
                    card-id="biggest_catch"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('biggest_catch')"
                    :order="getCardOrder('biggest_catch')"
                    :size="getCardSize('biggest_catch')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('biggest_catch')"
                    :is-last="isCardLast('biggest_catch')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('biggest_catch')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-gray-50/30 to-transparent dark:from-gray-950/10"
                >
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
                </DashboardCard>

                <!-- Runner Up / Second Biggest Catch -->
                <DashboardCard
                    v-if="isCardVisible('runner_up') && stats.secondBiggestCatch"
                    card-id="runner_up"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('runner_up')"
                    :order="getCardOrder('runner_up')"
                    :size="getCardSize('runner_up')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('runner_up')"
                    :is-last="isCardLast('runner_up')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('runner_up')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-orange-50/30 to-transparent dark:from-orange-950/10"
                >
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
                </DashboardCard>

                <DashboardCard
                    v-else-if="isCardVisible('runner_up')"
                    card-id="runner_up"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('runner_up')"
                    :order="getCardOrder('runner_up')"
                    :size="getCardSize('runner_up')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('runner_up')"
                    :is-last="isCardLast('runner_up')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('runner_up')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-gray-50/30 to-transparent dark:from-gray-950/10"
                >
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
                </DashboardCard>

                <!-- Species Distribution Pie Chart -->
                <DashboardCard
                    v-if="isCardVisible('species_pie_chart')"
                    card-id="species_pie_chart"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('species_pie_chart')"
                    :order="getCardOrder('species_pie_chart')"
                    :size="getCardSize('species_pie_chart')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('species_pie_chart')"
                    :is-last="isCardLast('species_pie_chart')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('species_pie_chart')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-pink-50/30 to-transparent dark:from-pink-950/10"
                >
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
                </DashboardCard>

                <!-- Favorite Weekday -->
                <DashboardCard
                    v-if="isCardVisible('favorite_weekday')"
                    card-id="favorite_weekday"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('favorite_weekday')"
                    :order="getCardOrder('favorite_weekday')"
                    :size="getCardSize('favorite_weekday')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('favorite_weekday')"
                    :is-last="isCardLast('favorite_weekday')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('favorite_weekday')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-cyan-50/50 to-transparent dark:from-cyan-950/20"
                >
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
                </DashboardCard>

                <!-- Streak Tracker -->
                <DashboardCard
                    v-if="isCardVisible('longest_streak')"
                    card-id="longest_streak"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('longest_streak')"
                    :order="getCardOrder('longest_streak')"
                    :size="getCardSize('longest_streak')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('longest_streak')"
                    :is-last="isCardLast('longest_streak')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('longest_streak')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-orange-50/50 to-transparent dark:from-orange-950/20"
                >
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
                </DashboardCard>

                <!-- Average per Trip -->
                <DashboardCard
                    v-if="isCardVisible('average_per_trip')"
                    card-id="average_per_trip"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('average_per_trip')"
                    :order="getCardOrder('average_per_trip')"
                    :size="getCardSize('average_per_trip')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('average_per_trip')"
                    :is-last="isCardLast('average_per_trip')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('average_per_trip')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-indigo-50/50 to-transparent dark:from-indigo-950/20"
                >
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
                </DashboardCard>

                <!-- Days Fished -->
                <DashboardCard
                    v-if="isCardVisible('days_fished')"
                    card-id="days_fished"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('days_fished')"
                    :order="getCardOrder('days_fished')"
                    :size="getCardSize('days_fished')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('days_fished')"
                    :is-last="isCardLast('days_fished')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('days_fished')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-sky-50/50 to-transparent dark:from-sky-950/20"
                >
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
                </DashboardCard>

                <!-- Successful Days -->
                <DashboardCard
                    v-if="isCardVisible('successful_days')"
                    card-id="successful_days"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('successful_days')"
                    :order="getCardOrder('successful_days')"
                    :size="getCardSize('successful_days')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('successful_days')"
                    :is-last="isCardLast('successful_days')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('successful_days')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-green-50/50 to-transparent dark:from-green-950/20"
                >
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
                </DashboardCard>

                <!-- Days Skunked -->
                <DashboardCard
                    v-if="isCardVisible('days_skunked')"
                    card-id="days_skunked"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('days_skunked')"
                    :order="getCardOrder('days_skunked')"
                    :size="getCardSize('days_skunked')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('days_skunked')"
                    :is-last="isCardLast('days_skunked')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('days_skunked')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-red-50/50 to-transparent dark:from-red-950/20"
                >
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
                </DashboardCard>

                <!-- Most in a Day -->
                <DashboardCard
                    v-if="isCardVisible('most_in_day')"
                    card-id="most_in_day"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('most_in_day')"
                    :order="getCardOrder('most_in_day')"
                    :size="getCardSize('most_in_day')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('most_in_day')"
                    :is-last="isCardLast('most_in_day')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('most_in_day')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-violet-50/50 to-transparent dark:from-violet-950/20"
                >
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
                </DashboardCard>

                <!-- Fish Caught per Month -->
                <DashboardCard
                    v-if="isCardVisible('catches_by_month')"
                    card-id="catches_by_month"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('catches_by_month')"
                    :order="getCardOrder('catches_by_month')"
                    :size="getCardSize('catches_by_month')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('catches_by_month')"
                    :is-last="isCardLast('catches_by_month')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('catches_by_month')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-blue-50/30 to-transparent dark:from-blue-950/10"
                >
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
                </DashboardCard>

                <!-- Fish Caught by Moon Phase -->
                <DashboardCard
                    v-if="isCardVisible('catches_by_moon_phase')"
                    card-id="catches_by_moon_phase"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('catches_by_moon_phase')"
                    :order="getCardOrder('catches_by_moon_phase')"
                    :size="getCardSize('catches_by_moon_phase')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('catches_by_moon_phase')"
                    :is-last="isCardLast('catches_by_moon_phase')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('catches_by_moon_phase')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-slate-50/30 to-transparent dark:from-slate-950/10"
                >
                    <CardHeader class="pb-1">
                        <CardTitle class="flex items-center gap-2 text-base">
                            <div class="rounded-full bg-slate-100 p-1.5 dark:bg-slate-900/30">
                                <Moon class="h-5 w-5 text-slate-600 dark:text-slate-400" />
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
                </DashboardCard>

                <!-- Fish Caught by Sun Phase -->
                <DashboardCard
                    v-if="isCardVisible('catches_by_sun_phase')"
                    card-id="catches_by_sun_phase"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('catches_by_sun_phase')"
                    :order="getCardOrder('catches_by_sun_phase')"
                    :size="getCardSize('catches_by_sun_phase')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('catches_by_sun_phase')"
                    :is-last="isCardLast('catches_by_sun_phase')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('catches_by_sun_phase')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-amber-50/30 to-transparent dark:from-amber-950/10"
                >
                    <CardHeader class="pb-1">
                        <CardTitle class="flex items-center gap-2 text-base">
                            <div class="rounded-full bg-amber-100 p-1.5 dark:bg-amber-900/30">
                                <Sun class="h-5 w-5 text-amber-600 dark:text-amber-400" />
                            </div>
                            Fish Caught by Sun Phase
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="pt-0 pb-3">
                        <div v-if="catchesBySunPhase.length > 0" class="flex items-center gap-4">
                            <!-- SVG Pie Chart -->
                            <div class="relative w-44 h-44 flex-shrink-0">
                                <svg class="w-full h-full" viewBox="0 0 200 200">
                                    <!-- Pie slices -->
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
                                        {{ catchesBySunPhase.length }}
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
                                    v-for="(phase, index) in catchesBySunPhase"
                                    :key="phase.time_of_day"
                                    class="flex items-center justify-between gap-2 p-1.5 rounded hover:bg-muted/50 cursor-pointer transition-colors"
                                    :class="hoveredSunSlice === index ? 'bg-muted' : ''"
                                    @mouseenter="hoveredSunSlice = index"
                                    @mouseleave="hoveredSunSlice = null"
                                >
                                    <div class="flex items-center gap-2 min-w-0">
                                        <div
                                            class="w-2.5 h-2.5 rounded-full flex-shrink-0"
                                            :style="{ backgroundColor: getSunPhaseColor(phase.time_of_day) }"
                                        ></div>
                                        <span class="text-xs font-medium truncate">{{ phase.time_of_day }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 flex-shrink-0">
                                        <span class="text-xs font-bold">{{ phase.total_caught }}</span>
                                        <span class="text-xs text-muted-foreground">
                                            ({{ sunPhasePieSlices[index]?.percentage }}%)
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-4">
                            <p class="text-muted-foreground text-sm mb-2">No sun phase data yet</p>
                            <Link :href="fishingLog()" class="text-sm text-primary hover:underline">
                                Log catches with times →
                            </Link>
                        </div>
                    </CardContent>
                </DashboardCard>

                <!-- Most Successful Fly (by quantity) -->
                <DashboardCard
                    v-if="isCardVisible('most_successful_fly')"
                    card-id="most_successful_fly"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('most_successful_fly')"
                    :order="getCardOrder('most_successful_fly')"
                    :size="getCardSize('most_successful_fly')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('most_successful_fly')"
                    :is-last="isCardLast('most_successful_fly')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('most_successful_fly')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-rose-50/30 to-transparent dark:from-rose-950/10"
                >
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
                </DashboardCard>

                <!-- Biggest Fish Fly -->
                <DashboardCard
                    v-if="isCardVisible('biggest_fish_fly')"
                    card-id="biggest_fish_fly"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('biggest_fish_fly')"
                    :order="getCardOrder('biggest_fish_fly')"
                    :size="getCardSize('biggest_fish_fly')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('biggest_fish_fly')"
                    :is-last="isCardLast('biggest_fish_fly')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('biggest_fish_fly')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-teal-50/30 to-transparent dark:from-teal-950/10"
                >
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
                </DashboardCard>

                <!-- Most Successful Fly Type -->
                <DashboardCard
                    v-if="isCardVisible('most_successful_fly_type')"
                    card-id="most_successful_fly_type"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('most_successful_fly_type')"
                    :order="getCardOrder('most_successful_fly_type')"
                    :size="getCardSize('most_successful_fly_type')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('most_successful_fly_type')"
                    :is-last="isCardLast('most_successful_fly_type')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('most_successful_fly_type')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-purple-50/30 to-transparent dark:from-purple-950/10"
                >
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
                </DashboardCard>

                <!-- Most Successful Fly Color -->
                <DashboardCard
                    v-if="isCardVisible('most_successful_fly_color')"
                    card-id="most_successful_fly_color"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('most_successful_fly_color')"
                    :order="getCardOrder('most_successful_fly_color')"
                    :size="getCardSize('most_successful_fly_color')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('most_successful_fly_color')"
                    :is-last="isCardLast('most_successful_fly_color')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('most_successful_fly_color')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-indigo-50/30 to-transparent dark:from-indigo-950/10"
                >
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
                </DashboardCard>

                <!-- Top Species Caught by Count -->
                <DashboardCard
                    v-if="isCardVisible('top_species_by_count')"
                    card-id="top_species_by_count"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('top_species_by_count')"
                    :order="getCardOrder('top_species_by_count')"
                    :size="getCardSize('top_species_by_count')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('top_species_by_count')"
                    :is-last="isCardLast('top_species_by_count')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('top_species_by_count')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-yellow-50/30 to-transparent dark:from-yellow-950/10"
                >
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
                </DashboardCard>

                <!-- Top Species by Size -->
                <DashboardCard
                    v-if="isCardVisible('top_species_by_size')"
                    card-id="top_species_by_size"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('top_species_by_size')"
                    :order="getCardOrder('top_species_by_size')"
                    :size="getCardSize('top_species_by_size')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('top_species_by_size')"
                    :is-last="isCardLast('top_species_by_size')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('top_species_by_size')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-orange-50/30 to-transparent dark:from-orange-950/10"
                >
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
                </DashboardCard>

                <!-- Top Locations by Count -->
                <DashboardCard
                    v-if="isCardVisible('top_locations_by_count')"
                    card-id="top_locations_by_count"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('top_locations_by_count')"
                    :order="getCardOrder('top_locations_by_count')"
                    :size="getCardSize('top_locations_by_count')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('top_locations_by_count')"
                    :is-last="isCardLast('top_locations_by_count')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('top_locations_by_count')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-lime-50/30 to-transparent dark:from-lime-950/10"
                >
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
                                <div v-if="location.city || location.state || location.country" class="text-right">
                                    <div v-if="location.city" class="text-sm font-medium text-lime-700 dark:text-lime-300">{{ location.city }}</div>
                                    <div v-if="location.state || location.country" class="text-xs text-muted-foreground">{{ location.state || location.country }}</div>
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-muted-foreground">No location data available</p>
                    </CardContent>
                </DashboardCard>

                <!-- Top Locations by Size -->
                <DashboardCard
                    v-if="isCardVisible('top_locations_by_size')"
                    card-id="top_locations_by_size"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('top_locations_by_size')"
                    :order="getCardOrder('top_locations_by_size')"
                    :size="getCardSize('top_locations_by_size')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('top_locations_by_size')"
                    :is-last="isCardLast('top_locations_by_size')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('top_locations_by_size')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-emerald-50/30 to-transparent dark:from-emerald-950/10"
                >
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
                                <div v-if="location.city || location.state || location.country" class="text-right">
                                    <div v-if="location.city" class="text-sm font-medium text-emerald-700 dark:text-emerald-300">{{ location.city }}</div>
                                    <div v-if="location.state || location.country" class="text-xs text-muted-foreground">{{ location.state || location.country }}</div>
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-muted-foreground">No location data available</p>
                    </CardContent>
                </DashboardCard>
            </div>
            <!-- End of unified grid -->

            <!-- AdSense & Premium Upgrade Row (not part of customizable cards) -->
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
        </div>
    </AppLayout>
</template>
