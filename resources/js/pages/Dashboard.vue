<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import PremiumFeatureDialog from '@/components/PremiumFeatureDialog.vue';
import DashboardCard from '@/components/dashboard/DashboardCard.vue';
import DashboardCardHeader from '@/components/dashboard/DashboardCardHeader.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { NativeSelect, NativeSelectOption } from '@/components/ui/native-select';
import { dashboard, fishingLog } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Fish, MapPin, Users, TrendingUp, Award, Target, BarChart3, Calendar, X, Flame, Crown, Moon, Sun, Settings, Check, RotateCcw, Ruler, Scale, Thermometer, Droplets, Gauge, CircleDot, Mountain, Globe, Map, Waves, CalendarDays, LineChart, Palette, Layers, BarChart2 } from 'lucide-vue-next';
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

// Weather stats interfaces
interface BestCondition {
    cloud?: string;
    wind?: string;
    pressure?: string;
    total: number;
}

interface PrecipitationData {
    precipitation: string;
    total_caught: number;
}

// Water condition interfaces
interface WaterLevelData {
    level: string;
    total_caught: number;
}

interface TideData {
    tide: string;
    total_caught: number;
}

// Moon position interfaces
interface MoonPositionData {
    position: string;
    total_caught: number;
}

interface MajorMinorFeeding {
    major: number;
    minor: number;
    other: number;
}

interface BestMoonForBigFish {
    position: string | null;
    position_biggest_size: number;
    position_avg_size: number;
    phase: string | null;
    phase_biggest_size: number;
    phase_avg_size: number;
}

// Weight interfaces
interface HeaviestCatch {
    weight: number;
    species: string | null;
    location: string | null;
    date: string;
}

interface AvgWeightBySpecies {
    species: string | null;
    avg_weight: number;
    count: number;
}

// Friend interfaces
interface MostProductiveBuddy {
    name: string;
    total: number;
    trips: number;
}

interface SoloVsGroup {
    solo: { catches: number; trips: number; avg: number };
    group: { catches: number; trips: number; avg: number };
}

interface LuckyCharmFriend {
    name: string;
    biggest_fish: number;
    avg_size: number;
}

// Rod and style interfaces
interface RodStats {
    name: string | null;
    total: number;
    days: number;
}

interface BestRodForTrophies {
    name: string | null;
    biggest_size: number;
    avg_size: number;
}

interface StyleData {
    style: string;
    total_caught: number;
}

// Golden conditions interfaces
interface GoldenConditions {
    // Moon & Time
    moon_position: string | null;
    moon_phase: string | null;
    time_of_day: string | null;
    season: string | null;
    // Weather
    cloud: string | null;
    wind: string | null;
    precipitation: string | null;
    barometric_pressure: string | null;
    air_temperature: string | null;
    // Water
    clarity: string | null;
    water_level: string | null;
    water_speed: string | null;
    surface_condition: string | null;
    tide: string | null;
    water_temperature: string | null;
}

interface BigFishConditions {
    // Moon & Time
    moon_position?: string;
    moon_phase?: string;
    time_of_day?: string;
    season?: string;
    // Weather
    cloud?: string;
    wind?: string;
    precipitation?: string;
    barometric_pressure?: string;
    air_temperature?: string;
    // Water
    clarity?: string;
    water_level?: string;
    water_speed?: string;
    surface_condition?: string;
    tide?: string;
    water_temperature?: string;
}

// Time pattern interfaces
interface BestHour {
    hour: number;
    formatted: string;
    total: number;
}

interface SeasonalTrend {
    total: number;
    days: number;
    avg: number;
}

// Location intelligence interfaces
interface MostConsistentSpot {
    name: string;
    success_rate: number;
    days: number;
}

interface BestLocationBySeason {
    name: string;
    total: number;
}

// Species deep dive interfaces
interface RarestCatch {
    name: string;
    count: number;
}

interface SizeImprovementItem {
    name: string;
    improvement: number;
    current_avg: number;
    previous_avg: number;
}

interface SizeImprovement {
    items: SizeImprovementItem[];
    currentYear: number;
    previousYear: number;
}

// Fly pattern interfaces
interface OneHitWonder {
    name: string;
    caught: number;
}

interface BestFlyByLocation {
    location: string;
    fly: string;
    total: number;
}

interface BestFlyBySpecies {
    species: string;
    fly: string;
    total: number;
}

// Progress interfaces
interface YoyComparison {
    thisYear: { catches: number; days: number; biggest: number | null; year: number };
    lastYearToDate: { catches: number; days: number; biggest: number | null; year: number; asOfDate: string };
    lastYearFull: { catches: number; days: number; biggest: number | null; year: number };
    catchChange: number | null;
    isFullYearComparison: boolean;
}

// Environmental combo interfaces
interface WindCloudCombo {
    wind: string;
    cloud: string;
    total: number;
}

interface MoonTimeCombo {
    moon: string;
    time: string;
    total: number;
}

interface WaterWeatherCombo {
    clarity: string;
    cloud: string;
    total: number;
}

// Gamification interfaces
interface Badge {
    name: string;
    icon: string;
    description: string;
}

interface LuckyNumber {
    number: number;
    occurrences: number;
}

// Temperature interfaces
interface TempData {
    temperature: string;
    total: number;
}

interface TempSweetSpot {
    air_temp: string;
    water_temp: string;
    total: number;
}

interface TempChartData {
    temperature: string;
    total_caught: number;
}

interface BigFishAirTemp {
    temperature: string;
    biggest_size: number;
    avg_size: number;
}

// Fly size interfaces
interface BestFlySize {
    size: string;
    total: number;
}

interface FlySizeBySpecies {
    species: string;
    size: string;
    total: number;
}

interface FlySizeBySeason {
    season: string;
    size: string;
    total: number;
}

// Geographic interfaces
interface CatchesByState {
    state: string;
    total: number;
}

interface CatchesByCountry {
    country: string;
    total: number;
}

interface SpeciesByState {
    state: string;
    species_count: number;
}

interface SpeciesByCountry {
    country: string;
    species_count: number;
}

interface FreshwaterVsSaltwater {
    freshwater: number;
    saltwater: number;
}

interface SpeciesByWaterType {
    freshwater: number;
    saltwater: number;
}

// Additional analysis interfaces
interface WeekendWarrior {
    weekend: { catches: number; days: number; avg: number };
    weekday: { catches: number; days: number; avg: number };
}

interface MonthlyPersonalBest {
    month: string;
    biggest_size: number;
}

interface CatchRateTrend {
    month: string;
    catch_rate: number;
}

interface SpeciesByLocation {
    location: string;
    species: string;
    total: number;
}

interface FlyColorByConditions {
    cloud: string;
    color: string;
    total: number;
}

interface MultiSpeciesDays {
    count: number;
    total_days: number;
    percentage: number;
}

interface QuantityVsQuality {
    high_quantity_avg_size: number | null;
    low_quantity_avg_size: number | null;
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
    // Weather stats
    bestCloudCover: { cloud: string; total: number } | null;
    bestWindCondition: { wind: string; total: number } | null;
    catchesByPrecipitation: PrecipitationData[];
    bestBarometricPressure: { pressure: string; total: number } | null;
    // Water condition stats
    bestWaterClarity: { clarity: string; total: number } | null;
    catchesByWaterLevel: WaterLevelData[];
    bestWaterSpeed: { speed: string; total: number } | null;
    bestSurfaceCondition: { condition: string; total: number } | null;
    catchesByTide: TideData[];
    // Moon position stats (Solunar)
    catchesByMoonPosition: MoonPositionData[];
    majorVsMinorFeeding: MajorMinorFeeding;
    bestMoonForBigFish: BestMoonForBigFish | null;
    // Weight stats
    heaviestCatch: HeaviestCatch | null;
    totalWeight: number;
    fishWithWeightCount: number;
    avgWeightPerFish: number;
    avgWeightBySpecies: AvgWeightBySpecies | null;
    // Friend stats
    mostProductiveBuddy: MostProductiveBuddy | null;
    soloVsGroup: SoloVsGroup;
    luckyCharmFriend: LuckyCharmFriend | null;
    // Rod and style stats
    mostSuccessfulRod: RodStats | null;
    bestRodForTrophies: BestRodForTrophies | null;
    catchesByStyle: StyleData[];
    // Golden conditions
    goldenConditions: GoldenConditions;
    bigFishConditions: BigFishConditions;
    // Time pattern stats
    bestHour: BestHour | null;
    seasonalTrends: Record<string, SeasonalTrend>;
    // Location stats
    locationVariety: number;
    mostConsistentSpot: MostConsistentSpot | null;
    underexploredSpots: number;
    bestLocationBySeason: Record<string, BestLocationBySeason>;
    newSpotSuccessRate: number | null;
    // Species stats
    rarestCatches: RarestCatch[];
    sizeImprovement: SizeImprovement;
    // Fly pattern stats
    flyRotation: number;
    oneHitWonders: OneHitWonder[];
    bestFlyByLocation: BestFlyByLocation[];
    bestFlyBySpecies: BestFlyBySpecies[];
    // Progress stats
    yoyComparison: YoyComparison;
    improvementRate: {
        monthlyData: { month: string; monthNum: number; avg: number; total: number; days: number }[];
        percent: number | null;
        maxAvg: number;
    } | null;
    fishingFrequency: Record<string, number>;
    avgSizeTrend: {
        monthlyData: { month: string; monthNum: number; avg: number; count: number }[];
        percent: number | null;
        maxAvg: number;
    } | null;
    avgWeightTrend: {
        monthlyData: { month: string; monthNum: number; avg: number; count: number }[];
        percent: number | null;
        maxAvg: number;
    } | null;
    // Environmental combo stats
    windCloudCombo: WindCloudCombo | null;
    moonTimeCombo: MoonTimeCombo | null;
    waterWeatherCombo: WaterWeatherCombo | null;
    // Gamification stats
    badges: Badge[];
    hotStreak: number | null;
    luckyNumber: LuckyNumber | null;
    // Temperature stats
    tempSweetSpot: TempSweetSpot | null;
    catchesByAirTemp: TempChartData[];
    catchesByWaterTemp: TempChartData[];
    bigFishAirTemp: BigFishAirTemp | null;
    // Fly size stats
    bestFlySize: BestFlySize | null;
    flySizeBySpecies: FlySizeBySpecies[];
    flySizeBySeason: FlySizeBySeason[];
    // Geographic stats
    fishingRadius: number | null;
    catchesByState: CatchesByState[];
    catchesByCountry: CatchesByCountry[];
    speciesByState: SpeciesByState[];
    speciesByCountry: SpeciesByCountry[];
    freshwaterVsSaltwater: FreshwaterVsSaltwater;
    speciesByWaterType: SpeciesByWaterType;
    // Additional analysis stats
    weekendWarrior: WeekendWarrior;
    monthlyPersonalBests: MonthlyPersonalBest[];
    catchRateTrend: CatchRateTrend[];
    speciesByLocation: SpeciesByLocation[];
    flyColorByConditions: FlyColorByConditions[];
    multiSpeciesDays: MultiSpeciesDays;
    quantityVsQuality: QuantityVsQuality;
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
    if (pref?.size) return pref.size;

    // Default sizes for specific cards
    const defaultSizes: Record<string, number> = {
        // 1/2 width cards
        'top_species_by_count': 6,
        'top_species_by_size': 6,
        'species_pie_chart': 6,
        // 1/4 width cards (explicit for clarity)
        'biggest_catch': 3,
        'runner_up': 3,
        'most_successful_fly': 3,
        'biggest_fish_fly': 3,
        'most_successful_fly_type': 3,
        'most_successful_fly_color': 3,
    };

    return defaultSizes[cardId] ?? 3; // Default to 1/4 (3 columns)
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

// Top 5 species for the list display
const topSpecies = computed(() => {
    return props.allSpecies.slice(0, 5);
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

// Moon position pie chart colors (Solunar Theory)
const moonPositionColors: Record<string, string> = {
    'Overhead': '#8b5cf6', // violet-500
    'Underfoot': '#6366f1', // indigo-500
    'Rising': '#a78bfa', // violet-400
    'Setting': '#818cf8', // indigo-400
    'Above Horizon': '#c4b5fd', // violet-300
    'Below Horizon': '#a5b4fc', // indigo-300
};

const getMoonPositionColor = (position: string) => {
    return moonPositionColors[position] || '#8b5cf6';
};

// Moon position pie chart slices
const moonPositionPieSlices = computed(() => {
    const total = props.catchesByMoonPosition.reduce((sum, pos) => sum + Number(pos.total_caught), 0);
    if (total === 0) return [];

    const slices: { path: string; color: string; percentage: number; position: string; total: number }[] = [];
    let currentAngle = -90;

    props.catchesByMoonPosition.forEach((pos) => {
        const caught = Number(pos.total_caught);
        const percentage = caught / total;
        // For a single item (100%), use 359.99 degrees to avoid full circle rendering issue
        const angle = percentage >= 0.9999 ? 359.99 : percentage * 360;

        slices.push({
            path: createPieSlice(100, 100, 70, currentAngle, currentAngle + angle),
            color: getMoonPositionColor(pos.position),
            percentage: Math.round(percentage * 100),
            position: pos.position,
            total: caught,
        });

        currentAngle += angle;
    });

    return slices;
});

// Style pie chart colors - using maximally distinct colors that alternate warm/cool
const styleColors: Record<string, string> = {
    'Dry Fly': '#ef4444', // red-500
    'Nymph': '#3b82f6', // blue-500
    'Streamer': '#f59e0b', // amber-500
    'Euro Nymph': '#8b5cf6', // violet-500
    'Wet Fly': '#10b981', // emerald-500
    'Indicator': '#f97316', // orange-500
    'Hopper Dropper': '#06b6d4', // cyan-500
    'Tenkara': '#ec4899', // pink-500
    'Spey': '#22c55e', // green-500
    'Switch': '#6366f1', // indigo-500
};

const getStyleColor = (style: string, index: number) => {
    if (styleColors[style]) return styleColors[style];
    // Fallback colors - alternating warm/cool for maximum distinction
    const fallbackColors = ['#ef4444', '#3b82f6', '#f59e0b', '#8b5cf6', '#10b981', '#f97316', '#06b6d4', '#ec4899', '#22c55e', '#6366f1'];
    return fallbackColors[index % fallbackColors.length];
};

// Style pie chart slices
const stylePieSlices = computed(() => {
    const total = props.catchesByStyle.reduce((sum, s) => sum + Number(s.total_caught), 0);
    if (total === 0) return [];

    const slices: { path: string; color: string; percentage: number; style: string; total: number }[] = [];
    let currentAngle = -90;

    props.catchesByStyle.forEach((styleData, index) => {
        const caught = Number(styleData.total_caught);
        const percentage = caught / total;
        // For a single item (100%), use 359.99 degrees to avoid full circle rendering issue
        const angle = percentage >= 0.9999 ? 359.99 : percentage * 360;

        slices.push({
            path: createPieSlice(100, 100, 70, currentAngle, currentAngle + angle),
            color: getStyleColor(styleData.style, index),
            percentage: Math.round(percentage * 100),
            style: styleData.style,
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
const hoveredMoonPositionSlice = ref<number | null>(null);
const hoveredStyleSlice = ref<number | null>(null);
const hoveredStateSlice = ref<number | null>(null);
const hoveredCountrySlice = ref<number | null>(null);
const hoveredCatchesStateSlice = ref<number | null>(null);
const hoveredCatchesCountrySlice = ref<number | null>(null);

// State pie chart colors - using distinct colors for geographic regions
const stateColors = [
    '#10b981', // emerald-500
    '#14b8a6', // teal-500
    '#06b6d4', // cyan-500
    '#0ea5e9', // sky-500
    '#3b82f6', // blue-500
    '#6366f1', // indigo-500
    '#8b5cf6', // violet-500
    '#a855f7', // purple-500
    '#d946ef', // fuchsia-500
    '#ec4899', // pink-500
];

const getStateColor = (index: number) => {
    return stateColors[index % stateColors.length];
};

// Country pie chart colors - using distinct colors for countries
const countryColors = [
    '#3b82f6', // blue-500
    '#6366f1', // indigo-500
    '#8b5cf6', // violet-500
    '#a855f7', // purple-500
    '#ec4899', // pink-500
    '#f43f5e', // rose-500
    '#ef4444', // red-500
    '#f97316', // orange-500
    '#f59e0b', // amber-500
    '#eab308', // yellow-500
];

const getCountryColor = (index: number) => {
    return countryColors[index % countryColors.length];
};

// Catches by state pie chart colors
const catchesStateColors = [
    '#22c55e', // green-500
    '#16a34a', // green-600
    '#15803d', // green-700
    '#84cc16', // lime-500
    '#65a30d', // lime-600
    '#4d7c0f', // lime-700
    '#a3e635', // lime-400
    '#bef264', // lime-300
    '#86efac', // green-300
    '#4ade80', // green-400
];

const getCatchesStateColor = (index: number) => {
    return catchesStateColors[index % catchesStateColors.length];
};

// Catches by country pie chart colors
const catchesCountryColors = [
    '#0ea5e9', // sky-500
    '#0284c7', // sky-600
    '#0369a1', // sky-700
    '#06b6d4', // cyan-500
    '#0891b2', // cyan-600
    '#0e7490', // cyan-700
    '#22d3ee', // cyan-400
    '#67e8f9', // cyan-300
    '#7dd3fc', // sky-300
    '#38bdf8', // sky-400
];

const getCatchesCountryColor = (index: number) => {
    return catchesCountryColors[index % catchesCountryColors.length];
};

// Catches by state pie chart slices
const catchesStatePieSlices = computed(() => {
    const total = props.catchesByState.reduce((sum, s) => sum + Number(s.total), 0);
    if (total === 0) return [];

    const slices: { path: string; color: string; percentage: number; state: string; total: number }[] = [];
    let currentAngle = -90;

    props.catchesByState.forEach((stateData, index) => {
        const caught = Number(stateData.total);
        const percentage = caught / total;
        const angle = percentage >= 0.9999 ? 359.99 : percentage * 360;

        slices.push({
            path: createPieSlice(100, 100, 70, currentAngle, currentAngle + angle),
            color: getCatchesStateColor(index),
            percentage: Math.round(percentage * 100),
            state: stateData.state,
            total: caught,
        });

        currentAngle += angle;
    });

    return slices;
});

// Catches by country pie chart slices
const catchesCountryPieSlices = computed(() => {
    const total = props.catchesByCountry.reduce((sum, c) => sum + Number(c.total), 0);
    if (total === 0) return [];

    const slices: { path: string; color: string; percentage: number; country: string; total: number }[] = [];
    let currentAngle = -90;

    props.catchesByCountry.forEach((countryData, index) => {
        const caught = Number(countryData.total);
        const percentage = caught / total;
        const angle = percentage >= 0.9999 ? 359.99 : percentage * 360;

        slices.push({
            path: createPieSlice(100, 100, 70, currentAngle, currentAngle + angle),
            color: getCatchesCountryColor(index),
            percentage: Math.round(percentage * 100),
            country: countryData.country,
            total: caught,
        });

        currentAngle += angle;
    });

    return slices;
});

// State pie chart slices (species count)
const statePieSlices = computed(() => {
    const total = props.speciesByState.reduce((sum, s) => sum + Number(s.species_count), 0);
    if (total === 0) return [];

    const slices: { path: string; color: string; percentage: number; state: string; speciesCount: number }[] = [];
    let currentAngle = -90;

    props.speciesByState.forEach((stateData, index) => {
        const speciesCount = Number(stateData.species_count);
        const percentage = speciesCount / total;
        const angle = percentage >= 0.9999 ? 359.99 : percentage * 360;

        slices.push({
            path: createPieSlice(100, 100, 70, currentAngle, currentAngle + angle),
            color: getStateColor(index),
            percentage: Math.round(percentage * 100),
            state: stateData.state,
            speciesCount: speciesCount,
        });

        currentAngle += angle;
    });

    return slices;
});

// Country pie chart slices (species count)
const countryPieSlices = computed(() => {
    const total = props.speciesByCountry.reduce((sum, c) => sum + Number(c.species_count), 0);
    if (total === 0) return [];

    const slices: { path: string; color: string; percentage: number; country: string; speciesCount: number }[] = [];
    let currentAngle = -90;

    props.speciesByCountry.forEach((countryData, index) => {
        const speciesCount = Number(countryData.species_count);
        const percentage = speciesCount / total;
        const angle = percentage >= 0.9999 ? 359.99 : percentage * 360;

        slices.push({
            path: createPieSlice(100, 100, 70, currentAngle, currentAngle + angle),
            color: getCountryColor(index),
            percentage: Math.round(percentage * 100),
            country: countryData.country,
            speciesCount: speciesCount,
        });

        currentAngle += angle;
    });

    return slices;
});
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
                    <DashboardCardHeader title="Total Catches" emoji="🐟" color="blue">
                        <template #subtitle>Your {{ yearLabel.toLowerCase() }} fishing stats</template>
                    </DashboardCardHeader>
                    <CardContent class="pb-4 pt-1">
                        <div class="text-3xl font-bold text-blue-700 dark:text-blue-300">{{ stats.totalCatches }}</div>
                        <p class="text-sm text-muted-foreground mt-1">Across {{ stats.totalTrips }} trips</p>
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
                    <DashboardCardHeader title="Favorite Location" :icon="MapPin" color="emerald" />
                    <CardContent class="pb-3">
                        <div class="text-2xl font-bold text-emerald-700 dark:text-emerald-300">{{ stats.favoriteLocation || 'N/A' }}</div>
                        <p class="text-xs text-muted-foreground">Most visited spot</p>
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
                    <DashboardCardHeader title="Fishing Buddies" :icon="Users" color="purple" />
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
                    <DashboardCardHeader title="Biggest Catch" subtitle="Largest fish caught" :icon="Award" color="yellow" />
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
                    <DashboardCardHeader title="Biggest Catch" subtitle="Largest fish caught" :icon="Award" color="gray" />
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
                    <DashboardCardHeader title="Runner Up" subtitle="Second largest catch" :icon="Award" color="orange" />
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
                    <DashboardCardHeader title="Runner Up" subtitle="Second largest catch" :icon="Award" color="gray" />
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
                    <DashboardCardHeader title="Favorite Weekday" subtitle="Most productive day of week" :icon="Calendar" color="cyan" />
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
                    <DashboardCardHeader title="Consecutive Days Streak" subtitle="Longest fishing streak" :icon="Flame" color="orange" />
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
                    <DashboardCardHeader title="Average per Trip" subtitle="Your catch rate efficiency" :icon="BarChart3" color="indigo" />
                    <CardContent class="pb-4 pt-1">
                        <div class="text-3xl font-bold text-indigo-700 dark:text-indigo-300">
                            {{ speciesStats.totalTrips > 0 ? (speciesStats.totalFish / speciesStats.totalTrips).toFixed(1) : '0' }}
                        </div>
                        <p class="text-sm text-muted-foreground mt-1">
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
                    <DashboardCardHeader title="Days Fished" subtitle="Total days on the water" :icon="Calendar" color="sky" />
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
                    <DashboardCardHeader title="Days Skunked" subtitle="Days without a catch" :icon="X" color="red" />
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
                    <DashboardCardHeader title="Most in a Day" subtitle="Personal best day" :icon="Award" color="violet" />
                    <CardContent class="pb-3">
                        <div class="text-2xl font-bold text-violet-700 dark:text-violet-300">{{ yearStats.mostInDay }}</div>
                        <p class="text-xs text-muted-foreground">
                            Personal best day
                        </p>
                    </CardContent>
                </DashboardCard>

                <!-- Most Species in a Day -->
                <DashboardCard
                    v-if="isCardVisible('most_species_in_day')"
                    card-id="most_species_in_day"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('most_species_in_day')"
                    :order="getCardOrder('most_species_in_day')"
                    :size="getCardSize('most_species_in_day')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('most_species_in_day')"
                    :is-last="isCardLast('most_species_in_day')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('most_species_in_day')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-emerald-50/30 to-teal-50/30 dark:from-emerald-950/10 dark:to-teal-950/10"
                >
                    <DashboardCardHeader title="Most Species in a Day" subtitle="Best single-day variety" emoji="🐟" color="emerald" gradientTo="teal" />
                    <CardContent class="pt-0 pb-4">
                        <div v-if="yearStats.mostSpeciesInDay" class="space-y-2">
                            <div class="text-3xl font-bold text-emerald-600 dark:text-emerald-400">{{ yearStats.mostSpeciesInDay.count }}</div>
                            <div class="text-sm text-muted-foreground">species on {{ formatDate(yearStats.mostSpeciesInDay.date) }}</div>
                        </div>
                        <p v-else class="text-muted-foreground text-sm">No data yet</p>
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
                    <DashboardCardHeader title="Fish Caught per Month" subtitle="Monthly catch distribution" :icon="Calendar" color="blue" />
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
                    <DashboardCardHeader title="Fish Caught by Moon Phase" subtitle="Lunar influence on catches" :icon="Moon" color="slate" />
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
                    <DashboardCardHeader title="Fish Caught by Sun Phase" subtitle="Time of day distribution" :icon="Sun" color="amber" />
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
                    <DashboardCardHeader title="Most Successful Fly" subtitle="Your top producing fly" :icon="Award" color="rose" />
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
                    <DashboardCardHeader title="Biggest Fish Fly" subtitle="Fly for trophy catches" :icon="Award" color="teal" />
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
                    <DashboardCardHeader title="Most Successful Type" subtitle="Best fly type" :icon="Award" color="purple" />
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
                    <DashboardCardHeader title="Most Successful Color" subtitle="Best fly color" :icon="Award" color="indigo" />
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
                    <DashboardCardHeader title="Top Species by Size" subtitle="Biggest fish per species" :icon="Award" color="orange" />
                    <CardContent class="pt-0 pb-1">
                        <div v-if="topSpeciesBySize.length > 0" class="space-y-2">
                            <div v-for="(species, index) in topSpeciesBySize" :key="species.species" class="flex items-center gap-3 pb-2 border-b last:border-0">
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
                    <DashboardCardHeader title="Top Locations by Count" subtitle="Your most productive spots" :icon="MapPin" color="lime" />
                    <CardContent class="pt-0 pb-1">
                        <div v-if="topLocations.length > 0" class="space-y-2">
                            <div v-for="(location, index) in topLocations" :key="location.name" class="flex items-center gap-3 pb-2 border-b last:border-0">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-lime-100 dark:bg-lime-900/30 text-lime-700 dark:text-lime-300 text-sm font-bold flex-shrink-0">
                                    {{ index + 1 }}
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-medium">{{ location.name }}</span>
                                        <span v-if="index === 0">🥇</span>
                                        <span v-else-if="index === 1">🥈</span>
                                        <span v-else-if="index === 2">🥉</span>
                                    </div>
                                    <div class="text-xs text-muted-foreground">{{ location.total }} catches</div>
                                </div>
                                <div v-if="location.city || location.state || location.country" class="text-right">
                                    <div v-if="location.city" class="text-xs font-medium text-lime-700 dark:text-lime-300">{{ location.city }}</div>
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
                    <DashboardCardHeader title="Top Locations by Size" subtitle="Biggest fish per location" :icon="Award" color="emerald" />
                    <CardContent class="pt-0 pb-1">
                        <div v-if="topLocationsBySize.length > 0" class="space-y-2">
                            <div v-for="(location, index) in topLocationsBySize" :key="location.name" class="flex items-center gap-3 pb-2 border-b last:border-0">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 text-sm font-bold flex-shrink-0">
                                    {{ index + 1 }}
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-medium">{{ location.name }}</span>
                                        <span v-if="index === 0">🥇</span>
                                        <span v-else-if="index === 1">🥈</span>
                                        <span v-else-if="index === 2">🥉</span>
                                    </div>
                                    <div class="text-xs text-muted-foreground flex items-center gap-2">
                                        <span>Largest catch:</span>
                                        <span class="font-medium text-emerald-700 dark:text-emerald-300">{{ formatSize(location.biggest_size) }}"</span>
                                    </div>
                                </div>
                                <div v-if="location.city || location.state || location.country" class="text-right">
                                    <div v-if="location.city" class="text-xs font-medium text-emerald-700 dark:text-emerald-300">{{ location.city }}</div>
                                    <div v-if="location.state || location.country" class="text-xs text-muted-foreground">{{ location.state || location.country }}</div>
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-muted-foreground">No location data available</p>
                    </CardContent>
                </DashboardCard>

                <!-- ========== WEATHER CARDS ========== -->

                <!-- Best Cloud Cover -->
                <DashboardCard
                    v-if="isCardVisible('best_cloud_cover')"
                    card-id="best_cloud_cover"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('best_cloud_cover')"
                    :order="getCardOrder('best_cloud_cover')"
                    :size="getCardSize('best_cloud_cover')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('best_cloud_cover')"
                    :is-last="isCardLast('best_cloud_cover')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('best_cloud_cover')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-sky-50/30 to-transparent dark:from-sky-950/10"
                >
                    <DashboardCardHeader title="Best Cloud Cover" subtitle="Most productive sky condition" emoji="☁️" color="sky" />
                    <CardContent class="pt-0 pb-4">
                        <div v-if="bestCloudCover" class="space-y-2">
                            <div class="text-xl font-bold text-sky-700 dark:text-sky-300">{{ bestCloudCover.cloud }}</div>
                            <span class="inline-flex items-center gap-2 rounded-full bg-sky-100 px-2.5 py-0.5 text-xs font-medium text-sky-800 dark:bg-sky-900/30 dark:text-sky-300">
                                🎣 {{ bestCloudCover.total }} fish caught
                            </span>
                        </div>
                        <p v-else class="text-muted-foreground">No weather data yet</p>
                    </CardContent>
                </DashboardCard>

                <!-- Best Wind Condition -->
                <DashboardCard
                    v-if="isCardVisible('best_wind_condition')"
                    card-id="best_wind_condition"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('best_wind_condition')"
                    :order="getCardOrder('best_wind_condition')"
                    :size="getCardSize('best_wind_condition')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('best_wind_condition')"
                    :is-last="isCardLast('best_wind_condition')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('best_wind_condition')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-slate-50/30 to-transparent dark:from-slate-950/10"
                >
                    <DashboardCardHeader title="Best Wind Condition" subtitle="Most productive wind" emoji="💨" color="slate" />
                    <CardContent class="pt-0 pb-4">
                        <div v-if="bestWindCondition" class="space-y-2">
                            <div class="text-xl font-bold text-slate-700 dark:text-slate-300">{{ bestWindCondition.wind }}</div>
                            <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-800 dark:bg-slate-900/30 dark:text-slate-300">
                                🎣 {{ bestWindCondition.total }} fish caught
                            </span>
                        </div>
                        <p v-else class="text-muted-foreground">No wind data yet</p>
                    </CardContent>
                </DashboardCard>

                <!-- Catches by Precipitation -->
                <DashboardCard
                    v-if="isCardVisible('catches_by_precipitation')"
                    card-id="catches_by_precipitation"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('catches_by_precipitation')"
                    :order="getCardOrder('catches_by_precipitation')"
                    :size="getCardSize('catches_by_precipitation')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('catches_by_precipitation')"
                    :is-last="isCardLast('catches_by_precipitation')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('catches_by_precipitation')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-blue-50/30 to-transparent dark:from-blue-950/10"
                >
                    <DashboardCardHeader title="Catches by Precipitation" subtitle="Fish caught in different weather" emoji="🌧️" color="blue" />
                    <CardContent class="pt-0 pb-4">
                        <div v-if="catchesByPrecipitation.length > 0" class="space-y-2">
                            <div v-for="item in catchesByPrecipitation" :key="item.precipitation" class="flex items-center justify-between">
                                <span class="text-sm font-medium">{{ item.precipitation }}</span>
                                <span class="text-sm font-bold text-blue-700 dark:text-blue-300">{{ item.total_caught }}</span>
                            </div>
                        </div>
                        <p v-else class="text-muted-foreground">No precipitation data yet</p>
                    </CardContent>
                </DashboardCard>

                <!-- Best Barometric Pressure -->
                <DashboardCard
                    v-if="isCardVisible('best_barometric_pressure')"
                    card-id="best_barometric_pressure"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('best_barometric_pressure')"
                    :order="getCardOrder('best_barometric_pressure')"
                    :size="getCardSize('best_barometric_pressure')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('best_barometric_pressure')"
                    :is-last="isCardLast('best_barometric_pressure')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('best_barometric_pressure')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-indigo-50/30 to-transparent dark:from-indigo-950/10"
                >
                    <DashboardCardHeader title="Best Barometric Pressure" subtitle="Most productive pressure" emoji="📊" color="indigo" />
                    <CardContent class="pt-0 pb-4">
                        <div v-if="bestBarometricPressure" class="space-y-2">
                            <div class="text-xl font-bold text-indigo-700 dark:text-indigo-300">{{ bestBarometricPressure.pressure }}</div>
                            <span class="inline-flex items-center gap-2 rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-medium text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300">
                                🎣 {{ bestBarometricPressure.total }} fish caught
                            </span>
                        </div>
                        <p v-else class="text-muted-foreground">No pressure data yet</p>
                    </CardContent>
                </DashboardCard>

                <!-- ========== WATER CONDITION CARDS ========== -->

                <!-- Best Water Clarity -->
                <DashboardCard
                    v-if="isCardVisible('best_water_clarity')"
                    card-id="best_water_clarity"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('best_water_clarity')"
                    :order="getCardOrder('best_water_clarity')"
                    :size="getCardSize('best_water_clarity')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('best_water_clarity')"
                    :is-last="isCardLast('best_water_clarity')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('best_water_clarity')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-cyan-50/30 to-transparent dark:from-cyan-950/10"
                >
                    <DashboardCardHeader title="Best Water Clarity" subtitle="Most productive water clarity" emoji="💧" color="cyan" />
                    <CardContent class="pt-0 pb-4">
                        <div v-if="bestWaterClarity" class="space-y-2">
                            <div class="text-xl font-bold text-cyan-700 dark:text-cyan-300">{{ bestWaterClarity.clarity }}</div>
                            <span class="inline-flex items-center gap-2 rounded-full bg-cyan-100 px-2.5 py-0.5 text-xs font-medium text-cyan-800 dark:bg-cyan-900/30 dark:text-cyan-300">
                                🎣 {{ bestWaterClarity.total }} fish caught
                            </span>
                        </div>
                        <p v-else class="text-muted-foreground">No water clarity data yet</p>
                    </CardContent>
                </DashboardCard>

                <!-- Catches by Water Level -->
                <DashboardCard
                    v-if="isCardVisible('catches_by_water_level')"
                    card-id="catches_by_water_level"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('catches_by_water_level')"
                    :order="getCardOrder('catches_by_water_level')"
                    :size="getCardSize('catches_by_water_level')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('catches_by_water_level')"
                    :is-last="isCardLast('catches_by_water_level')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('catches_by_water_level')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-teal-50/30 to-transparent dark:from-teal-950/10"
                >
                    <DashboardCardHeader title="Catches by Water Level" subtitle="Fish caught at different levels" emoji="📏" color="teal" />
                    <CardContent class="pt-0 pb-4">
                        <div v-if="catchesByWaterLevel.length > 0" class="space-y-2">
                            <div v-for="item in catchesByWaterLevel" :key="item.level" class="flex items-center justify-between">
                                <span class="text-sm font-medium">{{ item.level }}</span>
                                <span class="text-sm font-bold text-teal-700 dark:text-teal-300">{{ item.total_caught }}</span>
                            </div>
                        </div>
                        <p v-else class="text-muted-foreground">No water level data yet</p>
                    </CardContent>
                </DashboardCard>

                <!-- Best Water Speed -->
                <DashboardCard
                    v-if="isCardVisible('best_water_speed')"
                    card-id="best_water_speed"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('best_water_speed')"
                    :order="getCardOrder('best_water_speed')"
                    :size="getCardSize('best_water_speed')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('best_water_speed')"
                    :is-last="isCardLast('best_water_speed')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('best_water_speed')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-blue-50/30 to-transparent dark:from-blue-950/10"
                >
                    <DashboardCardHeader title="Best Water Speed" subtitle="Most productive current speed" emoji="🌊" color="blue" />
                    <CardContent class="pt-0 pb-4">
                        <div v-if="bestWaterSpeed" class="space-y-2">
                            <div class="text-xl font-bold text-blue-700 dark:text-blue-300">{{ bestWaterSpeed.speed }}</div>
                            <span class="inline-flex items-center gap-2 rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                🎣 {{ bestWaterSpeed.total }} fish caught
                            </span>
                        </div>
                        <p v-else class="text-muted-foreground">No water speed data yet</p>
                    </CardContent>
                </DashboardCard>

                <!-- Best Surface Condition -->
                <DashboardCard
                    v-if="isCardVisible('best_surface_condition')"
                    card-id="best_surface_condition"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('best_surface_condition')"
                    :order="getCardOrder('best_surface_condition')"
                    :size="getCardSize('best_surface_condition')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('best_surface_condition')"
                    :is-last="isCardLast('best_surface_condition')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('best_surface_condition')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-sky-50/30 to-transparent dark:from-sky-950/10"
                >
                    <DashboardCardHeader title="Best Surface Condition" subtitle="Most productive surface" emoji="〰️" color="sky" />
                    <CardContent class="pt-0 pb-4">
                        <div v-if="bestSurfaceCondition" class="space-y-2">
                            <div class="text-xl font-bold text-sky-700 dark:text-sky-300">{{ bestSurfaceCondition.condition }}</div>
                            <span class="inline-flex items-center gap-2 rounded-full bg-sky-100 px-2.5 py-0.5 text-xs font-medium text-sky-800 dark:bg-sky-900/30 dark:text-sky-300">
                                🎣 {{ bestSurfaceCondition.total }} fish caught
                            </span>
                        </div>
                        <p v-else class="text-muted-foreground">No surface data yet</p>
                    </CardContent>
                </DashboardCard>

                <!-- Catches by Tide -->
                <DashboardCard
                    v-if="isCardVisible('catches_by_tide')"
                    card-id="catches_by_tide"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('catches_by_tide')"
                    :order="getCardOrder('catches_by_tide')"
                    :size="getCardSize('catches_by_tide')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('catches_by_tide')"
                    :is-last="isCardLast('catches_by_tide')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('catches_by_tide')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-indigo-50/30 to-transparent dark:from-indigo-950/10"
                >
                    <DashboardCardHeader title="Catches by Tide" subtitle="Tidal influence on catches" emoji="🌊" color="indigo" />
                    <CardContent class="pt-0 pb-4">
                        <div v-if="catchesByTide.length > 0" class="space-y-2">
                            <div v-for="item in catchesByTide" :key="item.tide" class="flex items-center justify-between">
                                <span class="text-sm font-medium">{{ item.tide }}</span>
                                <span class="text-sm font-bold text-indigo-700 dark:text-indigo-300">{{ item.total_caught }}</span>
                            </div>
                        </div>
                        <p v-else class="text-muted-foreground">No tide data yet</p>
                    </CardContent>
                </DashboardCard>

                <!-- ========== MOON POSITION CARDS (SOLUNAR) ========== -->

                <!-- Catches by Moon Position -->
                <DashboardCard
                    v-if="isCardVisible('catches_by_moon_position')"
                    card-id="catches_by_moon_position"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('catches_by_moon_position')"
                    :order="getCardOrder('catches_by_moon_position')"
                    :size="getCardSize('catches_by_moon_position')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('catches_by_moon_position')"
                    :is-last="isCardLast('catches_by_moon_position')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('catches_by_moon_position')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-violet-50/30 to-transparent dark:from-violet-950/10"
                >
                    <DashboardCardHeader title="Catches by Moon Position" subtitle="Solunar position data" :icon="Moon" color="violet" />
                    <CardContent class="pt-0 pb-3">
                        <div v-if="catchesByMoonPosition.length > 0" class="flex items-center gap-4">
                            <!-- SVG Pie Chart -->
                            <div class="relative w-44 h-44 flex-shrink-0">
                                <svg class="w-full h-full" viewBox="0 0 200 200">
                                    <!-- Pie slices -->
                                    <g v-for="(slice, index) in moonPositionPieSlices" :key="`moon-pos-slice-${index}`">
                                        <path
                                            :d="slice.path"
                                            :fill="slice.color"
                                            :class="hoveredMoonPositionSlice === index ? 'opacity-100' : 'opacity-80'"
                                            class="cursor-pointer transition-all hover:opacity-100 stroke-violet-200 dark:stroke-violet-800"
                                            stroke-width="1"
                                            @mouseenter="hoveredMoonPositionSlice = index"
                                            @mouseleave="hoveredMoonPositionSlice = null"
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
                                        {{ catchesByMoonPosition.reduce((sum, p) => sum + Number(p.total_caught), 0) }}
                                    </text>
                                    <text
                                        x="100"
                                        y="110"
                                        text-anchor="middle"
                                        class="text-xs text-muted-foreground"
                                        fill="currentColor"
                                    >
                                        Total
                                    </text>
                                </svg>
                            </div>

                            <!-- Legend -->
                            <div class="flex-1 space-y-1 max-h-44 overflow-y-auto">
                                <div
                                    v-for="(pos, index) in catchesByMoonPosition"
                                    :key="pos.position"
                                    class="flex items-center justify-between gap-2 p-1.5 rounded hover:bg-muted/50 cursor-pointer transition-colors"
                                    :class="hoveredMoonPositionSlice === index ? 'bg-muted' : ''"
                                    @mouseenter="hoveredMoonPositionSlice = index"
                                    @mouseleave="hoveredMoonPositionSlice = null"
                                >
                                    <div class="flex items-center gap-2 min-w-0">
                                        <div
                                            class="w-2.5 h-2.5 rounded-full flex-shrink-0"
                                            :style="{ backgroundColor: getMoonPositionColor(pos.position) }"
                                        ></div>
                                        <span class="text-xs font-medium truncate">{{ pos.position }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 flex-shrink-0">
                                        <span class="text-xs font-bold">{{ pos.total_caught }}</span>
                                        <span class="text-xs text-muted-foreground">
                                            ({{ moonPositionPieSlices[index]?.percentage }}%)
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-muted-foreground">No moon position data yet</p>
                    </CardContent>
                </DashboardCard>

                <!-- Major vs Minor Feeding -->
                <DashboardCard
                    v-if="isCardVisible('major_vs_minor_feeding')"
                    card-id="major_vs_minor_feeding"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('major_vs_minor_feeding')"
                    :order="getCardOrder('major_vs_minor_feeding')"
                    :size="getCardSize('major_vs_minor_feeding')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('major_vs_minor_feeding')"
                    :is-last="isCardLast('major_vs_minor_feeding')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('major_vs_minor_feeding')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-purple-50/30 to-transparent dark:from-purple-950/10"
                >
                    <DashboardCardHeader title="Major vs Minor Feeding" subtitle="Overhead/Underfoot vs Rising/Setting" emoji="🌙" color="purple" />
                    <CardContent class="pt-4 pb-4">
                        <div v-if="majorVsMinorFeeding.major > 0 || majorVsMinorFeeding.minor > 0 || majorVsMinorFeeding.other > 0" class="space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="text-lg">⭐</span>
                                    <span class="text-sm font-medium">Major (Overhead/Underfoot)</span>
                                </div>
                                <span class="text-lg font-bold text-purple-700 dark:text-purple-300">{{ majorVsMinorFeeding.major }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="text-lg">✨</span>
                                    <span class="text-sm font-medium">Minor (Rising/Setting)</span>
                                </div>
                                <span class="text-lg font-bold text-purple-700 dark:text-purple-300">{{ majorVsMinorFeeding.minor }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="text-lg">🌅</span>
                                    <span class="text-sm font-medium">Off-Peak (Between Windows)</span>
                                </div>
                                <span class="text-lg font-bold text-purple-700 dark:text-purple-300">{{ majorVsMinorFeeding.other }}</span>
                            </div>
                        </div>
                        <p v-else class="text-muted-foreground">No feeding window data yet</p>
                    </CardContent>
                </DashboardCard>

                <!-- Best Moon for Big Fish -->
                <DashboardCard
                    v-if="isCardVisible('best_moon_for_big_fish')"
                    card-id="best_moon_for_big_fish"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('best_moon_for_big_fish')"
                    :order="getCardOrder('best_moon_for_big_fish')"
                    :size="getCardSize('best_moon_for_big_fish')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('best_moon_for_big_fish')"
                    :is-last="isCardLast('best_moon_for_big_fish')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('best_moon_for_big_fish')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-fuchsia-50/30 to-transparent dark:from-fuchsia-950/10"
                >
                    <DashboardCardHeader title="Best Moon for Big Fish" subtitle="Moon position & phase for trophy catches" emoji="🏆" color="fuchsia" />
                    <CardContent class="pt-4 pb-4">
                        <div v-if="bestMoonForBigFish.position || bestMoonForBigFish.phase" class="space-y-3">
                            <!-- Moon Position -->
                            <div v-if="bestMoonForBigFish.position" class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm">🌙</span>
                                    <span class="text-lg font-bold text-fuchsia-700 dark:text-fuchsia-300">{{ bestMoonForBigFish.position }}</span>
                                </div>
                                <div class="flex flex-wrap gap-2 pl-6">
                                    <span class="inline-flex items-center gap-1 rounded-full bg-fuchsia-100 px-2 py-0.5 text-xs font-medium text-fuchsia-800 dark:bg-fuchsia-900/30 dark:text-fuchsia-300">
                                        📏 Biggest: {{ formatSize(bestMoonForBigFish.position_biggest_size) }}"
                                    </span>
                                    <span class="inline-flex items-center gap-1 rounded-full bg-fuchsia-100 px-2 py-0.5 text-xs font-medium text-fuchsia-800 dark:bg-fuchsia-900/30 dark:text-fuchsia-300">
                                        📊 Avg: {{ formatSize(bestMoonForBigFish.position_avg_size) }}"
                                    </span>
                                </div>
                            </div>
                            <!-- Moon Phase -->
                            <div v-if="bestMoonForBigFish.phase" class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm">🌕</span>
                                    <span class="text-lg font-bold text-fuchsia-700 dark:text-fuchsia-300">{{ bestMoonForBigFish.phase }}</span>
                                </div>
                                <div class="flex flex-wrap gap-2 pl-6">
                                    <span class="inline-flex items-center gap-1 rounded-full bg-fuchsia-100 px-2 py-0.5 text-xs font-medium text-fuchsia-800 dark:bg-fuchsia-900/30 dark:text-fuchsia-300">
                                        📏 Biggest: {{ formatSize(bestMoonForBigFish.phase_biggest_size) }}"
                                    </span>
                                    <span class="inline-flex items-center gap-1 rounded-full bg-fuchsia-100 px-2 py-0.5 text-xs font-medium text-fuchsia-800 dark:bg-fuchsia-900/30 dark:text-fuchsia-300">
                                        📊 Avg: {{ formatSize(bestMoonForBigFish.phase_avg_size) }}"
                                    </span>
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-muted-foreground">No size data yet</p>
                    </CardContent>
                </DashboardCard>

                <!-- ========== WEIGHT CARDS ========== -->

                <!-- Heaviest Catch -->
                <DashboardCard
                    v-if="isCardVisible('heaviest_catch')"
                    card-id="heaviest_catch"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('heaviest_catch')"
                    :order="getCardOrder('heaviest_catch')"
                    :size="getCardSize('heaviest_catch')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('heaviest_catch')"
                    :is-last="isCardLast('heaviest_catch')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('heaviest_catch')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-amber-50/30 to-transparent dark:from-amber-950/10"
                >
                    <DashboardCardHeader title="Heaviest Catch" subtitle="Your biggest fish by weight" emoji="⚖️" color="amber" />
                    <CardContent class="pt-0 pb-4">
                        <div v-if="heaviestCatch" class="space-y-2">
                            <div class="text-2xl font-bold text-amber-700 dark:text-amber-300">{{ heaviestCatch.weight }} lbs</div>
                            <div class="text-sm text-muted-foreground">
                                <span v-if="heaviestCatch.species">{{ heaviestCatch.species }}</span>
                                <span v-if="heaviestCatch.location"> @ {{ heaviestCatch.location }}</span>
                            </div>
                            <div class="text-xs text-muted-foreground">{{ formatDate(heaviestCatch.date) }}</div>
                        </div>
                        <p v-else class="text-muted-foreground">No weight data yet</p>
                    </CardContent>
                </DashboardCard>

                <!-- Total Weight -->
                <DashboardCard
                    v-if="isCardVisible('total_weight')"
                    card-id="total_weight"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('total_weight')"
                    :order="getCardOrder('total_weight')"
                    :size="getCardSize('total_weight')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('total_weight')"
                    :is-last="isCardLast('total_weight')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('total_weight')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-orange-50/30 to-transparent dark:from-orange-950/10"
                >
                    <DashboardCardHeader title="Total Weight Caught" subtitle="Combined weight of all fish" emoji="📦" color="orange" />
                    <CardContent class="pt-0 pb-4">
                        <div v-if="totalWeight > 0" class="space-y-2">
                            <div class="text-2xl font-bold text-orange-700 dark:text-orange-300">{{ totalWeight }} lbs</div>
                            <div class="flex flex-wrap gap-2">
                                <span class="inline-flex items-center gap-1 rounded-full bg-orange-100 px-2.5 py-0.5 text-xs font-medium text-orange-800 dark:bg-orange-900/30 dark:text-orange-300">
                                    🎣 {{ fishWithWeightCount }} fish weighed
                                </span>
                                <span v-if="avgWeightPerFish > 0" class="inline-flex items-center gap-1 rounded-full bg-orange-100 px-2.5 py-0.5 text-xs font-medium text-orange-800 dark:bg-orange-900/30 dark:text-orange-300">
                                    ⚖️ Avg: {{ avgWeightPerFish }} lbs
                                </span>
                            </div>
                            <div v-if="heaviestCatch" class="text-xs text-muted-foreground">
                                Heaviest: {{ heaviestCatch.weight }} lbs<span v-if="heaviestCatch.species"> ({{ heaviestCatch.species }})</span>
                            </div>
                        </div>
                        <p v-else class="text-muted-foreground">No weight data yet</p>
                    </CardContent>
                </DashboardCard>

                <!-- Avg Weight by Species -->
                <DashboardCard
                    v-if="isCardVisible('avg_weight_by_species')"
                    card-id="avg_weight_by_species"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('avg_weight_by_species')"
                    :order="getCardOrder('avg_weight_by_species')"
                    :size="getCardSize('avg_weight_by_species')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('avg_weight_by_species')"
                    :is-last="isCardLast('avg_weight_by_species')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('avg_weight_by_species')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-yellow-50/30 to-transparent dark:from-yellow-950/10"
                >
                    <DashboardCardHeader title="Avg Weight by Species" subtitle="Heaviest species on average" emoji="📊" color="yellow" />
                    <CardContent class="pt-0 pb-4">
                        <div v-if="avgWeightBySpecies" class="space-y-2">
                            <div class="text-xl font-bold text-yellow-700 dark:text-yellow-300">{{ avgWeightBySpecies.species }}</div>
                            <div class="flex flex-wrap gap-2">
                                <span class="inline-flex items-center gap-1 rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300">
                                    ⚖️ Avg: {{ avgWeightBySpecies.avg_weight }} lbs
                                </span>
                                <span class="inline-flex items-center gap-1 rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300">
                                    🎣 {{ avgWeightBySpecies.count }} caught
                                </span>
                            </div>
                        </div>
                        <p v-else class="text-muted-foreground">Need 2+ catches with weight</p>
                    </CardContent>
                </DashboardCard>

                <!-- ========== FRIEND/SOCIAL CARDS ========== -->

                <!-- Most Productive Buddy -->
                <DashboardCard
                    v-if="isCardVisible('most_productive_buddy')"
                    card-id="most_productive_buddy"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('most_productive_buddy')"
                    :order="getCardOrder('most_productive_buddy')"
                    :size="getCardSize('most_productive_buddy')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('most_productive_buddy')"
                    :is-last="isCardLast('most_productive_buddy')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('most_productive_buddy')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-green-50/30 to-transparent dark:from-green-950/10"
                >
                    <DashboardCardHeader title="Most Productive Buddy" subtitle="Best fishing partner" :icon="Users" color="green" />
                    <CardContent class="pt-0 pb-4">
                        <div v-if="mostProductiveBuddy" class="space-y-2">
                            <div class="text-xl font-bold text-green-700 dark:text-green-300">{{ mostProductiveBuddy.name }}</div>
                            <div class="flex flex-wrap gap-2">
                                <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                    🎣 {{ mostProductiveBuddy.total }} fish together
                                </span>
                                <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                    📅 {{ mostProductiveBuddy.trips }} trips
                                </span>
                            </div>
                        </div>
                        <p v-else class="text-muted-foreground">No buddy data yet</p>
                    </CardContent>
                </DashboardCard>

                <!-- Solo vs Group -->
                <DashboardCard
                    v-if="isCardVisible('solo_vs_group')"
                    card-id="solo_vs_group"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('solo_vs_group')"
                    :order="getCardOrder('solo_vs_group')"
                    :size="getCardSize('solo_vs_group')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('solo_vs_group')"
                    :is-last="isCardLast('solo_vs_group')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('solo_vs_group')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-teal-50/30 to-transparent dark:from-teal-950/10"
                >
                    <DashboardCardHeader title="Solo vs Group Fishing" subtitle="Compare your fishing styles" emoji="🎯" color="teal" />
                    <CardContent class="pt-0 pb-4">
                        <div v-if="soloVsGroup.solo.trips > 0 || soloVsGroup.group.trips > 0" class="space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="text-lg">🧘</span>
                                    <span class="text-sm font-medium">Solo</span>
                                </div>
                                <div class="text-right">
                                    <span class="text-sm font-bold text-teal-700 dark:text-teal-300">{{ soloVsGroup.solo.catches }}</span>
                                    <span class="text-xs text-muted-foreground ml-1">({{ soloVsGroup.solo.avg }}/trip)</span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="text-lg">👥</span>
                                    <span class="text-sm font-medium">Group</span>
                                </div>
                                <div class="text-right">
                                    <span class="text-sm font-bold text-teal-700 dark:text-teal-300">{{ soloVsGroup.group.catches }}</span>
                                    <span class="text-xs text-muted-foreground ml-1">({{ soloVsGroup.group.avg }}/trip)</span>
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-muted-foreground">No trip data yet</p>
                    </CardContent>
                </DashboardCard>

                <!-- Lucky Charm Friend -->
                <DashboardCard
                    v-if="isCardVisible('lucky_charm_friend')"
                    card-id="lucky_charm_friend"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('lucky_charm_friend')"
                    :order="getCardOrder('lucky_charm_friend')"
                    :size="getCardSize('lucky_charm_friend')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('lucky_charm_friend')"
                    :is-last="isCardLast('lucky_charm_friend')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('lucky_charm_friend')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-emerald-50/30 to-transparent dark:from-emerald-950/10"
                >
                    <DashboardCardHeader title="Lucky Charm Friend" subtitle="Present during biggest catches" emoji="🍀" color="emerald" />
                    <CardContent class="pt-0 pb-4">
                        <div v-if="luckyCharmFriend" class="space-y-2">
                            <div class="text-xl font-bold text-emerald-700 dark:text-emerald-300">{{ luckyCharmFriend.name }}</div>
                            <div class="flex flex-wrap gap-2">
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300">
                                    📏 Biggest: {{ formatSize(luckyCharmFriend.biggest_fish) }}"
                                </span>
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300">
                                    📊 Avg: {{ formatSize(luckyCharmFriend.avg_size) }}"
                                </span>
                            </div>
                        </div>
                        <p v-else class="text-muted-foreground">No friend data yet</p>
                    </CardContent>
                </DashboardCard>

                <!-- ========== ROD & STYLE CARDS ========== -->

                <!-- Most Successful Rod -->
                <DashboardCard
                    v-if="isCardVisible('most_successful_rod')"
                    card-id="most_successful_rod"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('most_successful_rod')"
                    :order="getCardOrder('most_successful_rod')"
                    :size="getCardSize('most_successful_rod')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('most_successful_rod')"
                    :is-last="isCardLast('most_successful_rod')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('most_successful_rod')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-stone-50/30 to-transparent dark:from-stone-950/10"
                >
                    <DashboardCardHeader title="Most Successful Rod" subtitle="Your go-to rod" emoji="🎣" color="stone" />
                    <CardContent class="pt-0 pb-4">
                        <div v-if="mostSuccessfulRod" class="space-y-2">
                            <div class="text-xl font-bold text-stone-700 dark:text-stone-300">{{ mostSuccessfulRod.name }}</div>
                            <div class="flex flex-wrap gap-2">
                                <span class="inline-flex items-center gap-1 rounded-full bg-stone-100 px-2.5 py-0.5 text-xs font-medium text-stone-800 dark:bg-stone-900/30 dark:text-stone-300">
                                    🎣 {{ mostSuccessfulRod.total }} fish
                                </span>
                                <span class="inline-flex items-center gap-1 rounded-full bg-stone-100 px-2.5 py-0.5 text-xs font-medium text-stone-800 dark:bg-stone-900/30 dark:text-stone-300">
                                    📅 {{ mostSuccessfulRod.days }} days
                                </span>
                            </div>
                        </div>
                        <p v-else class="text-muted-foreground">No rod data yet</p>
                    </CardContent>
                </DashboardCard>

                <!-- Best Rod for Trophies -->
                <DashboardCard
                    v-if="isCardVisible('best_rod_for_trophies')"
                    card-id="best_rod_for_trophies"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('best_rod_for_trophies')"
                    :order="getCardOrder('best_rod_for_trophies')"
                    :size="getCardSize('best_rod_for_trophies')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('best_rod_for_trophies')"
                    :is-last="isCardLast('best_rod_for_trophies')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('best_rod_for_trophies')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-amber-50/30 to-transparent dark:from-amber-950/10"
                >
                    <DashboardCardHeader title="Best Rod for Trophies" subtitle="Rod for biggest catches" emoji="🏆" color="amber" />
                    <CardContent class="pt-0 pb-4">
                        <div v-if="bestRodForTrophies" class="space-y-2">
                            <div class="text-xl font-bold text-amber-700 dark:text-amber-300">{{ bestRodForTrophies.name }}</div>
                            <div class="flex flex-wrap gap-2">
                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">
                                    📏 Biggest: {{ formatSize(bestRodForTrophies.biggest_size) }}"
                                </span>
                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">
                                    📊 Avg: {{ formatSize(bestRodForTrophies.avg_size) }}"
                                </span>
                            </div>
                        </div>
                        <p v-else class="text-muted-foreground">No rod size data yet</p>
                    </CardContent>
                </DashboardCard>

                <!-- Catches by Style -->
                <DashboardCard
                    v-if="isCardVisible('catches_by_style')"
                    card-id="catches_by_style"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('catches_by_style')"
                    :order="getCardOrder('catches_by_style')"
                    :size="getCardSize('catches_by_style')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('catches_by_style')"
                    :is-last="isCardLast('catches_by_style')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('catches_by_style')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-rose-50/30 to-transparent dark:from-rose-950/10"
                >
                    <DashboardCardHeader title="Catches by Style" subtitle="Fly style distribution" emoji="🎨" color="rose" />
                    <CardContent class="pt-0 pb-3">
                        <div v-if="catchesByStyle.length > 0" class="flex items-center gap-4">
                            <!-- SVG Pie Chart -->
                            <div class="relative w-44 h-44 flex-shrink-0">
                                <svg class="w-full h-full" viewBox="0 0 200 200">
                                    <!-- Pie slices -->
                                    <g v-for="(slice, index) in stylePieSlices" :key="`style-slice-${index}`">
                                        <path
                                            :d="slice.path"
                                            :fill="slice.color"
                                            :class="hoveredStyleSlice === index ? 'opacity-100' : 'opacity-80'"
                                            class="cursor-pointer transition-all hover:opacity-100 stroke-rose-200 dark:stroke-rose-800"
                                            stroke-width="1"
                                            @mouseenter="hoveredStyleSlice = index"
                                            @mouseleave="hoveredStyleSlice = null"
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
                                        {{ catchesByStyle.reduce((sum, s) => sum + Number(s.total_caught), 0) }}
                                    </text>
                                    <text
                                        x="100"
                                        y="110"
                                        text-anchor="middle"
                                        class="text-xs text-muted-foreground"
                                        fill="currentColor"
                                    >
                                        Total
                                    </text>
                                </svg>
                            </div>

                            <!-- Legend -->
                            <div class="flex-1 space-y-1 max-h-44 overflow-y-auto">
                                <div
                                    v-for="(styleData, index) in catchesByStyle"
                                    :key="styleData.style"
                                    class="flex items-center justify-between gap-2 p-1.5 rounded hover:bg-muted/50 cursor-pointer transition-colors"
                                    :class="hoveredStyleSlice === index ? 'bg-muted' : ''"
                                    @mouseenter="hoveredStyleSlice = index"
                                    @mouseleave="hoveredStyleSlice = null"
                                >
                                    <div class="flex items-center gap-2 min-w-0">
                                        <div
                                            class="w-2.5 h-2.5 rounded-full flex-shrink-0"
                                            :style="{ backgroundColor: getStyleColor(styleData.style, index) }"
                                        ></div>
                                        <span class="text-xs font-medium truncate">{{ styleData.style }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 flex-shrink-0">
                                        <span class="text-xs font-bold">{{ styleData.total_caught }}</span>
                                        <span class="text-xs text-muted-foreground">
                                            ({{ stylePieSlices[index]?.percentage }}%)
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-muted-foreground">No style data yet</p>
                    </CardContent>
                </DashboardCard>

                <!-- ========== COMBINED ANALYSIS CARDS ========== -->

                <!-- Golden Conditions -->
                <DashboardCard
                    v-if="isCardVisible('golden_conditions')"
                    card-id="golden_conditions"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('golden_conditions')"
                    :order="getCardOrder('golden_conditions')"
                    :size="getCardSize('golden_conditions')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('golden_conditions')"
                    :is-last="isCardLast('golden_conditions')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('golden_conditions')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-yellow-50/30 to-amber-50/30 dark:from-yellow-950/10 dark:to-amber-950/10"
                >
                    <DashboardCardHeader title="Golden Conditions" subtitle="Conditions on your best fishing days" emoji="✨" color="yellow" gradientTo="amber" />
                    <CardContent class="pt-0 pb-3">
                        <div v-if="goldenConditions.moon_position || goldenConditions.time_of_day || goldenConditions.cloud || goldenConditions.clarity" class="grid grid-cols-3 gap-2">
                            <div v-if="goldenConditions.moon_position" class="space-y-0.5">
                                <div class="text-[10px] text-muted-foreground">Moon Position</div>
                                <div class="text-xs font-medium text-amber-700 dark:text-amber-300">🌙 {{ goldenConditions.moon_position }}</div>
                            </div>
                            <div v-if="goldenConditions.moon_phase" class="space-y-0.5">
                                <div class="text-[10px] text-muted-foreground">Moon Phase</div>
                                <div class="text-xs font-medium text-amber-700 dark:text-amber-300">🌕 {{ goldenConditions.moon_phase }}</div>
                            </div>
                            <div v-if="goldenConditions.time_of_day" class="space-y-0.5">
                                <div class="text-[10px] text-muted-foreground">Time of Day</div>
                                <div class="text-xs font-medium text-amber-700 dark:text-amber-300">🕐 {{ goldenConditions.time_of_day }}</div>
                            </div>
                            <div v-if="goldenConditions.season" class="space-y-0.5">
                                <div class="text-[10px] text-muted-foreground">Season</div>
                                <div class="text-xs font-medium text-amber-700 dark:text-amber-300">🍂 {{ goldenConditions.season }}</div>
                            </div>
                            <div v-if="goldenConditions.cloud" class="space-y-0.5">
                                <div class="text-[10px] text-muted-foreground">Cloud Cover</div>
                                <div class="text-xs font-medium text-amber-700 dark:text-amber-300">☁️ {{ goldenConditions.cloud }}</div>
                            </div>
                            <div v-if="goldenConditions.wind" class="space-y-0.5">
                                <div class="text-[10px] text-muted-foreground">Wind</div>
                                <div class="text-xs font-medium text-amber-700 dark:text-amber-300">💨 {{ goldenConditions.wind }}</div>
                            </div>
                            <div v-if="goldenConditions.precipitation" class="space-y-0.5">
                                <div class="text-[10px] text-muted-foreground">Precipitation</div>
                                <div class="text-xs font-medium text-amber-700 dark:text-amber-300">🌧️ {{ goldenConditions.precipitation }}</div>
                            </div>
                            <div v-if="goldenConditions.barometric_pressure" class="space-y-0.5">
                                <div class="text-[10px] text-muted-foreground">Pressure</div>
                                <div class="text-xs font-medium text-amber-700 dark:text-amber-300">📊 {{ goldenConditions.barometric_pressure }}</div>
                            </div>
                            <div v-if="goldenConditions.air_temperature" class="space-y-0.5">
                                <div class="text-[10px] text-muted-foreground">Air Temp</div>
                                <div class="text-xs font-medium text-amber-700 dark:text-amber-300">🌡️ {{ goldenConditions.air_temperature }}</div>
                            </div>
                            <div v-if="goldenConditions.clarity" class="space-y-0.5">
                                <div class="text-[10px] text-muted-foreground">Water Clarity</div>
                                <div class="text-xs font-medium text-amber-700 dark:text-amber-300">💧 {{ goldenConditions.clarity }}</div>
                            </div>
                            <div v-if="goldenConditions.water_level" class="space-y-0.5">
                                <div class="text-[10px] text-muted-foreground">Water Level</div>
                                <div class="text-xs font-medium text-amber-700 dark:text-amber-300">📏 {{ goldenConditions.water_level }}</div>
                            </div>
                            <div v-if="goldenConditions.water_speed" class="space-y-0.5">
                                <div class="text-[10px] text-muted-foreground">Water Speed</div>
                                <div class="text-xs font-medium text-amber-700 dark:text-amber-300">🌊 {{ goldenConditions.water_speed }}</div>
                            </div>
                            <div v-if="goldenConditions.surface_condition" class="space-y-0.5">
                                <div class="text-[10px] text-muted-foreground">Surface</div>
                                <div class="text-xs font-medium text-amber-700 dark:text-amber-300">〰️ {{ goldenConditions.surface_condition }}</div>
                            </div>
                            <div v-if="goldenConditions.tide" class="space-y-0.5">
                                <div class="text-[10px] text-muted-foreground">Tide</div>
                                <div class="text-xs font-medium text-amber-700 dark:text-amber-300">🌊 {{ goldenConditions.tide }}</div>
                            </div>
                            <div v-if="goldenConditions.water_temperature" class="space-y-0.5">
                                <div class="text-[10px] text-muted-foreground">Water Temp</div>
                                <div class="text-xs font-medium text-amber-700 dark:text-amber-300">🌡️ {{ goldenConditions.water_temperature }}</div>
                            </div>
                        </div>
                        <p v-else class="text-muted-foreground">Not enough data yet</p>
                    </CardContent>
                </DashboardCard>

                <!-- Golden Conditions for Big Fish -->
                <DashboardCard
                    v-if="isCardVisible('best_conditions_summary')"
                    card-id="best_conditions_summary"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('best_conditions_summary')"
                    :order="getCardOrder('best_conditions_summary')"
                    :size="getCardSize('best_conditions_summary')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('best_conditions_summary')"
                    :is-last="isCardLast('best_conditions_summary')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('best_conditions_summary')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-orange-50/30 to-red-50/30 dark:from-orange-950/10 dark:to-red-950/10"
                >
                    <DashboardCardHeader title="Trophy Conditions" subtitle="Conditions when you caught your biggest fish" emoji="🏆" color="orange" gradientTo="red" />
                    <CardContent class="pt-0 pb-3">
                        <div v-if="bigFishConditions.moon_position || bigFishConditions.time_of_day || bigFishConditions.cloud || bigFishConditions.clarity" class="grid grid-cols-3 gap-2">
                            <div v-if="bigFishConditions.moon_position" class="space-y-0.5">
                                <div class="text-[10px] text-muted-foreground">Moon Position</div>
                                <div class="text-xs font-medium text-orange-700 dark:text-orange-300">🌙 {{ bigFishConditions.moon_position }}</div>
                            </div>
                            <div v-if="bigFishConditions.moon_phase" class="space-y-0.5">
                                <div class="text-[10px] text-muted-foreground">Moon Phase</div>
                                <div class="text-xs font-medium text-orange-700 dark:text-orange-300">🌕 {{ bigFishConditions.moon_phase }}</div>
                            </div>
                            <div v-if="bigFishConditions.time_of_day" class="space-y-0.5">
                                <div class="text-[10px] text-muted-foreground">Time of Day</div>
                                <div class="text-xs font-medium text-orange-700 dark:text-orange-300">🕐 {{ bigFishConditions.time_of_day }}</div>
                            </div>
                            <div v-if="bigFishConditions.season" class="space-y-0.5">
                                <div class="text-[10px] text-muted-foreground">Season</div>
                                <div class="text-xs font-medium text-orange-700 dark:text-orange-300">🍂 {{ bigFishConditions.season }}</div>
                            </div>
                            <div v-if="bigFishConditions.cloud" class="space-y-0.5">
                                <div class="text-[10px] text-muted-foreground">Cloud Cover</div>
                                <div class="text-xs font-medium text-orange-700 dark:text-orange-300">☁️ {{ bigFishConditions.cloud }}</div>
                            </div>
                            <div v-if="bigFishConditions.wind" class="space-y-0.5">
                                <div class="text-[10px] text-muted-foreground">Wind</div>
                                <div class="text-xs font-medium text-orange-700 dark:text-orange-300">💨 {{ bigFishConditions.wind }}</div>
                            </div>
                            <div v-if="bigFishConditions.precipitation" class="space-y-0.5">
                                <div class="text-[10px] text-muted-foreground">Precipitation</div>
                                <div class="text-xs font-medium text-orange-700 dark:text-orange-300">🌧️ {{ bigFishConditions.precipitation }}</div>
                            </div>
                            <div v-if="bigFishConditions.barometric_pressure" class="space-y-0.5">
                                <div class="text-[10px] text-muted-foreground">Pressure</div>
                                <div class="text-xs font-medium text-orange-700 dark:text-orange-300">📊 {{ bigFishConditions.barometric_pressure }}</div>
                            </div>
                            <div v-if="bigFishConditions.air_temperature" class="space-y-0.5">
                                <div class="text-[10px] text-muted-foreground">Air Temp</div>
                                <div class="text-xs font-medium text-orange-700 dark:text-orange-300">🌡️ {{ bigFishConditions.air_temperature }}</div>
                            </div>
                            <div v-if="bigFishConditions.clarity" class="space-y-0.5">
                                <div class="text-[10px] text-muted-foreground">Water Clarity</div>
                                <div class="text-xs font-medium text-orange-700 dark:text-orange-300">💧 {{ bigFishConditions.clarity }}</div>
                            </div>
                            <div v-if="bigFishConditions.water_level" class="space-y-0.5">
                                <div class="text-[10px] text-muted-foreground">Water Level</div>
                                <div class="text-xs font-medium text-orange-700 dark:text-orange-300">📏 {{ bigFishConditions.water_level }}</div>
                            </div>
                            <div v-if="bigFishConditions.water_speed" class="space-y-0.5">
                                <div class="text-[10px] text-muted-foreground">Water Speed</div>
                                <div class="text-xs font-medium text-orange-700 dark:text-orange-300">🌊 {{ bigFishConditions.water_speed }}</div>
                            </div>
                            <div v-if="bigFishConditions.surface_condition" class="space-y-0.5">
                                <div class="text-[10px] text-muted-foreground">Surface</div>
                                <div class="text-xs font-medium text-orange-700 dark:text-orange-300">〰️ {{ bigFishConditions.surface_condition }}</div>
                            </div>
                            <div v-if="bigFishConditions.tide" class="space-y-0.5">
                                <div class="text-[10px] text-muted-foreground">Tide</div>
                                <div class="text-xs font-medium text-orange-700 dark:text-orange-300">🌊 {{ bigFishConditions.tide }}</div>
                            </div>
                            <div v-if="bigFishConditions.water_temperature" class="space-y-0.5">
                                <div class="text-[10px] text-muted-foreground">Water Temp</div>
                                <div class="text-xs font-medium text-orange-700 dark:text-orange-300">🌡️ {{ bigFishConditions.water_temperature }}</div>
                            </div>
                        </div>
                        <p v-else class="text-muted-foreground">Not enough data yet</p>
                    </CardContent>
                </DashboardCard>

                <!-- ===== TIME & PATTERN ANALYSIS CARDS ===== -->

                <!-- Best Hour of Day -->
                <DashboardCard
                    v-if="isCardVisible('best_hour')"
                    card-id="best_hour"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('best_hour')"
                    :order="getCardOrder('best_hour')"
                    :size="getCardSize('best_hour')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('best_hour')"
                    :is-last="isCardLast('best_hour')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('best_hour')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-amber-50/30 to-orange-50/30 dark:from-amber-950/10 dark:to-orange-950/10"
                >
                    <DashboardCardHeader title="Best Hour of Day" subtitle="Peak fishing hour" emoji="⏰" color="amber" gradientTo="orange" />
                    <CardContent class="pt-0 pb-4">
                        <div v-if="bestHour" class="space-y-2">
                            <div class="text-3xl font-bold text-amber-600 dark:text-amber-400">{{ bestHour.formatted }}</div>
                            <div class="text-sm text-muted-foreground">{{ bestHour.total }} fish caught</div>
                        </div>
                        <p v-else class="text-muted-foreground">Not enough data yet</p>
                    </CardContent>
                </DashboardCard>

                <!-- Seasonal Trends -->
                <DashboardCard
                    v-if="isCardVisible('seasonal_trends')"
                    card-id="seasonal_trends"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('seasonal_trends')"
                    :order="getCardOrder('seasonal_trends')"
                    :size="getCardSize('seasonal_trends')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('seasonal_trends')"
                    :is-last="isCardLast('seasonal_trends')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('seasonal_trends')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-green-50/30 to-emerald-50/30 dark:from-green-950/10 dark:to-emerald-950/10"
                >
                    <DashboardCardHeader title="Seasonal Trends" subtitle="Catches by season" emoji="🍂" color="green" gradientTo="emerald" />
                    <CardContent class="pt-0 pb-4">
                        <div v-if="Object.keys(seasonalTrends).length > 0" class="grid grid-cols-2 gap-3">
                            <div v-for="(data, season) in seasonalTrends" :key="season" class="space-y-1">
                                <div class="text-xs text-muted-foreground">{{ season }}</div>
                                <div class="text-lg font-semibold text-green-600 dark:text-green-400">{{ data.total }}</div>
                                <div class="text-xs text-muted-foreground">{{ data.avg }}/trip avg</div>
                            </div>
                        </div>
                        <p v-else class="text-muted-foreground">Not enough data yet</p>
                    </CardContent>
                </DashboardCard>

                <!-- ===== LOCATION INTELLIGENCE CARDS ===== -->

                <!-- Location Variety -->
                <DashboardCard
                    v-if="isCardVisible('location_variety')"
                    card-id="location_variety"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('location_variety')"
                    :order="getCardOrder('location_variety')"
                    :size="getCardSize('location_variety')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('location_variety')"
                    :is-last="isCardLast('location_variety')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('location_variety')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-blue-50/30 to-indigo-50/30 dark:from-blue-950/10 dark:to-indigo-950/10"
                >
                    <DashboardCardHeader title="Location Variety" subtitle="Different spots fished" :icon="MapPin" color="blue" gradientTo="indigo" />
                    <CardContent class="pt-0 pb-4">
                        <div class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ locationVariety }}</div>
                        <div class="text-sm text-muted-foreground">unique locations</div>
                    </CardContent>
                </DashboardCard>

                <!-- Most Consistent Spot -->
                <DashboardCard
                    v-if="isCardVisible('most_consistent_spot')"
                    card-id="most_consistent_spot"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('most_consistent_spot')"
                    :order="getCardOrder('most_consistent_spot')"
                    :size="getCardSize('most_consistent_spot')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('most_consistent_spot')"
                    :is-last="isCardLast('most_consistent_spot')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('most_consistent_spot')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-emerald-50/30 to-green-50/30 dark:from-emerald-950/10 dark:to-green-950/10"
                >
                    <DashboardCardHeader title="Most Consistent Spot" subtitle="Highest success rate" :icon="Target" color="emerald" gradientTo="green" />
                    <CardContent class="pt-0 pb-4">
                        <div v-if="mostConsistentSpot" class="space-y-2">
                            <div class="text-lg font-semibold text-emerald-600 dark:text-emerald-400">{{ mostConsistentSpot.name }}</div>
                            <div class="text-2xl font-bold">{{ mostConsistentSpot.success_rate }}%</div>
                            <div class="text-sm text-muted-foreground">success rate ({{ mostConsistentSpot.days }} days)</div>
                        </div>
                        <p v-else class="text-muted-foreground">Not enough data yet</p>
                    </CardContent>
                </DashboardCard>

                <!-- Underexplored Spots -->
                <DashboardCard
                    v-if="isCardVisible('underexplored_spots')"
                    card-id="underexplored_spots"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('underexplored_spots')"
                    :order="getCardOrder('underexplored_spots')"
                    :size="getCardSize('underexplored_spots')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('underexplored_spots')"
                    :is-last="isCardLast('underexplored_spots')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('underexplored_spots')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-amber-50/30 to-yellow-50/30 dark:from-amber-950/10 dark:to-yellow-950/10"
                >
                    <DashboardCardHeader title="Underexplored Spots" subtitle="Visited only 1-3 times" emoji="🗺️" color="amber" gradientTo="yellow" />
                    <CardContent class="pt-0 pb-4">
                        <div v-if="underexploredSpots > 0">
                            <div class="text-3xl font-bold text-amber-600 dark:text-amber-400">{{ underexploredSpots }}</div>
                            <div class="text-sm text-muted-foreground">spots to revisit</div>
                        </div>
                        <p v-else class="text-muted-foreground">No underexplored spots</p>
                    </CardContent>
                </DashboardCard>

                <!-- Best Location by Season -->
                <DashboardCard
                    v-if="isCardVisible('best_location_by_season')"
                    card-id="best_location_by_season"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('best_location_by_season')"
                    :order="getCardOrder('best_location_by_season')"
                    :size="getCardSize('best_location_by_season')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('best_location_by_season')"
                    :is-last="isCardLast('best_location_by_season')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('best_location_by_season')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-rose-50/30 to-pink-50/30 dark:from-rose-950/10 dark:to-pink-950/10"
                >
                    <DashboardCardHeader title="Best Location by Season" subtitle="Top spot each season" emoji="📍" color="rose" gradientTo="pink" />
                    <CardContent class="pt-0 pb-4">
                        <div v-if="Object.keys(bestLocationBySeason).length > 0" class="grid grid-cols-2 gap-3">
                            <div v-for="(data, season) in bestLocationBySeason" :key="season" class="space-y-1">
                                <div class="text-xs text-muted-foreground">{{ season }}</div>
                                <div class="text-sm font-semibold text-rose-600 dark:text-rose-400 truncate">{{ data.name }}</div>
                                <div class="text-xs text-muted-foreground">{{ data.total }} fish</div>
                            </div>
                        </div>
                        <p v-else class="text-muted-foreground">Not enough data yet</p>
                    </CardContent>
                </DashboardCard>

                <!-- New Spot Success Rate -->
                <DashboardCard
                    v-if="isCardVisible('new_spot_success_rate')"
                    card-id="new_spot_success_rate"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('new_spot_success_rate')"
                    :order="getCardOrder('new_spot_success_rate')"
                    :size="getCardSize('new_spot_success_rate')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('new_spot_success_rate')"
                    :is-last="isCardLast('new_spot_success_rate')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('new_spot_success_rate')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-cyan-50/30 to-teal-50/30 dark:from-cyan-950/10 dark:to-teal-950/10"
                >
                    <DashboardCardHeader title="New Spot Success" subtitle="First visit success rate" emoji="🆕" color="cyan" gradientTo="teal" />
                    <CardContent class="pt-0 pb-4">
                        <div v-if="newSpotSuccessRate !== null" class="space-y-2">
                            <div class="text-3xl font-bold text-cyan-600 dark:text-cyan-400">{{ newSpotSuccessRate }}%</div>
                            <div class="text-sm text-muted-foreground">of new spots produced fish</div>
                        </div>
                        <p v-else class="text-muted-foreground">Not enough data yet</p>
                    </CardContent>
                </DashboardCard>

                <!-- ===== SPECIES DEEP DIVE CARDS ===== -->

                <!-- Rarest Catches -->
                <DashboardCard
                    v-if="isCardVisible('rarest_catches')"
                    card-id="rarest_catches"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('rarest_catches')"
                    :order="getCardOrder('rarest_catches')"
                    :size="getCardSize('rarest_catches')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('rarest_catches')"
                    :is-last="isCardLast('rarest_catches')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('rarest_catches')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-amber-50/30 to-orange-50/30 dark:from-amber-950/10 dark:to-orange-950/10"
                >
                    <DashboardCardHeader title="Rarest Catches" subtitle="Least common species" emoji="💎" color="amber" gradientTo="orange" />
                    <CardContent class="pt-0 pb-4">
                        <div v-if="rarestCatches.length > 0" class="space-y-2">
                            <div v-for="catch_ in rarestCatches" :key="catch_.name" class="flex justify-between items-center">
                                <span class="text-sm truncate">{{ catch_.name }}</span>
                                <span class="text-xs text-muted-foreground">{{ catch_.count }}x</span>
                            </div>
                        </div>
                        <p v-else class="text-muted-foreground">No rare catches yet</p>
                    </CardContent>
                </DashboardCard>

                <!-- Size Improvement -->
                <DashboardCard
                    v-if="isCardVisible('size_improvement')"
                    card-id="size_improvement"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('size_improvement')"
                    :order="getCardOrder('size_improvement')"
                    :size="getCardSize('size_improvement')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('size_improvement')"
                    :is-last="isCardLast('size_improvement')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('size_improvement')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-indigo-50/30 to-blue-50/30 dark:from-indigo-950/10 dark:to-blue-950/10"
                >
                    <DashboardCardHeader title="Size Improvement" :icon="TrendingUp" color="indigo" gradientTo="blue">
                        <template #subtitle>{{ sizeImprovement.previousYear }} vs {{ sizeImprovement.currentYear }}</template>
                    </DashboardCardHeader>
                    <CardContent class="pt-0 pb-4">
                        <div v-if="sizeImprovement.items.length > 0" class="space-y-2">
                            <div v-for="item in sizeImprovement.items" :key="item.name" class="flex justify-between items-center">
                                <span class="text-sm truncate">{{ item.name }}</span>
                                <span
                                    class="text-sm font-semibold"
                                    :class="item.improvement >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'"
                                >
                                    {{ item.improvement >= 0 ? '+' : '' }}{{ item.improvement }}%
                                </span>
                            </div>
                        </div>
                        <p v-else class="text-muted-foreground">Not enough data yet</p>
                    </CardContent>
                </DashboardCard>

                <!-- ===== FLY/LURE PATTERN CARDS ===== -->

                <!-- Fly Rotation -->
                <DashboardCard
                    v-if="isCardVisible('fly_rotation')"
                    card-id="fly_rotation"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('fly_rotation')"
                    :order="getCardOrder('fly_rotation')"
                    :size="getCardSize('fly_rotation')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('fly_rotation')"
                    :is-last="isCardLast('fly_rotation')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('fly_rotation')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-pink-50/30 to-rose-50/30 dark:from-pink-950/10 dark:to-rose-950/10"
                >
                    <DashboardCardHeader title="Fly Rotation" subtitle="Different flies used" emoji="🪰" color="pink" gradientTo="rose" />
                    <CardContent class="pt-0 pb-4">
                        <div class="text-3xl font-bold text-pink-600 dark:text-pink-400">{{ flyRotation }}</div>
                        <div class="text-sm text-muted-foreground">unique flies</div>
                    </CardContent>
                </DashboardCard>

                <!-- One-Hit Wonders -->
                <DashboardCard
                    v-if="isCardVisible('one_hit_wonders')"
                    card-id="one_hit_wonders"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('one_hit_wonders')"
                    :order="getCardOrder('one_hit_wonders')"
                    :size="getCardSize('one_hit_wonders')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('one_hit_wonders')"
                    :is-last="isCardLast('one_hit_wonders')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('one_hit_wonders')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-yellow-50/30 to-amber-50/30 dark:from-yellow-950/10 dark:to-amber-950/10"
                >
                    <DashboardCardHeader title="One-Hit Wonders" subtitle="Flies that worked great once" emoji="⭐" color="yellow" gradientTo="amber" />
                    <CardContent class="pt-0 pb-4">
                        <div v-if="oneHitWonders.length > 0" class="space-y-2">
                            <div v-for="fly in oneHitWonders" :key="fly.name" class="flex justify-between items-center">
                                <span class="text-sm truncate">{{ fly.name }}</span>
                                <span class="text-xs text-muted-foreground">{{ fly.caught }} fish</span>
                            </div>
                        </div>
                        <p v-else class="text-muted-foreground">No one-hit wonders yet</p>
                    </CardContent>
                </DashboardCard>

                <!-- Best Fly by Location -->
                <DashboardCard
                    v-if="isCardVisible('best_fly_by_location')"
                    card-id="best_fly_by_location"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('best_fly_by_location')"
                    :order="getCardOrder('best_fly_by_location')"
                    :size="getCardSize('best_fly_by_location')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('best_fly_by_location')"
                    :is-last="isCardLast('best_fly_by_location')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('best_fly_by_location')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-blue-50/30 to-cyan-50/30 dark:from-blue-950/10 dark:to-cyan-950/10"
                >
                    <DashboardCardHeader title="Best Fly by Location" subtitle="Top fly for each spot" :icon="MapPin" color="blue" gradientTo="cyan" />
                    <CardContent class="pt-0 pb-4">
                        <div v-if="bestFlyByLocation.length > 0" class="space-y-2">
                            <div v-for="item in bestFlyByLocation.slice(0, 5)" :key="item.location" class="flex justify-between items-center">
                                <div class="truncate">
                                    <span class="text-sm">{{ item.location }}</span>
                                    <span class="text-xs text-muted-foreground ml-1">→ {{ item.fly }}</span>
                                </div>
                                <span class="text-xs text-muted-foreground">{{ item.total }}</span>
                            </div>
                        </div>
                        <p v-else class="text-muted-foreground">Not enough data yet</p>
                    </CardContent>
                </DashboardCard>

                <!-- Best Fly by Species -->
                <DashboardCard
                    v-if="isCardVisible('best_fly_by_species')"
                    card-id="best_fly_by_species"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('best_fly_by_species')"
                    :order="getCardOrder('best_fly_by_species')"
                    :size="getCardSize('best_fly_by_species')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('best_fly_by_species')"
                    :is-last="isCardLast('best_fly_by_species')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('best_fly_by_species')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-violet-50/30 to-purple-50/30 dark:from-violet-950/10 dark:to-purple-950/10"
                >
                    <DashboardCardHeader title="Best Fly by Species" subtitle="Top fly for each species" emoji="🐡" color="violet" gradientTo="purple" />
                    <CardContent class="pt-0 pb-4">
                        <div v-if="bestFlyBySpecies.length > 0" class="space-y-2">
                            <div v-for="item in bestFlyBySpecies.slice(0, 5)" :key="item.species" class="flex justify-between items-center">
                                <div class="truncate">
                                    <span class="text-sm">{{ item.species }}</span>
                                    <span class="text-xs text-muted-foreground ml-1">→ {{ item.fly }}</span>
                                </div>
                                <span class="text-xs text-muted-foreground">{{ item.total }}</span>
                            </div>
                        </div>
                        <p v-else class="text-muted-foreground">Not enough data yet</p>
                    </CardContent>
                </DashboardCard>

                <!-- ===== PROGRESS & GOALS CARDS ===== -->

                <!-- Year-over-Year Comparison -->
                <DashboardCard
                    v-if="isCardVisible('yoy_comparison')"
                    card-id="yoy_comparison"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('yoy_comparison')"
                    :order="getCardOrder('yoy_comparison')"
                    :size="getCardSize('yoy_comparison')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('yoy_comparison')"
                    :is-last="isCardLast('yoy_comparison')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('yoy_comparison')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-indigo-50/30 to-blue-50/30 dark:from-indigo-950/10 dark:to-blue-950/10"
                >
                    <DashboardCardHeader title="Year-over-Year" :icon="BarChart3" color="indigo" gradientTo="blue">
                        <template #subtitle>{{ yoyComparison.previousYear }} vs {{ yoyComparison.currentYear }}</template>
                    </DashboardCardHeader>
                    <CardContent class="pt-0 pb-4">
                        <div class="grid grid-cols-3 gap-3 mb-3">
                            <div class="space-y-1">
                                <div class="text-xs text-muted-foreground">Catches</div>
                                <div class="text-lg font-semibold">{{ yoyComparison.thisYear.catches }}</div>
                                <div class="text-xs text-muted-foreground">vs {{ yoyComparison.lastYearToDate.catches }}</div>
                            </div>
                            <div class="space-y-1">
                                <div class="text-xs text-muted-foreground">Days</div>
                                <div class="text-lg font-semibold">{{ yoyComparison.thisYear.days }}</div>
                                <div class="text-xs text-muted-foreground">vs {{ yoyComparison.lastYearToDate.days }}</div>
                            </div>
                            <div class="space-y-1">
                                <div class="text-xs text-muted-foreground">Change</div>
                                <div v-if="yoyComparison.catchChange !== null" :class="['text-lg font-semibold', yoyComparison.catchChange >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400']">
                                    {{ yoyComparison.catchChange >= 0 ? '+' : '' }}{{ yoyComparison.catchChange }}%
                                </div>
                                <div v-else class="text-lg font-semibold text-muted-foreground">—</div>
                            </div>
                        </div>
                        <div v-if="!yoyComparison.isFullYearComparison" class="border-t pt-2">
                            <div class="text-xs text-muted-foreground mb-1">{{ yoyComparison.lastYearFull.year }} Full Year</div>
                            <div class="flex gap-4 text-sm">
                                <span><span class="font-medium">{{ yoyComparison.lastYearFull.catches }}</span> catches</span>
                                <span><span class="font-medium">{{ yoyComparison.lastYearFull.days }}</span> days</span>
                            </div>
                        </div>
                    </CardContent>
                </DashboardCard>

                <!-- Improvement Rate -->
                <DashboardCard
                    v-if="isCardVisible('improvement_rate')"
                    card-id="improvement_rate"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('improvement_rate')"
                    :order="getCardOrder('improvement_rate')"
                    :size="getCardSize('improvement_rate')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('improvement_rate')"
                    :is-last="isCardLast('improvement_rate')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('improvement_rate')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-green-50/30 to-emerald-50/30 dark:from-green-950/10 dark:to-emerald-950/10"
                >
                    <DashboardCardHeader title="Improvement Rate" subtitle="Catches per trip trend" :icon="TrendingUp" color="green" gradientTo="emerald" />
                    <CardContent class="pt-0 pb-4">
                        <div v-if="improvementRate && improvementRate.monthlyData.length > 0" class="space-y-3">
                            <!-- Bar chart -->
                            <div class="flex items-end gap-1 h-28">
                                <div
                                    v-for="item in improvementRate.monthlyData"
                                    :key="item.monthNum"
                                    class="flex-1 flex flex-col items-center h-full min-w-0"
                                >
                                    <div class="text-[10px] font-medium text-green-600 dark:text-green-400 mb-1 truncate">
                                        {{ item.avg }}
                                    </div>
                                    <div class="flex-1 w-full flex items-end">
                                        <div
                                            class="w-full bg-gradient-to-t from-green-500 to-emerald-400 dark:from-green-600 dark:to-emerald-500 rounded-t transition-all"
                                            :style="{ height: improvementRate.maxAvg > 0 ? `${Math.max((item.avg / improvementRate.maxAvg) * 100, item.avg > 0 ? 10 : 0)}%` : '0%' }"
                                        ></div>
                                    </div>
                                    <div class="text-xs text-muted-foreground mt-1">{{ item.month }}</div>
                                </div>
                            </div>
                            <!-- Summary -->
                            <div class="flex items-center justify-between pt-2 border-t">
                                <div class="text-sm text-muted-foreground">Overall trend</div>
                                <div v-if="improvementRate.percent !== null" :class="['text-lg font-bold', improvementRate.percent >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400']">
                                    {{ improvementRate.percent >= 0 ? '+' : '' }}{{ improvementRate.percent }}%
                                </div>
                                <div v-else class="text-sm text-muted-foreground">—</div>
                            </div>
                        </div>
                        <p v-else class="text-muted-foreground">Not enough data yet</p>
                    </CardContent>
                </DashboardCard>

                <!-- Average Size Trend -->
                <DashboardCard
                    v-if="isCardVisible('avg_size_trend')"
                    card-id="avg_size_trend"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('avg_size_trend')"
                    :order="getCardOrder('avg_size_trend')"
                    :size="getCardSize('avg_size_trend')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('avg_size_trend')"
                    :is-last="isCardLast('avg_size_trend')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('avg_size_trend')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-purple-50/30 to-violet-50/30 dark:from-purple-950/10 dark:to-violet-950/10"
                >
                    <DashboardCardHeader title="Avg Size Trend" subtitle="Average fish length by month (inches)" :icon="Ruler" color="purple" gradientTo="violet" />
                    <CardContent class="pt-0 pb-4">
                        <div v-if="avgSizeTrend && avgSizeTrend.monthlyData.length > 0" class="space-y-3">
                            <!-- Bar chart -->
                            <div class="flex items-end gap-1 h-28">
                                <div
                                    v-for="item in avgSizeTrend.monthlyData"
                                    :key="item.monthNum"
                                    class="flex-1 flex flex-col items-center h-full min-w-0"
                                >
                                    <div class="text-[10px] font-medium text-purple-600 dark:text-purple-400 mb-1 truncate">
                                        {{ item.avg }}
                                    </div>
                                    <div class="flex-1 w-full flex items-end">
                                        <div
                                            class="w-full bg-gradient-to-t from-purple-500 to-violet-400 dark:from-purple-600 dark:to-violet-500 rounded-t transition-all"
                                            :style="{ height: avgSizeTrend.maxAvg > 0 ? `${Math.max((item.avg / avgSizeTrend.maxAvg) * 100, item.avg > 0 ? 10 : 0)}%` : '0%' }"
                                        ></div>
                                    </div>
                                    <div class="text-xs text-muted-foreground mt-1">{{ item.month }}</div>
                                </div>
                            </div>
                            <!-- Summary -->
                            <div class="flex items-center justify-between pt-2 border-t">
                                <div class="text-sm text-muted-foreground">Overall trend</div>
                                <div v-if="avgSizeTrend.percent !== null" :class="['text-lg font-bold', avgSizeTrend.percent >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400']">
                                    {{ avgSizeTrend.percent >= 0 ? '+' : '' }}{{ avgSizeTrend.percent }}%
                                </div>
                                <div v-else class="text-sm text-muted-foreground">—</div>
                            </div>
                        </div>
                        <p v-else class="text-muted-foreground">Not enough data yet</p>
                    </CardContent>
                </DashboardCard>

                <!-- Average Weight Trend -->
                <DashboardCard
                    v-if="isCardVisible('avg_weight_trend')"
                    card-id="avg_weight_trend"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('avg_weight_trend')"
                    :order="getCardOrder('avg_weight_trend')"
                    :size="getCardSize('avg_weight_trend')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('avg_weight_trend')"
                    :is-last="isCardLast('avg_weight_trend')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('avg_weight_trend')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-amber-50/30 to-orange-50/30 dark:from-amber-950/10 dark:to-orange-950/10"
                >
                    <DashboardCardHeader title="Avg Weight Trend" subtitle="Average fish weight by month (lbs)" :icon="Scale" color="amber" gradientTo="orange" />
                    <CardContent class="pt-0 pb-4">
                        <div v-if="avgWeightTrend && avgWeightTrend.monthlyData.length > 0" class="space-y-3">
                            <!-- Bar chart -->
                            <div class="flex items-end gap-1 h-28">
                                <div
                                    v-for="item in avgWeightTrend.monthlyData"
                                    :key="item.monthNum"
                                    class="flex-1 flex flex-col items-center h-full min-w-0"
                                >
                                    <div class="text-[10px] font-medium text-amber-600 dark:text-amber-400 mb-1 truncate">
                                        {{ item.avg }}
                                    </div>
                                    <div class="flex-1 w-full flex items-end">
                                        <div
                                            class="w-full bg-gradient-to-t from-amber-500 to-orange-400 dark:from-amber-600 dark:to-orange-500 rounded-t transition-all"
                                            :style="{ height: avgWeightTrend.maxAvg > 0 ? `${Math.max((item.avg / avgWeightTrend.maxAvg) * 100, item.avg > 0 ? 10 : 0)}%` : '0%' }"
                                        ></div>
                                    </div>
                                    <div class="text-xs text-muted-foreground mt-1">{{ item.month }}</div>
                                </div>
                            </div>
                            <!-- Summary -->
                            <div class="flex items-center justify-between pt-2 border-t">
                                <div class="text-sm text-muted-foreground">Overall trend</div>
                                <div v-if="avgWeightTrend.percent !== null" :class="['text-lg font-bold', avgWeightTrend.percent >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400']">
                                    {{ avgWeightTrend.percent >= 0 ? '+' : '' }}{{ avgWeightTrend.percent }}%
                                </div>
                                <div v-else class="text-sm text-muted-foreground">—</div>
                            </div>
                        </div>
                        <p v-else class="text-muted-foreground">Not enough data yet</p>
                    </CardContent>
                </DashboardCard>

                <!-- Fishing Frequency -->
                <DashboardCard
                    v-if="isCardVisible('fishing_frequency')"
                    card-id="fishing_frequency"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('fishing_frequency')"
                    :order="getCardOrder('fishing_frequency')"
                    :size="getCardSize('fishing_frequency')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('fishing_frequency')"
                    :is-last="isCardLast('fishing_frequency')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('fishing_frequency')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-sky-50/30 to-blue-50/30 dark:from-sky-950/10 dark:to-blue-950/10"
                >
                    <DashboardCardHeader title="Fishing Frequency" subtitle="Days fished per month" :icon="Calendar" color="sky" gradientTo="blue" />
                    <CardContent class="pt-4 pb-4">
                        <div v-if="Object.keys(fishingFrequency).length > 0" class="grid grid-cols-6 gap-2">
                            <div v-for="(days, month) in fishingFrequency" :key="month" class="text-center px-2 py-1 bg-sky-100/50 dark:bg-sky-900/20 rounded">
                                <div class="text-xs text-muted-foreground">{{ month }}</div>
                                <div class="text-sm font-semibold text-sky-600 dark:text-sky-400">{{ days }}</div>
                            </div>
                        </div>
                        <p v-else class="text-muted-foreground">Not enough data yet</p>
                    </CardContent>
                </DashboardCard>

                <!-- ===== ENVIRONMENTAL COMBO CARDS ===== -->

                <!-- Wind + Cloud Combo -->
                <DashboardCard
                    v-if="isCardVisible('wind_cloud_combo')"
                    card-id="wind_cloud_combo"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('wind_cloud_combo')"
                    :order="getCardOrder('wind_cloud_combo')"
                    :size="getCardSize('wind_cloud_combo')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('wind_cloud_combo')"
                    :is-last="isCardLast('wind_cloud_combo')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('wind_cloud_combo')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-slate-50/30 to-gray-50/30 dark:from-slate-950/10 dark:to-gray-950/10"
                >
                    <DashboardCardHeader title="Wind + Cloud Combo" subtitle="Best weather combination" emoji="💨☁️" color="slate" gradientTo="gray" />
                    <CardContent class="pt-0 pb-4">
                        <div v-if="windCloudCombo" class="space-y-2">
                            <div class="text-lg font-semibold">{{ windCloudCombo.wind }} + {{ windCloudCombo.cloud }}</div>
                            <div class="text-2xl font-bold text-slate-600 dark:text-slate-400">{{ windCloudCombo.total }} fish</div>
                        </div>
                        <p v-else class="text-muted-foreground">Not enough data yet</p>
                    </CardContent>
                </DashboardCard>

                <!-- Moon + Time Combo -->
                <DashboardCard
                    v-if="isCardVisible('moon_time_combo')"
                    card-id="moon_time_combo"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('moon_time_combo')"
                    :order="getCardOrder('moon_time_combo')"
                    :size="getCardSize('moon_time_combo')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('moon_time_combo')"
                    :is-last="isCardLast('moon_time_combo')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('moon_time_combo')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-indigo-50/30 to-violet-50/30 dark:from-indigo-950/10 dark:to-violet-950/10"
                >
                    <DashboardCardHeader title="Moon + Time Combo" subtitle="Best solunar timing" :icon="Moon" color="indigo" gradientTo="violet" />
                    <CardContent class="pt-0 pb-4">
                        <div v-if="moonTimeCombo" class="space-y-2">
                            <div class="text-lg font-semibold">{{ moonTimeCombo.moon }} + {{ moonTimeCombo.time }}</div>
                            <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ moonTimeCombo.total }} fish</div>
                        </div>
                        <p v-else class="text-muted-foreground">Not enough data yet</p>
                    </CardContent>
                </DashboardCard>

                <!-- Water + Weather Combo -->
                <DashboardCard
                    v-if="isCardVisible('water_weather_combo')"
                    card-id="water_weather_combo"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('water_weather_combo')"
                    :order="getCardOrder('water_weather_combo')"
                    :size="getCardSize('water_weather_combo')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('water_weather_combo')"
                    :is-last="isCardLast('water_weather_combo')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('water_weather_combo')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-cyan-50/30 to-teal-50/30 dark:from-cyan-950/10 dark:to-teal-950/10"
                >
                    <DashboardCardHeader title="Water + Weather Combo" subtitle="Best conditions combo" emoji="💧☀️" color="cyan" gradientTo="teal" />
                    <CardContent class="pt-0 pb-4">
                        <div v-if="waterWeatherCombo" class="space-y-2">
                            <div class="text-lg font-semibold">{{ waterWeatherCombo.clarity }} + {{ waterWeatherCombo.cloud }}</div>
                            <div class="text-2xl font-bold text-cyan-600 dark:text-cyan-400">{{ waterWeatherCombo.total }} fish</div>
                        </div>
                        <p v-else class="text-muted-foreground">Not enough data yet</p>
                    </CardContent>
                </DashboardCard>

                <!-- ===== GAMIFICATION CARDS ===== -->

                <!-- Achievement Badges -->
                <DashboardCard
                    v-if="isCardVisible('badges')"
                    card-id="badges"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('badges')"
                    :order="getCardOrder('badges')"
                    :size="getCardSize('badges')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('badges')"
                    :is-last="isCardLast('badges')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('badges')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-purple-50/30 to-pink-50/30 dark:from-purple-950/10 dark:to-pink-950/10"
                >
                    <DashboardCardHeader title="Achievement Badges" subtitle="Your earned achievements" :icon="Award" color="purple" gradientTo="pink" />
                    <CardContent class="pt-0 pb-2">
                        <div v-if="badges.length > 0" class="flex flex-wrap gap-1">
                            <div v-for="badge in badges" :key="badge.name" class="flex items-center gap-0.5 px-1.5 py-0.5 bg-purple-100/50 dark:bg-purple-900/20 rounded-full" :title="badge.description">
                                <span class="text-sm">{{ badge.icon }}</span>
                                <span class="text-xs font-medium">{{ badge.name }}</span>
                            </div>
                        </div>
                        <p v-else class="text-muted-foreground">Keep fishing to unlock badges!</p>
                    </CardContent>
                </DashboardCard>

                <!-- Hot Streak -->
                <DashboardCard
                    v-if="isCardVisible('hot_streak')"
                    card-id="hot_streak"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('hot_streak')"
                    :order="getCardOrder('hot_streak')"
                    :size="getCardSize('hot_streak')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('hot_streak')"
                    :is-last="isCardLast('hot_streak')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('hot_streak')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-red-50/30 to-orange-50/30 dark:from-red-950/10 dark:to-orange-950/10"
                >
                    <DashboardCardHeader title="Hot Streak" subtitle="Current success streak" :icon="Flame" color="red" gradientTo="orange" />
                    <CardContent class="pt-0 pb-4">
                        <div v-if="hotStreak" class="space-y-2">
                            <div class="text-3xl font-bold text-red-600 dark:text-red-400">🔥 {{ hotStreak }} days</div>
                            <div class="text-sm text-muted-foreground">You're on fire!</div>
                        </div>
                        <p v-else class="text-muted-foreground">Start a streak today!</p>
                    </CardContent>
                </DashboardCard>

                <!-- Lucky Number -->
                <DashboardCard
                    v-if="isCardVisible('lucky_number')"
                    card-id="lucky_number"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('lucky_number')"
                    :order="getCardOrder('lucky_number')"
                    :size="getCardSize('lucky_number')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('lucky_number')"
                    :is-last="isCardLast('lucky_number')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('lucky_number')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-green-50/30 to-emerald-50/30 dark:from-green-950/10 dark:to-emerald-950/10"
                >
                    <DashboardCardHeader title="Lucky Number" subtitle="Most common catch count" emoji="🍀" color="green" gradientTo="emerald" />
                    <CardContent class="pt-0 pb-4">
                        <div v-if="luckyNumber" class="space-y-2">
                            <div class="text-3xl font-bold text-green-600 dark:text-green-400">{{ luckyNumber.number }}</div>
                            <div class="text-sm text-muted-foreground">caught {{ luckyNumber.occurrences }} times</div>
                        </div>
                        <p v-else class="text-muted-foreground">Not enough data yet</p>
                    </CardContent>
                </DashboardCard>

                <!-- Temperature Sweet Spot -->
                <DashboardCard
                    v-if="isCardVisible('temp_sweet_spot')"
                    card-id="temp_sweet_spot"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('temp_sweet_spot')"
                    :order="getCardOrder('temp_sweet_spot')"
                    :size="getCardSize('temp_sweet_spot')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('temp_sweet_spot')"
                    :is-last="isCardLast('temp_sweet_spot')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('temp_sweet_spot')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-amber-50/30 to-orange-50/30 dark:from-amber-950/10 dark:to-orange-950/10"
                >
                    <DashboardCardHeader title="Temperature Sweet Spot" subtitle="Best air + water combo" :icon="Target" color="amber" gradientTo="orange" />
                    <CardContent class="pt-0 pb-4">
                        <div v-if="tempSweetSpot" class="space-y-2">
                            <div class="flex items-center gap-2">
                                <Thermometer class="h-4 w-4 text-orange-500" />
                                <span class="font-semibold">{{ tempSweetSpot.air_temp }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <Droplets class="h-4 w-4 text-blue-500" />
                                <span class="font-semibold">{{ tempSweetSpot.water_temp }}</span>
                            </div>
                            <div class="text-sm text-muted-foreground">{{ tempSweetSpot.total }} fish caught</div>
                        </div>
                        <p v-else class="text-muted-foreground">Need both temp readings</p>
                    </CardContent>
                </DashboardCard>

                <!-- Big Fish Temperature -->
                <DashboardCard
                    v-if="isCardVisible('big_fish_air_temp')"
                    card-id="big_fish_air_temp"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('big_fish_air_temp')"
                    :order="getCardOrder('big_fish_air_temp')"
                    :size="getCardSize('big_fish_air_temp')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('big_fish_air_temp')"
                    :is-last="isCardLast('big_fish_air_temp')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('big_fish_air_temp')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-red-50/30 to-orange-50/30 dark:from-red-950/10 dark:to-orange-950/10"
                >
                    <DashboardCardHeader title="Big Fish Temperature" subtitle="Air temp for trophy fish" :icon="Crown" color="red" gradientTo="orange" />
                    <CardContent class="pt-0 pb-4">
                        <div v-if="bigFishAirTemp" class="space-y-2">
                            <div class="text-3xl font-bold text-red-600 dark:text-red-400">{{ bigFishAirTemp.temperature }}</div>
                            <div class="text-sm text-muted-foreground">Biggest: {{ bigFishAirTemp.biggest_size }}" | Avg: {{ bigFishAirTemp.avg_size }}"</div>
                        </div>
                        <p v-else class="text-muted-foreground">No size data yet</p>
                    </CardContent>
                </DashboardCard>

                <!-- Catches by Air Temp -->
                <DashboardCard
                    v-if="isCardVisible('catches_by_air_temp')"
                    card-id="catches_by_air_temp"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('catches_by_air_temp')"
                    :order="getCardOrder('catches_by_air_temp')"
                    :size="getCardSize('catches_by_air_temp')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('catches_by_air_temp')"
                    :is-last="isCardLast('catches_by_air_temp')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('catches_by_air_temp')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-orange-50/30 to-amber-50/30 dark:from-orange-950/10 dark:to-amber-950/10"
                >
                    <DashboardCardHeader title="Catches by Air Temp" subtitle="Distribution by air temperature" :icon="BarChart3" color="orange" gradientTo="amber" />
                    <CardContent class="pt-0 pb-4">
                        <div v-if="catchesByAirTemp.length > 0" class="space-y-2">
                            <div v-for="item in catchesByAirTemp.slice(0, 5)" :key="item.temperature" class="flex items-center justify-between">
                                <span class="text-sm">{{ item.temperature }}</span>
                                <div class="flex items-center gap-2">
                                    <div class="h-2 bg-orange-500 rounded" :style="{ width: `${Math.min(100, (item.total_caught / Math.max(...catchesByAirTemp.map(i => i.total_caught))) * 80)}px` }"></div>
                                    <span class="text-sm font-medium w-8 text-right">{{ item.total_caught }}</span>
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-muted-foreground">No temperature data yet</p>
                    </CardContent>
                </DashboardCard>

                <!-- Catches by Water Temp -->
                <DashboardCard
                    v-if="isCardVisible('catches_by_water_temp')"
                    card-id="catches_by_water_temp"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('catches_by_water_temp')"
                    :order="getCardOrder('catches_by_water_temp')"
                    :size="getCardSize('catches_by_water_temp')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('catches_by_water_temp')"
                    :is-last="isCardLast('catches_by_water_temp')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('catches_by_water_temp')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-blue-50/30 to-cyan-50/30 dark:from-blue-950/10 dark:to-cyan-950/10"
                >
                    <DashboardCardHeader title="Catches by Water Temp" subtitle="Distribution by water temperature" :icon="BarChart3" color="blue" gradientTo="cyan" />
                    <CardContent class="pt-0 pb-4">
                        <div v-if="catchesByWaterTemp.length > 0" class="space-y-2">
                            <div v-for="item in catchesByWaterTemp.slice(0, 5)" :key="item.temperature" class="flex items-center justify-between">
                                <span class="text-sm">{{ item.temperature }}</span>
                                <div class="flex items-center gap-2">
                                    <div class="h-2 bg-blue-500 rounded" :style="{ width: `${Math.min(100, (item.total_caught / Math.max(...catchesByWaterTemp.map(i => i.total_caught))) * 80)}px` }"></div>
                                    <span class="text-sm font-medium w-8 text-right">{{ item.total_caught }}</span>
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-muted-foreground">No water temp data yet</p>
                    </CardContent>
                </DashboardCard>

                <!-- Best Fly Size -->
                <DashboardCard
                    v-if="isCardVisible('best_fly_size')"
                    card-id="best_fly_size"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('best_fly_size')"
                    :order="getCardOrder('best_fly_size')"
                    :size="getCardSize('best_fly_size')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('best_fly_size')"
                    :is-last="isCardLast('best_fly_size')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('best_fly_size')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-teal-50/30 to-emerald-50/30 dark:from-teal-950/10 dark:to-emerald-950/10"
                >
                    <DashboardCardHeader title="Best Fly Size" subtitle="Most productive fly size" :icon="Target" color="teal" gradientTo="emerald" />
                    <CardContent class="pt-0 pb-4">
                        <div v-if="bestFlySize" class="space-y-2">
                            <div class="text-3xl font-bold text-teal-600 dark:text-teal-400">{{ bestFlySize.size }}</div>
                            <div class="text-sm text-muted-foreground">{{ bestFlySize.total }} fish caught</div>
                        </div>
                        <p v-else class="text-muted-foreground">No fly size data yet</p>
                    </CardContent>
                </DashboardCard>

                <!-- Fly Size by Species -->
                <DashboardCard
                    v-if="isCardVisible('fly_size_by_species')"
                    card-id="fly_size_by_species"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('fly_size_by_species')"
                    :order="getCardOrder('fly_size_by_species')"
                    :size="getCardSize('fly_size_by_species')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('fly_size_by_species')"
                    :is-last="isCardLast('fly_size_by_species')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('fly_size_by_species')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-teal-50/30 to-cyan-50/30 dark:from-teal-950/10 dark:to-cyan-950/10"
                >
                    <DashboardCardHeader title="Fly Size by Species" subtitle="Best fly size for each species" emoji="🐟" color="teal" gradientTo="cyan" />
                    <CardContent class="pt-0 pb-4">
                        <div v-if="flySizeBySpecies.length > 0" class="space-y-2">
                            <div v-for="item in flySizeBySpecies" :key="item.species" class="flex items-center justify-between">
                                <span class="text-sm truncate flex-1">{{ item.species }}</span>
                                <span class="text-sm font-medium text-teal-600 dark:text-teal-400 ml-2">{{ item.size }}</span>
                            </div>
                        </div>
                        <p v-else class="text-muted-foreground">No fly size data yet</p>
                    </CardContent>
                </DashboardCard>

                <!-- Fly Size by Season -->
                <DashboardCard
                    v-if="isCardVisible('fly_size_by_season')"
                    card-id="fly_size_by_season"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('fly_size_by_season')"
                    :order="getCardOrder('fly_size_by_season')"
                    :size="getCardSize('fly_size_by_season')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('fly_size_by_season')"
                    :is-last="isCardLast('fly_size_by_season')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('fly_size_by_season')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-green-50/30 to-emerald-50/30 dark:from-green-950/10 dark:to-emerald-950/10"
                >
                    <DashboardCardHeader title="Fly Size by Season" subtitle="Best fly size for each season" :icon="Calendar" color="green" gradientTo="emerald" />
                    <CardContent class="pt-0 pb-4">
                        <div v-if="flySizeBySeason.length > 0" class="grid grid-cols-2 gap-2">
                            <div v-for="item in flySizeBySeason" :key="item.season" class="text-center p-2 rounded bg-green-50 dark:bg-green-900/20">
                                <div class="text-xs text-muted-foreground">{{ item.season }}</div>
                                <div class="text-lg font-bold text-green-600 dark:text-green-400">{{ item.size }}</div>
                            </div>
                        </div>
                        <p v-else class="text-muted-foreground">No seasonal data yet</p>
                    </CardContent>
                </DashboardCard>

                <!-- Fishing Radius -->
                <DashboardCard
                    v-if="isCardVisible('fishing_radius')"
                    card-id="fishing_radius"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('fishing_radius')"
                    :order="getCardOrder('fishing_radius')"
                    :size="getCardSize('fishing_radius')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('fishing_radius')"
                    :is-last="isCardLast('fishing_radius')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('fishing_radius')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-sky-50/30 to-blue-50/30 dark:from-sky-950/10 dark:to-blue-950/10"
                >
                    <DashboardCardHeader title="Fishing Radius" subtitle="Max distance between spots" :icon="Globe" color="sky" gradientTo="blue" />
                    <CardContent class="pt-0 pb-4">
                        <div v-if="fishingRadius" class="space-y-2">
                            <div class="text-3xl font-bold text-sky-600 dark:text-sky-400">{{ fishingRadius }} mi</div>
                            <div class="text-sm text-muted-foreground">Your fishing range</div>
                        </div>
                        <p v-else class="text-muted-foreground">Need multiple locations</p>
                    </CardContent>
                </DashboardCard>

                <!-- Freshwater vs Saltwater -->
                <DashboardCard
                    v-if="isCardVisible('freshwater_vs_saltwater')"
                    card-id="freshwater_vs_saltwater"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('freshwater_vs_saltwater')"
                    :order="getCardOrder('freshwater_vs_saltwater')"
                    :size="getCardSize('freshwater_vs_saltwater')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('freshwater_vs_saltwater')"
                    :is-last="isCardLast('freshwater_vs_saltwater')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('freshwater_vs_saltwater')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-cyan-50/30 to-teal-50/30 dark:from-cyan-950/10 dark:to-teal-950/10"
                >
                    <DashboardCardHeader title="Freshwater vs Saltwater" subtitle="Catches by water type" :icon="Waves" color="cyan" gradientTo="teal" />
                    <CardContent class="pt-0 pb-4">
                        <div v-if="freshwaterVsSaltwater.freshwater > 0 || freshwaterVsSaltwater.saltwater > 0" class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-sm">Freshwater</span>
                                <span class="font-bold text-blue-600 dark:text-blue-400">{{ freshwaterVsSaltwater.freshwater }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm">Saltwater</span>
                                <span class="font-bold text-cyan-600 dark:text-cyan-400">{{ freshwaterVsSaltwater.saltwater }}</span>
                            </div>
                        </div>
                        <p v-else class="text-muted-foreground">No water type data yet</p>
                    </CardContent>
                </DashboardCard>

                <!-- Species by Water Type -->
                <DashboardCard
                    v-if="isCardVisible('species_by_water_type')"
                    card-id="species_by_water_type"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('species_by_water_type')"
                    :order="getCardOrder('species_by_water_type')"
                    :size="getCardSize('species_by_water_type')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('species_by_water_type')"
                    :is-last="isCardLast('species_by_water_type')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('species_by_water_type')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-violet-50/30 to-purple-50/30 dark:from-violet-950/10 dark:to-purple-950/10"
                >
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
                </DashboardCard>

                <!-- Species by State Pie Chart -->
                <DashboardCard
                    v-if="isCardVisible('states_pie_chart')"
                    card-id="states_pie_chart"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('states_pie_chart')"
                    :order="getCardOrder('states_pie_chart')"
                    :size="getCardSize('states_pie_chart')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('states_pie_chart')"
                    :is-last="isCardLast('states_pie_chart')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('states_pie_chart')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-emerald-50/30 to-teal-50/30 dark:from-emerald-950/10 dark:to-teal-950/10"
                >
                    <DashboardCardHeader title="Species by State" subtitle="Geographic species diversity" :icon="Map" color="emerald" gradientTo="teal" />
                    <CardContent class="pt-0 pb-3">
                        <div v-if="speciesByState.length > 0 && statePieSlices.length > 0" class="flex items-center gap-4">
                            <!-- SVG Pie Chart -->
                            <div class="relative w-44 h-44 flex-shrink-0">
                                <svg class="w-full h-full" viewBox="0 0 200 200">
                                    <!-- Pie slices -->
                                    <g v-for="(slice, index) in statePieSlices" :key="`state-slice-${index}`">
                                        <path
                                            :d="slice.path"
                                            :fill="slice.color"
                                            :class="hoveredStateSlice === index ? 'opacity-100' : 'opacity-80'"
                                            class="cursor-pointer transition-all hover:opacity-100 stroke-emerald-200 dark:stroke-emerald-800"
                                            stroke-width="1"
                                            @mouseenter="hoveredStateSlice = index"
                                            @mouseleave="hoveredStateSlice = null"
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
                                        {{ speciesByState.length }}
                                    </text>
                                    <text
                                        x="100"
                                        y="110"
                                        text-anchor="middle"
                                        class="text-xs text-muted-foreground"
                                        fill="currentColor"
                                    >
                                        States
                                    </text>
                                </svg>
                            </div>

                            <!-- Legend -->
                            <div class="flex-1 space-y-1 max-h-44 overflow-y-auto">
                                <div
                                    v-for="(stateData, index) in speciesByState"
                                    :key="stateData.state"
                                    class="flex items-center justify-between gap-2 p-1.5 rounded hover:bg-muted/50 cursor-pointer transition-colors"
                                    :class="hoveredStateSlice === index ? 'bg-muted' : ''"
                                    @mouseenter="hoveredStateSlice = index"
                                    @mouseleave="hoveredStateSlice = null"
                                >
                                    <div class="flex items-center gap-2 min-w-0">
                                        <div
                                            class="w-2.5 h-2.5 rounded-full flex-shrink-0"
                                            :style="{ backgroundColor: getStateColor(index) }"
                                        ></div>
                                        <span class="text-xs font-medium truncate">{{ stateData.state }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 flex-shrink-0">
                                        <span class="text-xs font-bold">{{ stateData.species_count }} species</span>
                                        <span class="text-xs text-muted-foreground">
                                            ({{ statePieSlices[index]?.percentage }}%)
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-muted-foreground">No state data yet</p>
                    </CardContent>
                </DashboardCard>

                <!-- Species by Country Pie Chart -->
                <DashboardCard
                    v-if="isCardVisible('countries_pie_chart')"
                    card-id="countries_pie_chart"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('countries_pie_chart')"
                    :order="getCardOrder('countries_pie_chart')"
                    :size="getCardSize('countries_pie_chart')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('countries_pie_chart')"
                    :is-last="isCardLast('countries_pie_chart')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('countries_pie_chart')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-blue-50/30 to-indigo-50/30 dark:from-blue-950/10 dark:to-indigo-950/10"
                >
                    <DashboardCardHeader title="Species by Country" subtitle="International species diversity" :icon="Globe" color="blue" gradientTo="indigo" />
                    <CardContent class="pt-0 pb-3">
                        <div v-if="speciesByCountry.length > 0 && countryPieSlices.length > 0" class="flex items-center gap-4">
                            <!-- SVG Pie Chart -->
                            <div class="relative w-44 h-44 flex-shrink-0">
                                <svg class="w-full h-full" viewBox="0 0 200 200">
                                    <!-- Pie slices -->
                                    <g v-for="(slice, index) in countryPieSlices" :key="`country-slice-${index}`">
                                        <path
                                            :d="slice.path"
                                            :fill="slice.color"
                                            :class="hoveredCountrySlice === index ? 'opacity-100' : 'opacity-80'"
                                            class="cursor-pointer transition-all hover:opacity-100 stroke-blue-200 dark:stroke-blue-800"
                                            stroke-width="1"
                                            @mouseenter="hoveredCountrySlice = index"
                                            @mouseleave="hoveredCountrySlice = null"
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
                                        {{ speciesByCountry.length }}
                                    </text>
                                    <text
                                        x="100"
                                        y="110"
                                        text-anchor="middle"
                                        class="text-xs text-muted-foreground"
                                        fill="currentColor"
                                    >
                                        Countries
                                    </text>
                                </svg>
                            </div>

                            <!-- Legend -->
                            <div class="flex-1 space-y-1 max-h-44 overflow-y-auto">
                                <div
                                    v-for="(countryData, index) in speciesByCountry"
                                    :key="countryData.country"
                                    class="flex items-center justify-between gap-2 p-1.5 rounded hover:bg-muted/50 cursor-pointer transition-colors"
                                    :class="hoveredCountrySlice === index ? 'bg-muted' : ''"
                                    @mouseenter="hoveredCountrySlice = index"
                                    @mouseleave="hoveredCountrySlice = null"
                                >
                                    <div class="flex items-center gap-2 min-w-0">
                                        <div
                                            class="w-2.5 h-2.5 rounded-full flex-shrink-0"
                                            :style="{ backgroundColor: getCountryColor(index) }"
                                        ></div>
                                        <span class="text-xs font-medium truncate">{{ countryData.country }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 flex-shrink-0">
                                        <span class="text-xs font-bold">{{ countryData.species_count }} species</span>
                                        <span class="text-xs text-muted-foreground">
                                            ({{ countryPieSlices[index]?.percentage }}%)
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-muted-foreground">No country data yet</p>
                    </CardContent>
                </DashboardCard>

                <!-- Catches by State Pie Chart -->
                <DashboardCard
                    v-if="isCardVisible('catches_state_pie_chart')"
                    card-id="catches_state_pie_chart"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('catches_state_pie_chart')"
                    :order="getCardOrder('catches_state_pie_chart')"
                    :size="getCardSize('catches_state_pie_chart')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('catches_state_pie_chart')"
                    :is-last="isCardLast('catches_state_pie_chart')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('catches_state_pie_chart')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-green-50/30 to-lime-50/30 dark:from-green-950/10 dark:to-lime-950/10"
                >
                    <DashboardCardHeader title="Catches by State" subtitle="State-by-state totals" :icon="Map" color="green" gradientTo="lime" />
                    <CardContent class="pt-0 pb-3">
                        <div v-if="catchesByState.length > 0 && catchesStatePieSlices.length > 0" class="flex items-center gap-4">
                            <!-- SVG Pie Chart -->
                            <div class="relative w-44 h-44 flex-shrink-0">
                                <svg class="w-full h-full" viewBox="0 0 200 200">
                                    <!-- Pie slices -->
                                    <g v-for="(slice, index) in catchesStatePieSlices" :key="`catches-state-slice-${index}`">
                                        <path
                                            :d="slice.path"
                                            :fill="slice.color"
                                            :class="hoveredCatchesStateSlice === index ? 'opacity-100' : 'opacity-80'"
                                            class="cursor-pointer transition-all hover:opacity-100 stroke-green-200 dark:stroke-green-800"
                                            stroke-width="1"
                                            @mouseenter="hoveredCatchesStateSlice = index"
                                            @mouseleave="hoveredCatchesStateSlice = null"
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
                                        {{ catchesByState.length }}
                                    </text>
                                    <text
                                        x="100"
                                        y="110"
                                        text-anchor="middle"
                                        class="text-xs text-muted-foreground"
                                        fill="currentColor"
                                    >
                                        States
                                    </text>
                                </svg>
                            </div>

                            <!-- Legend -->
                            <div class="flex-1 space-y-1 max-h-44 overflow-y-auto">
                                <div
                                    v-for="(stateData, index) in catchesByState"
                                    :key="stateData.state"
                                    class="flex items-center justify-between gap-2 p-1.5 rounded hover:bg-muted/50 cursor-pointer transition-colors"
                                    :class="hoveredCatchesStateSlice === index ? 'bg-muted' : ''"
                                    @mouseenter="hoveredCatchesStateSlice = index"
                                    @mouseleave="hoveredCatchesStateSlice = null"
                                >
                                    <div class="flex items-center gap-2 min-w-0">
                                        <div
                                            class="w-2.5 h-2.5 rounded-full flex-shrink-0"
                                            :style="{ backgroundColor: getCatchesStateColor(index) }"
                                        ></div>
                                        <span class="text-xs font-medium truncate">{{ stateData.state }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 flex-shrink-0">
                                        <span class="text-xs font-bold">{{ stateData.total }} catches</span>
                                        <span class="text-xs text-muted-foreground">
                                            ({{ catchesStatePieSlices[index]?.percentage }}%)
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-muted-foreground">No state data yet</p>
                    </CardContent>
                </DashboardCard>

                <!-- Catches by Country Pie Chart -->
                <DashboardCard
                    v-if="isCardVisible('catches_country_pie_chart')"
                    card-id="catches_country_pie_chart"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('catches_country_pie_chart')"
                    :order="getCardOrder('catches_country_pie_chart')"
                    :size="getCardSize('catches_country_pie_chart')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('catches_country_pie_chart')"
                    :is-last="isCardLast('catches_country_pie_chart')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('catches_country_pie_chart')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-sky-50/30 to-cyan-50/30 dark:from-sky-950/10 dark:to-cyan-950/10"
                >
                    <DashboardCardHeader title="Catches by Country" subtitle="Country-by-country totals" :icon="Globe" color="sky" gradientTo="cyan" />
                    <CardContent class="pt-0 pb-3">
                        <div v-if="catchesByCountry.length > 0 && catchesCountryPieSlices.length > 0" class="flex items-center gap-4">
                            <!-- SVG Pie Chart -->
                            <div class="relative w-44 h-44 flex-shrink-0">
                                <svg class="w-full h-full" viewBox="0 0 200 200">
                                    <!-- Pie slices -->
                                    <g v-for="(slice, index) in catchesCountryPieSlices" :key="`catches-country-slice-${index}`">
                                        <path
                                            :d="slice.path"
                                            :fill="slice.color"
                                            :class="hoveredCatchesCountrySlice === index ? 'opacity-100' : 'opacity-80'"
                                            class="cursor-pointer transition-all hover:opacity-100 stroke-sky-200 dark:stroke-sky-800"
                                            stroke-width="1"
                                            @mouseenter="hoveredCatchesCountrySlice = index"
                                            @mouseleave="hoveredCatchesCountrySlice = null"
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
                                        {{ catchesByCountry.length }}
                                    </text>
                                    <text
                                        x="100"
                                        y="110"
                                        text-anchor="middle"
                                        class="text-xs text-muted-foreground"
                                        fill="currentColor"
                                    >
                                        Countries
                                    </text>
                                </svg>
                            </div>

                            <!-- Legend -->
                            <div class="flex-1 space-y-1 max-h-44 overflow-y-auto">
                                <div
                                    v-for="(countryData, index) in catchesByCountry"
                                    :key="countryData.country"
                                    class="flex items-center justify-between gap-2 p-1.5 rounded hover:bg-muted/50 cursor-pointer transition-colors"
                                    :class="hoveredCatchesCountrySlice === index ? 'bg-muted' : ''"
                                    @mouseenter="hoveredCatchesCountrySlice = index"
                                    @mouseleave="hoveredCatchesCountrySlice = null"
                                >
                                    <div class="flex items-center gap-2 min-w-0">
                                        <div
                                            class="w-2.5 h-2.5 rounded-full flex-shrink-0"
                                            :style="{ backgroundColor: getCatchesCountryColor(index) }"
                                        ></div>
                                        <span class="text-xs font-medium truncate">{{ countryData.country }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 flex-shrink-0">
                                        <span class="text-xs font-bold">{{ countryData.total }} catches</span>
                                        <span class="text-xs text-muted-foreground">
                                            ({{ catchesCountryPieSlices[index]?.percentage }}%)
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-muted-foreground">No country data yet</p>
                    </CardContent>
                </DashboardCard>

                <!-- Weekend Warrior -->
                <DashboardCard
                    v-if="isCardVisible('weekend_warrior')"
                    card-id="weekend_warrior"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('weekend_warrior')"
                    :order="getCardOrder('weekend_warrior')"
                    :size="getCardSize('weekend_warrior')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('weekend_warrior')"
                    :is-last="isCardLast('weekend_warrior')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('weekend_warrior')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-violet-50/30 to-purple-50/30 dark:from-violet-950/10 dark:to-purple-950/10"
                >
                    <DashboardCardHeader title="Weekend Warrior" subtitle="Weekend vs weekday fishing" :icon="CalendarDays" color="violet" gradientTo="purple" />
                    <CardContent class="pt-0 pb-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-center p-2 rounded bg-violet-50 dark:bg-violet-900/20">
                                <div class="text-xs text-muted-foreground">Weekend</div>
                                <div class="text-xl font-bold text-violet-600 dark:text-violet-400">{{ weekendWarrior.weekend.catches }}</div>
                                <div class="text-xs text-muted-foreground">{{ weekendWarrior.weekend.avg }}/day</div>
                            </div>
                            <div class="text-center p-2 rounded bg-purple-50 dark:bg-purple-900/20">
                                <div class="text-xs text-muted-foreground">Weekday</div>
                                <div class="text-xl font-bold text-purple-600 dark:text-purple-400">{{ weekendWarrior.weekday.catches }}</div>
                                <div class="text-xs text-muted-foreground">{{ weekendWarrior.weekday.avg }}/day</div>
                            </div>
                        </div>
                    </CardContent>
                </DashboardCard>

                <!-- Monthly Personal Bests -->
                <DashboardCard
                    v-if="isCardVisible('monthly_personal_bests')"
                    card-id="monthly_personal_bests"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('monthly_personal_bests')"
                    :order="getCardOrder('monthly_personal_bests')"
                    :size="getCardSize('monthly_personal_bests')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('monthly_personal_bests')"
                    :is-last="isCardLast('monthly_personal_bests')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('monthly_personal_bests')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-amber-50/30 to-yellow-50/30 dark:from-amber-950/10 dark:to-yellow-950/10"
                >
                    <DashboardCardHeader title="Monthly Personal Bests" subtitle="Biggest fish each month" :icon="Award" color="amber" gradientTo="yellow" />
                    <CardContent class="pt-0 pb-4">
                        <div v-if="monthlyPersonalBests.length > 0" class="space-y-2">
                            <div v-for="item in [...monthlyPersonalBests.slice(-6)].reverse()" :key="item.month" class="flex items-center justify-between">
                                <span class="text-sm">{{ item.month }}</span>
                                <span class="text-sm font-medium text-amber-600 dark:text-amber-400">{{ item.biggest_size }}"</span>
                            </div>
                        </div>
                        <p v-else class="text-muted-foreground">No size data yet</p>
                    </CardContent>
                </DashboardCard>

                <!-- Catch Rate Trend -->
                <DashboardCard
                    v-if="isCardVisible('catch_rate_trend')"
                    card-id="catch_rate_trend"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('catch_rate_trend')"
                    :order="getCardOrder('catch_rate_trend')"
                    :size="getCardSize('catch_rate_trend')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('catch_rate_trend')"
                    :is-last="isCardLast('catch_rate_trend')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('catch_rate_trend')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-green-50/30 to-teal-50/30 dark:from-green-950/10 dark:to-teal-950/10"
                >
                    <DashboardCardHeader title="Catch Rate Trend" subtitle="Fish per trip over time" :icon="TrendingUp" color="green" gradientTo="teal" />
                    <CardContent class="pt-0 pb-4">
                        <div v-if="catchRateTrend.length > 0" class="space-y-2">
                            <div v-for="item in [...catchRateTrend.slice(-6)].reverse()" :key="item.month" class="flex items-center justify-between">
                                <span class="text-sm">{{ item.month }}</span>
                                <span class="text-sm font-medium text-green-600 dark:text-green-400">{{ item.catch_rate }} fish/trip</span>
                            </div>
                        </div>
                        <p v-else class="text-muted-foreground">No trip data yet</p>
                    </CardContent>
                </DashboardCard>

                <!-- Species by Location -->
                <DashboardCard
                    v-if="isCardVisible('species_by_location')"
                    card-id="species_by_location"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('species_by_location')"
                    :order="getCardOrder('species_by_location')"
                    :size="getCardSize('species_by_location')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('species_by_location')"
                    :is-last="isCardLast('species_by_location')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('species_by_location')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-blue-50/30 to-sky-50/30 dark:from-blue-950/10 dark:to-sky-950/10"
                >
                    <DashboardCardHeader title="Species by Location" subtitle="Top species at each spot" :icon="MapPin" color="blue" gradientTo="sky" />
                    <CardContent class="pt-0 pb-4">
                        <div v-if="speciesByLocation.length > 0" class="space-y-2">
                            <div v-for="item in speciesByLocation" :key="item.location" class="flex items-center justify-between">
                                <span class="text-sm truncate flex-1">{{ item.location }}</span>
                                <span class="text-sm font-medium text-blue-600 dark:text-blue-400 ml-2">{{ item.species }}</span>
                            </div>
                        </div>
                        <p v-else class="text-muted-foreground">No location data yet</p>
                    </CardContent>
                </DashboardCard>

                <!-- Fly Color by Conditions -->
                <DashboardCard
                    v-if="isCardVisible('fly_color_by_conditions')"
                    card-id="fly_color_by_conditions"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('fly_color_by_conditions')"
                    :order="getCardOrder('fly_color_by_conditions')"
                    :size="getCardSize('fly_color_by_conditions')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('fly_color_by_conditions')"
                    :is-last="isCardLast('fly_color_by_conditions')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('fly_color_by_conditions')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-pink-50/30 to-rose-50/30 dark:from-pink-950/10 dark:to-rose-950/10"
                >
                    <DashboardCardHeader title="Fly Color by Conditions" subtitle="Best fly color for each weather" :icon="Palette" color="pink" gradientTo="rose" />
                    <CardContent class="pt-0 pb-4">
                        <div v-if="flyColorByConditions.length > 0" class="space-y-2">
                            <div v-for="item in flyColorByConditions" :key="item.cloud" class="flex items-center justify-between">
                                <span class="text-sm truncate flex-1">{{ item.cloud }}</span>
                                <span class="text-sm font-medium text-pink-600 dark:text-pink-400 ml-2">{{ item.color }}</span>
                            </div>
                        </div>
                        <p v-else class="text-muted-foreground">No color/weather data yet</p>
                    </CardContent>
                </DashboardCard>

                <!-- Multi-Species Days -->
                <DashboardCard
                    v-if="isCardVisible('multi_species_days')"
                    card-id="multi_species_days"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('multi_species_days')"
                    :order="getCardOrder('multi_species_days')"
                    :size="getCardSize('multi_species_days')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('multi_species_days')"
                    :is-last="isCardLast('multi_species_days')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('multi_species_days')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-orange-50/30 to-amber-50/30 dark:from-orange-950/10 dark:to-amber-950/10"
                >
                    <DashboardCardHeader title="Multi-Species Days" subtitle="Days with multiple species" :icon="Layers" color="orange" gradientTo="amber" />
                    <CardContent class="pt-0 pb-4">
                        <div class="space-y-2">
                            <div class="text-3xl font-bold text-orange-600 dark:text-orange-400">{{ multiSpeciesDays.count }}</div>
                            <div class="text-sm text-muted-foreground">{{ multiSpeciesDays.percentage }}% of {{ multiSpeciesDays.total_days }} days</div>
                        </div>
                    </CardContent>
                </DashboardCard>

                <!-- Quantity vs Quality -->
                <DashboardCard
                    v-if="isCardVisible('quantity_vs_quality')"
                    card-id="quantity_vs_quality"
                    :is-edit-mode="isEditMode"
                    :is-hidden="isCardHidden('quantity_vs_quality')"
                    :order="getCardOrder('quantity_vs_quality')"
                    :size="getCardSize('quantity_vs_quality')"
                    @hide="hideCard"
                    @show="showCard"
                    @resize="resizeCard"
                    :is-first="isCardFirst('quantity_vs_quality')"
                    :is-last="isCardLast('quantity_vs_quality')"
                    @move-up="moveCardUp"
                    @move-down="moveCardDown"
                    :display-position="getCardDisplayPosition('quantity_vs_quality')"
                    :total-visible="getTotalVisibleCards"
                    @jump-to-position="jumpCardToPosition"
                    class="bg-gradient-to-br from-teal-50/30 to-cyan-50/30 dark:from-teal-950/10 dark:to-cyan-950/10"
                >
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
                </DashboardCard>
            </div>
            <!-- End of unified grid -->
        </div>
    </AppLayout>
</template>
