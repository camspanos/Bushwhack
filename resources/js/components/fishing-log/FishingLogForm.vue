<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Calendar } from '@/components/ui/calendar';
import { Checkbox } from '@/components/ui/checkbox';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import LocationFormDialog from '@/components/LocationFormDialog.vue';
import { Fish, MapPin, Calendar as CalendarIcon, Clock, Plus, ChevronDown, X, AlertCircle, Cloud, Droplets } from 'lucide-vue-next';
import { CalendarDate, getLocalTimeZone, today } from '@internationalized/date';
import axios from '@/lib/axios';

// Cache for form data by date+location (15 minute expiry)
// Using sessionStorage to persist across component remounts
interface CachedFormData {
    weather: WeatherData;
    water_condition: WaterConditionData;
    friend_ids: number[];
    timestamp: number;
}

const CACHE_EXPIRY_MS = 15 * 60 * 1000; // 15 minutes
const CACHE_STORAGE_KEY = 'fishingLogFormCache';

const getCacheKey = (date: string, locationId: string): string => {
    return `${date}|${locationId}`;
};

const getStoredCache = (): Record<string, CachedFormData> => {
    try {
        const stored = sessionStorage.getItem(CACHE_STORAGE_KEY);
        return stored ? JSON.parse(stored) : {};
    } catch {
        return {};
    }
};

const setStoredCache = (cache: Record<string, CachedFormData>): void => {
    try {
        sessionStorage.setItem(CACHE_STORAGE_KEY, JSON.stringify(cache));
    } catch {
        // Ignore storage errors
    }
};

const getCachedData = (date: string, locationId: string): CachedFormData | null => {
    const key = getCacheKey(date, locationId);
    const cache = getStoredCache();
    const cached = cache[key];

    if (!cached) return null;

    // Check if cache has expired
    if (Date.now() - cached.timestamp > CACHE_EXPIRY_MS) {
        delete cache[key];
        setStoredCache(cache);
        return null;
    }

    return cached;
};

const setCachedData = (date: string, locationId: string, data: Omit<CachedFormData, 'timestamp'>): void => {
    const key = getCacheKey(date, locationId);
    const cache = getStoredCache();
    cache[key] = {
        ...data,
        timestamp: Date.now(),
    };
    setStoredCache(cache);
    console.log('Cache SET:', key, data);
};

// Types
export interface WeatherData {
    temperature: string;
    cloud: string;
    wind: string;
    precipitation: string;
    barometric_pressure: string;
}

export interface WaterConditionData {
    temperature: string;
    clarity: string;
    level: string;
    speed: string;
    surface_condition: string;
    tide: string;
}

export interface FishingLogFormData {
    date: string;
    time: string;
    user_location_id: string;
    user_fish_id: string;
    quantity: string;
    maxSize: string;
    maxWeight: string;
    avgSize: string;
    avgWeight: string;
    user_fly_id: string;
    user_rod_id: string;
    fishingStyle: string;
    moonPhase: string;
    moonPosition: string;
    timeOfDay: string;
    friend_ids: number[];
    notes: string;
    weather: WeatherData;
    water_condition: WaterConditionData;
}

export interface FishingLogInitialData {
    id?: number;
    date?: string;
    time?: string;
    user_location_id?: number;
    user_fish_id?: number;
    quantity?: number;
    max_size?: number;
    max_weight?: number;
    avg_size?: number;
    avg_weight?: number;
    user_fly_id?: number;
    user_rod_id?: number;
    style?: string;
    moon_phase?: string;
    moon_altitude?: number;
    moon_position?: string;
    time_of_day?: string;
    friends?: { id: number; name: string }[];
    notes?: string;
    weather?: {
        id?: number;
        temperature?: string;
        cloud?: string;
        wind?: string;
        precipitation?: string;
        barometric_pressure?: string;
    };
    water_condition?: {
        id?: number;
        temperature?: string;
        clarity?: string;
        level?: string;
        speed?: string;
        surface_condition?: string;
        tide?: string;
    };
}

// Props
const props = withDefaults(defineProps<{
    mode: 'create' | 'edit';
    initialData?: FishingLogInitialData;
    showFooter?: boolean;
    submitLabel?: string;
    cancelLabel?: string;
}>(), {
    mode: 'create',
    showFooter: true,
    submitLabel: 'Save Fishing Log',
    cancelLabel: 'Cancel',
});

// Emits
const emit = defineEmits<{
    (e: 'submit', data: any): void;
    (e: 'cancel'): void;
    (e: 'success', response: any): void;
    (e: 'error', error: any): void;
}>();

// Date state
const selectedDate = ref(today(getLocalTimeZone()));
const dateInput = ref('');
const datePickerOpen = ref(false);
const maxDate = today(getLocalTimeZone()); // Prevent future date selection

// Time picker state
const timePickerOpen = ref(false);
const selectedHour = ref(12);
const selectedMinute = ref(0);
const selectedPeriod = ref<'AM' | 'PM'>('AM');

// Moon altitude (for display purposes only)
const moonAltitude = ref<number | null>(null);

// Form data
const formData = ref<FishingLogFormData>({
    date: '',
    time: '',
    user_location_id: '',
    user_fish_id: '',
    quantity: '',
    maxSize: '',
    maxWeight: '',
    avgSize: '',
    avgWeight: '',
    user_fly_id: '',
    user_rod_id: '',
    fishingStyle: '',
    moonPhase: '',
    moonPosition: '',
    timeOfDay: '',
    friend_ids: [],
    notes: '',
    weather: {
        temperature: '',
        cloud: '',
        wind: '',
        precipitation: '',
        barometric_pressure: '',
    },
    water_condition: {
        temperature: '',
        clarity: '',
        level: '',
        speed: '',
        surface_condition: '',
        tide: '',
    },
});

// Form validation errors
const formErrors = ref<Record<string, string[]>>({});
const formErrorMessage = ref('');

// Dynamic data from API
const locations = ref<any[]>([]);
const fishSpecies = ref<any[]>([]);
const flies = ref<any[]>([]);
const equipment = ref<any[]>([]);
const friends = ref<any[]>([]);

// Modal states
const showLocationModal = ref(false);
const showFishModal = ref(false);
const showFlyModal = ref(false);
const showEquipmentModal = ref(false);
const showFriendModal = ref(false);
const showWeatherModal = ref(false);
const showWaterConditionModal = ref(false);

// Error messages for each modal
const fishError = ref('');
const flyError = ref('');
const equipmentError = ref('');
const friendError = ref('');

// New item forms
const newFish = ref({ species: '', water_type: '' });
const newFly = ref({ name: '', color: '', size: '', type: '' });
const newEquipment = ref({ rod_name: '', rod_weight: '', rod_length: '', reel: '', line: '' });
const newFriend = ref({ name: '' });

// Moon phase options
const moonPhaseOptions = [
    'New Moon',
    'Waxing Crescent',
    'First Quarter',
    'Waxing Gibbous',
    'Full Moon',
    'Waning Gibbous',
    'Last Quarter',
    'Waning Crescent',
];

// Time of day options
const timeOfDayOptions = [
    'Dawn',
    'Morning',
    'Midday',
    'Afternoon',
    'Dusk',
    'Night',
];

// Moon position options based on Solunar Theory
// - Overhead: Major feeding window (~2-3 hours around upper transit)
// - Rising: Minor feeding window (~45-75 min around moonrise)
// - Setting: Minor feeding window (~45-75 min around moonset)
// - Underfoot: Major feeding window (~2-3 hours around lower transit)
// - Above Horizon: Transitional period between Rising/Setting and Overhead
// - Below Horizon: Transitional period between Setting and Rising (via Underfoot)
const moonPositionOptions = [
    'Overhead',
    'Rising',
    'Setting',
    'Underfoot',
    'Above Horizon',
    'Below Horizon',
];

// Weather options
const cloudOptions = ['Clear', 'Partly Cloudy', 'Mostly Cloudy', 'Overcast'];
const windOptions = ['Calm', 'Light', 'Moderate', 'Strong', 'Very Strong'];
const precipitationOptions = ['None', 'Light Rain', 'Moderate Rain', 'Heavy Rain', 'Light Snow', 'Heavy Snow', 'Sleet', 'Hail'];

// Water condition options
const clarityOptions = ['Crystal Clear', 'Clear', 'Slightly Stained', 'Stained', 'Murky', 'Muddy'];
const levelOptions = ['Very Low', 'Low', 'Normal', 'High', 'Very High', 'Flood'];
const speedOptions = ['Still', 'Slow', 'Moderate', 'Fast', 'Very Fast'];
const surfaceConditionOptions = ['Calm', 'Rippled', 'Choppy', 'Rough', 'Very Rough'];
const tideOptions = ['Low', 'Rising', 'High', 'Falling', 'Slack'];

// Computed formatted date
const formattedDate = computed(() => {
    if (dateInput.value) return dateInput.value;
    if (!selectedDate.value) return '';
    const year = selectedDate.value.year;
    const month = String(selectedDate.value.month).padStart(2, '0');
    const day = String(selectedDate.value.day).padStart(2, '0');
    return `${year}-${month}-${day}`;
});

// Computed submit button label
const computedSubmitLabel = computed(() => {
    if (props.submitLabel !== 'Save Fishing Log') return props.submitLabel;
    return props.mode === 'edit' ? 'Update Fishing Log' : 'Save Fishing Log';
});

// Computed properties for weather and water condition summaries
const hasWeatherData = computed(() => {
    const w = formData.value.weather;
    return w.temperature || w.cloud || w.wind || w.precipitation || w.barometric_pressure;
});

const weatherSummary = computed(() => {
    const w = formData.value.weather;
    const parts: string[] = [];
    if (w.temperature) parts.push(`${w.temperature}°`);
    if (w.cloud) parts.push(w.cloud);
    if (w.wind) parts.push(`Wind: ${w.wind}`);
    if (w.precipitation && w.precipitation !== 'None') parts.push(w.precipitation);
    return parts.length > 0 ? parts.join(', ') : 'Weather conditions set';
});

const hasWaterConditionData = computed(() => {
    const wc = formData.value.water_condition;
    return wc.temperature || wc.clarity || wc.level || wc.speed || wc.surface_condition || wc.tide;
});

const waterConditionSummary = computed(() => {
    const wc = formData.value.water_condition;
    const parts: string[] = [];
    if (wc.temperature) parts.push(`${wc.temperature}°`);
    if (wc.clarity) parts.push(wc.clarity);
    if (wc.level) parts.push(`Level: ${wc.level}`);
    if (wc.speed) parts.push(`Speed: ${wc.speed}`);
    return parts.length > 0 ? parts.join(', ') : 'Water conditions set';
});

// Calculate moon phase based on date
const calculateMoonPhase = (year: number, month: number, day: number): string => {
    const knownNewMoon = new Date(2000, 0, 6, 18, 14);
    const targetDate = new Date(year, month - 1, day);
    const lunarCycle = 29.53058867;
    const daysSinceNewMoon = (targetDate.getTime() - knownNewMoon.getTime()) / (1000 * 60 * 60 * 24);
    const phase = ((daysSinceNewMoon % lunarCycle) + lunarCycle) % lunarCycle;

    if (phase < 1.84566) return 'New Moon';
    if (phase < 7.38264) return 'Waxing Crescent';
    if (phase < 9.22830) return 'First Quarter';
    if (phase < 14.76528) return 'Waxing Gibbous';
    if (phase < 16.61094) return 'Full Moon';
    if (phase < 22.14792) return 'Waning Gibbous';
    if (phase < 23.99358) return 'Last Quarter';
    if (phase < 29.53059) return 'Waning Crescent';
    return 'New Moon';
};

// Recalculate time of day by calling the backend API
// Uses the TimeOfDayCalculator service for accurate sunrise/sunset calculations
const recalculateTimeOfDay = async () => {
    if (!formData.value.time || !dateInput.value) {
        formData.value.timeOfDay = '';
        return;
    }

    try {
        const response = await axios.post('/fishing-logs/calculate-time-of-day', {
            time: formData.value.time,
            date: dateInput.value,
            location_id: formData.value.user_location_id ? parseInt(formData.value.user_location_id) : null,
        });

        if (response.data.time_of_day) {
            formData.value.timeOfDay = response.data.time_of_day;
        }
    } catch (error) {
        console.error('Failed to calculate time of day:', error);
        // Don't clear the value on error - keep whatever was there
    }
};

// Recalculate moon position by calling the backend API
// Uses the MoonPositionCalculator service for accurate astronomical calculations
// Only calculates when date, time, AND location are all set
const recalculateMoonPosition = async () => {
    // Require date, time, AND location to calculate moon position
    if (!dateInput.value || !formData.value.time || !formData.value.user_location_id) {
        formData.value.moonPosition = '';
        moonAltitude.value = null;
        return;
    }

    try {
        const response = await axios.post('/fishing-logs/calculate-moon-position', {
            time: formData.value.time,
            date: dateInput.value,
            location_id: parseInt(formData.value.user_location_id),
        });

        if (response.data.moon_position) {
            formData.value.moonPosition = response.data.moon_position;
            moonAltitude.value = response.data.moon_altitude;
        } else {
            formData.value.moonPosition = '';
            moonAltitude.value = null;
        }
    } catch (error) {
        console.error('Failed to calculate moon position:', error);
        // Don't clear the value on error - keep whatever was there
    }
};

// Initialize date input
const initializeDateInput = () => {
    const todayDate = today(getLocalTimeZone());
    const year = todayDate.year;
    const month = String(todayDate.month).padStart(2, '0');
    const day = String(todayDate.day).padStart(2, '0');
    dateInput.value = `${year}-${month}-${day}`;
    selectedDate.value = todayDate;
    formData.value.moonPhase = calculateMoonPhase(todayDate.year, todayDate.month, todayDate.day);
};

// Handle date input change
const handleInputChange = () => {
    if (!dateInput.value) return;
    try {
        const [year, month, day] = dateInput.value.split('-').map(Number);
        if (year && month && day) {
            selectedDate.value = new CalendarDate(year, month, day);
            formData.value.moonPhase = calculateMoonPhase(year, month, day);
            recalculateTimeOfDay();
            recalculateMoonPosition();
        }
    } catch (error) {
        console.error('Invalid date format:', error);
    }
};

// Handle calendar date selection
const handleCalendarSelect = (date: CalendarDate | undefined) => {
    if (!date) return;
    selectedDate.value = date;
    const year = date.year;
    const month = String(date.month).padStart(2, '0');
    const day = String(date.day).padStart(2, '0');
    dateInput.value = `${year}-${month}-${day}`;
    formData.value.moonPhase = calculateMoonPhase(date.year, date.month, date.day);
    recalculateTimeOfDay();
    recalculateMoonPosition();
    datePickerOpen.value = false;
};

// Format time for display (12-hour format with AM/PM)
const formatTimeDisplay = (time: string): string => {
    if (!time) return 'Pick a time';
    const [hours, minutes] = time.split(':').map(Number);
    const period = hours >= 12 ? 'PM' : 'AM';
    const displayHour = hours === 0 ? 12 : hours > 12 ? hours - 12 : hours;
    return `${displayHour}:${String(minutes).padStart(2, '0')} ${period}`;
};

// Parse time string to update time picker state
const parseTimeToPickerState = (time: string) => {
    if (!time) return;
    const [hours, minutes] = time.split(':').map(Number);
    selectedMinute.value = minutes;
    if (hours === 0) {
        selectedHour.value = 12;
        selectedPeriod.value = 'AM';
    } else if (hours === 12) {
        selectedHour.value = 12;
        selectedPeriod.value = 'PM';
    } else if (hours > 12) {
        selectedHour.value = hours - 12;
        selectedPeriod.value = 'PM';
    } else {
        selectedHour.value = hours;
        selectedPeriod.value = 'AM';
    }
};

// Update formData.time from picker state
const updateTimeFromPicker = () => {
    let hours = selectedHour.value;
    if (selectedPeriod.value === 'AM') {
        hours = selectedHour.value === 12 ? 0 : selectedHour.value;
    } else {
        hours = selectedHour.value === 12 ? 12 : selectedHour.value + 12;
    }
    formData.value.time = `${String(hours).padStart(2, '0')}:${String(selectedMinute.value).padStart(2, '0')}`;
    recalculateTimeOfDay();
    recalculateMoonPosition();
};

// Handle time picker selection
const handleTimeSelect = () => {
    updateTimeFromPicker();
    timePickerOpen.value = false;
};

// Populate form with initial data (for edit mode)
const populateForm = (data: FishingLogInitialData) => {
    if (data.date) {
        const dateParts = data.date.split('T')[0].split('-');
        const year = parseInt(dateParts[0]);
        const month = dateParts[1].padStart(2, '0');
        const day = dateParts[2].padStart(2, '0');
        dateInput.value = `${year}-${month}-${day}`;
        selectedDate.value = new CalendarDate(year, parseInt(month), parseInt(day));
    }

    // Parse time into picker state
    if (data.time) {
        parseTimeToPickerState(data.time.substring(0, 5));
    }

    // Set moon altitude for display
    moonAltitude.value = data.moon_altitude || null;

    formData.value = {
        date: dateInput.value,
        time: data.time ? data.time.substring(0, 5) : '',
        user_location_id: data.user_location_id?.toString() || '',
        user_fish_id: data.user_fish_id?.toString() || '',
        quantity: data.quantity?.toString() || '',
        maxSize: data.max_size?.toString() || '',
        maxWeight: data.max_weight?.toString() || '',
        avgSize: data.avg_size?.toString() || '',
        avgWeight: data.avg_weight?.toString() || '',
        user_fly_id: data.user_fly_id?.toString() || '',
        user_rod_id: data.user_rod_id?.toString() || '',
        fishingStyle: data.style || '',
        moonPhase: data.moon_phase || '',
        moonPosition: data.moon_position || '',
        timeOfDay: data.time_of_day || '',
        friend_ids: data.friends?.map((f) => f.id) || [],
        notes: data.notes || '',
        weather: {
            temperature: data.weather?.temperature || '',
            cloud: data.weather?.cloud || '',
            wind: data.weather?.wind || '',
            precipitation: data.weather?.precipitation || '',
            barometric_pressure: data.weather?.barometric_pressure || '',
        },
        water_condition: {
            temperature: data.water_condition?.temperature || '',
            clarity: data.water_condition?.clarity || '',
            level: data.water_condition?.level || '',
            speed: data.water_condition?.speed || '',
            surface_condition: data.water_condition?.surface_condition || '',
            tide: data.water_condition?.tide || '',
        },
    };
};

// Clear form errors
const clearFormErrors = () => {
    formErrors.value = {};
    formErrorMessage.value = '';
};

// Reset form
const resetForm = () => {
    // Clear moon altitude
    moonAltitude.value = null;

    formData.value = {
        date: '',
        time: '',
        user_location_id: '',
        user_fish_id: '',
        quantity: '',
        maxSize: '',
        maxWeight: '',
        avgSize: '',
        avgWeight: '',
        user_fly_id: '',
        user_rod_id: '',
        fishingStyle: '',
        moonPhase: '',
        moonPosition: '',
        timeOfDay: '',
        friend_ids: [],
        notes: '',
        weather: {
            temperature: '',
            cloud: '',
            wind: '',
            precipitation: '',
            barometric_pressure: '',
        },
        water_condition: {
            temperature: '',
            clarity: '',
            level: '',
            speed: '',
            surface_condition: '',
            tide: '',
        },
    };
    initializeDateInput();
    clearFormErrors();
};

// Fetch data from API
const fetchLocations = async () => {
    try {
        const response = await axios.get('/locations', { params: { per_page: 1000 } });
        const data = response.data.data || response.data;
        locations.value = (Array.isArray(data) ? data : [])
            .filter((loc: any) => loc !== null && loc !== undefined)
            .sort((a: any, b: any) => a.name.localeCompare(b.name));
    } catch (error) {
        console.error('Error fetching locations:', error);
    }
};

const fetchEquipment = async () => {
    try {
        const response = await axios.get('/rods', { params: { per_page: 1000 } });
        const data = response.data.data || response.data;
        equipment.value = (Array.isArray(data) ? data : [])
            .filter((eq: any) => eq !== null && eq !== undefined)
            .sort((a: any, b: any) => a.rod_name.localeCompare(b.rod_name));
    } catch (error) {
        console.error('Error fetching equipment:', error);
    }
};

const fetchFish = async () => {
    try {
        const response = await axios.get('/fish', { params: { per_page: 1000 } });
        const data = response.data.data || response.data;
        fishSpecies.value = (Array.isArray(data) ? data : [])
            .filter((fish: any) => fish !== null && fish !== undefined)
            .sort((a: any, b: any) => a.species.localeCompare(b.species));
    } catch (error) {
        console.error('Error fetching fish:', error);
    }
};

const fetchFlies = async () => {
    try {
        const response = await axios.get('/flies', { params: { per_page: 1000 } });
        const data = response.data.data || response.data;
        flies.value = (Array.isArray(data) ? data : [])
            .filter((fly: any) => fly !== null && fly !== undefined)
            .sort((a: any, b: any) => a.name.localeCompare(b.name));
    } catch (error) {
        console.error('Error fetching flies:', error);
    }
};

const fetchFriends = async () => {
    try {
        const response = await axios.get('/friends', { params: { per_page: 1000 } });
        const data = response.data.data || response.data;
        friends.value = (Array.isArray(data) ? data : [])
            .filter((friend: any) => friend !== null && friend !== undefined)
            .sort((a: any, b: any) => a.name.localeCompare(b.name));
    } catch (error) {
        console.error('Error fetching friends:', error);
    }
};

// Handle location form success
const handleLocationSuccess = (location: any) => {
    locations.value.push(location);
    formData.value.user_location_id = location.id.toString();
};

// Create new fish
const createFish = async () => {
    fishError.value = '';
    try {
        const response = await axios.post('/fish', newFish.value);
        fishSpecies.value.push(response.data);
        formData.value.user_fish_id = response.data.id.toString();
        newFish.value = { species: '', water_type: '' };
        showFishModal.value = false;
    } catch (error: any) {
        fishError.value = error.response?.data?.message || 'An error occurred while creating the fish species.';
    }
};

// Create new fly
const createFly = async () => {
    flyError.value = '';
    try {
        const response = await axios.post('/flies', newFly.value);
        flies.value.push(response.data);
        formData.value.user_fly_id = response.data.id.toString();
        newFly.value = { name: '', color: '', size: '', type: '' };
        showFlyModal.value = false;
    } catch (error: any) {
        flyError.value = error.response?.data?.message || 'An error occurred while creating the fly.';
    }
};

// Create new equipment
const createEquipment = async () => {
    equipmentError.value = '';
    try {
        const response = await axios.post('/rods', newEquipment.value);
        equipment.value.push(response.data);
        formData.value.user_rod_id = response.data.id.toString();
        newEquipment.value = { rod_name: '', rod_weight: '', rod_length: '', reel: '', line: '' };
        showEquipmentModal.value = false;
    } catch (error: any) {
        equipmentError.value = error.response?.data?.message || 'An error occurred while creating the rod.';
    }
};

// Create new friend
const createFriend = async () => {
    friendError.value = '';
    try {
        const response = await axios.post('/friends', newFriend.value);
        friends.value.push(response.data);
        formData.value.friend_ids.push(response.data.id);
        newFriend.value = { name: '' };
        showFriendModal.value = false;
    } catch (error: any) {
        friendError.value = error.response?.data?.message || 'An error occurred while creating the friend.';
    }
};

// Handle form submission
const handleSubmit = () => {
    clearFormErrors();

    const submitData = {
        date: formattedDate.value,
        time: formData.value.time || null,
        user_location_id: formData.value.user_location_id ? parseInt(formData.value.user_location_id) : null,
        user_fish_id: formData.value.user_fish_id ? parseInt(formData.value.user_fish_id) : null,
        quantity: formData.value.quantity ? parseInt(formData.value.quantity) : null,
        max_size: formData.value.maxSize ? parseFloat(formData.value.maxSize) : null,
        max_weight: formData.value.maxWeight ? parseFloat(formData.value.maxWeight) : null,
        avg_size: formData.value.avgSize ? parseFloat(formData.value.avgSize) : null,
        avg_weight: formData.value.avgWeight ? parseFloat(formData.value.avgWeight) : null,
        user_fly_id: formData.value.user_fly_id ? parseInt(formData.value.user_fly_id) : null,
        user_rod_id: formData.value.user_rod_id ? parseInt(formData.value.user_rod_id) : null,
        style: formData.value.fishingStyle || null,
        moon_phase: formData.value.moonPhase || null,
        moon_position: formData.value.moonPosition || null,
        time_of_day: formData.value.timeOfDay || null,
        friend_ids: formData.value.friend_ids,
        notes: formData.value.notes || null,
        weather: formData.value.weather,
        water_condition: formData.value.water_condition,
    };

    // Cache weather, water conditions, and friends by date+location for quick re-entry
    console.log('handleSubmit - checking cache conditions:', {
        date: formattedDate.value,
        locationId: formData.value.user_location_id,
        weather: formData.value.weather,
        water_condition: formData.value.water_condition,
        friend_ids: formData.value.friend_ids
    });

    if (formattedDate.value && formData.value.user_location_id) {
        setCachedData(formattedDate.value, formData.value.user_location_id, {
            weather: { ...formData.value.weather },
            water_condition: { ...formData.value.water_condition },
            friend_ids: [...formData.value.friend_ids],
        });
    }

    emit('submit', submitData);
};

// Handle cancel
const handleCancel = () => {
    emit('cancel');
};

// Watch for initialData changes
watch(() => props.initialData, (newData) => {
    if (newData && props.mode === 'edit') {
        populateForm(newData);
    }
}, { immediate: true });

// Watch for time changes to recalculate time of day and moon position
watch(() => formData.value.time, () => {
    recalculateTimeOfDay();
    recalculateMoonPosition();
});

// Watch for location changes to recalculate time of day and moon position
watch(() => formData.value.user_location_id, () => {
    recalculateTimeOfDay();
    recalculateMoonPosition();
});

// Apply cached form data (weather, water conditions, friends) when date+location match
// Clears fields first, then applies cached data if available
const applyCachedFormData = () => {
    // Only apply cache in create mode
    if (props.mode !== 'create') return;

    const date = dateInput.value;
    const locationId = formData.value.user_location_id;

    console.log('applyCachedFormData called:', { date, locationId });

    if (!date || !locationId) return;

    // Clear the fields first when date/location changes
    formData.value.weather = {
        temperature: '',
        cloud: '',
        wind: '',
        precipitation: '',
        barometric_pressure: '',
    };
    formData.value.water_condition = {
        temperature: '',
        clarity: '',
        level: '',
        speed: '',
        surface_condition: '',
        tide: '',
    };
    formData.value.friend_ids = [];

    // Then apply cached data if it exists for this date+location
    const cached = getCachedData(date, locationId);
    console.log('Cache GET:', getCacheKey(date, locationId), cached);

    if (cached) {
        console.log('Applying cached data');
        formData.value.weather = { ...cached.weather };
        formData.value.water_condition = { ...cached.water_condition };
        formData.value.friend_ids = [...cached.friend_ids];
    }
};

// Watch for date changes to apply cached data
watch(() => dateInput.value, () => {
    applyCachedFormData();
});

// Watch for location changes to apply cached data
watch(() => formData.value.user_location_id, () => {
    applyCachedFormData();
});

// Load data on mount
onMounted(() => {
    if (props.mode === 'create') {
        initializeDateInput();
    }
    fetchLocations();
    fetchEquipment();
    fetchFish();
    fetchFlies();
    fetchFriends();
});

// Expose methods for parent components
defineExpose({
    resetForm,
    clearFormErrors,
    setErrors: (errors: Record<string, string[]>, message: string) => {
        formErrors.value = errors;
        formErrorMessage.value = message;
    },
});
</script>

<template>
    <div class="fishing-log-form">
        <!-- Error Alert -->
        <Alert v-if="formErrorMessage" variant="destructive" class="mb-4">
            <AlertCircle class="h-4 w-4" />
            <AlertTitle>Error</AlertTitle>
            <AlertDescription>
                <p>{{ formErrorMessage }}</p>
                <ul v-if="Object.keys(formErrors).length > 0" class="mt-2 list-disc list-inside">
                    <li v-for="(errors, field) in formErrors" :key="field">
                        <strong>{{ field }}:</strong> {{ errors.join(', ') }}
                    </li>
                </ul>
            </AlertDescription>
        </Alert>

        <form @submit.prevent="handleSubmit" class="space-y-6">
            <!-- Date and Time -->
            <div class="grid grid-cols-2 gap-4">
                <div class="grid gap-2">
                    <Label for="date" class="flex items-center gap-2">
                        <CalendarIcon class="h-4 w-4" />
                        Date
                    </Label>
                    <Popover v-model:open="datePickerOpen">
                        <PopoverTrigger as-child>
                            <Button
                                id="date"
                                variant="outline"
                                class="w-full justify-between text-left font-normal"
                                :class="{ 'text-muted-foreground': !dateInput }"
                            >
                                <span>{{ dateInput ? new Date(dateInput + 'T00:00:00').toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : 'Pick a date' }}</span>
                                <CalendarIcon class="h-4 w-4 opacity-50" />
                            </Button>
                        </PopoverTrigger>
                        <PopoverContent class="w-auto p-0" align="start">
                            <Calendar
                                class="min-w-[280px]"
                                :model-value="selectedDate"
                                @update:model-value="handleCalendarSelect"
                                :max-value="maxDate"
                                layout="month-and-year"
                            />
                        </PopoverContent>
                    </Popover>
                </div>

                <div class="grid gap-2">
                    <Label for="time" class="flex items-center gap-2">
                        <Clock class="h-4 w-4" />
                        Time
                    </Label>
                    <Popover v-model:open="timePickerOpen">
                        <PopoverTrigger as-child>
                            <Button
                                id="time"
                                variant="outline"
                                class="w-full justify-between text-left font-normal"
                                :class="{ 'text-muted-foreground': !formData.time }"
                            >
                                <span>{{ formatTimeDisplay(formData.time) }}</span>
                                <Clock class="h-4 w-4 opacity-50" />
                            </Button>
                        </PopoverTrigger>
                        <PopoverContent class="w-44 p-0" align="start" @interact-outside="timePickerOpen = false">
                            <div class="flex h-48">
                                <!-- Hours column -->
                                <div class="flex flex-col border-r border-border flex-1">
                                    <div class="py-1 text-xs font-medium text-muted-foreground text-center border-b border-border bg-muted/50">Hr</div>
                                    <div class="overflow-y-auto flex-1">
                                        <button
                                            v-for="h in 12"
                                            :key="h"
                                            type="button"
                                            class="w-full py-1 text-xs hover:bg-accent hover:text-accent-foreground text-center"
                                            :class="{ 'bg-primary text-primary-foreground hover:bg-primary hover:text-primary-foreground': selectedHour === h }"
                                            @click="selectedHour = h; updateTimeFromPicker()"
                                        >
                                            {{ h }}
                                        </button>
                                    </div>
                                </div>
                                <!-- Minutes column (5-minute increments) -->
                                <div class="flex flex-col border-r border-border flex-1">
                                    <div class="py-1 text-xs font-medium text-muted-foreground text-center border-b border-border bg-muted/50">Min</div>
                                    <div class="overflow-y-auto flex-1">
                                        <button
                                            v-for="m in [0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55]"
                                            :key="m"
                                            type="button"
                                            class="w-full py-1 text-xs hover:bg-accent hover:text-accent-foreground text-center"
                                            :class="{ 'bg-primary text-primary-foreground hover:bg-primary hover:text-primary-foreground': selectedMinute === m }"
                                            @click="selectedMinute = m; updateTimeFromPicker()"
                                        >
                                            {{ String(m).padStart(2, '0') }}
                                        </button>
                                    </div>
                                </div>
                                <!-- AM/PM column -->
                                <div class="flex flex-col flex-1">
                                    <div class="py-1 text-xs font-medium text-muted-foreground text-center border-b border-border bg-muted/50">AM/PM</div>
                                    <div class="flex flex-col">
                                        <button
                                            type="button"
                                            class="py-1.5 text-xs hover:bg-accent hover:text-accent-foreground text-center"
                                            :class="{ 'bg-primary text-primary-foreground hover:bg-primary hover:text-primary-foreground': selectedPeriod === 'AM' }"
                                            @click="selectedPeriod = 'AM'; updateTimeFromPicker()"
                                        >
                                            AM
                                        </button>
                                        <button
                                            type="button"
                                            class="py-1.5 text-xs hover:bg-accent hover:text-accent-foreground text-center"
                                            :class="{ 'bg-primary text-primary-foreground hover:bg-primary hover:text-primary-foreground': selectedPeriod === 'PM' }"
                                            @click="selectedPeriod = 'PM'; updateTimeFromPicker()"
                                        >
                                            PM
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </PopoverContent>
                    </Popover>
                </div>
            </div>

            <!-- Location -->
            <div class="grid gap-2">
                <Label for="location" class="flex items-center gap-2">
                    <MapPin class="h-4 w-4" />
                    Location
                </Label>
                <div class="flex gap-2">
                    <Select v-model="formData.user_location_id" @update:open="(open: boolean) => { if (open) { timePickerOpen = false; datePickerOpen = false; } }">
                        <SelectTrigger id="location" class="flex-1">
                            <SelectValue placeholder="Select a location" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="location in locations"
                                :key="location.id"
                                :value="location.id.toString()"
                            >
                                {{ location.name }}
                                <span v-if="location.city" class="text-muted-foreground">
                                    - {{ location.city }}
                                </span>
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <Button type="button" variant="outline" size="icon" @click="showLocationModal = true">
                        <Plus class="h-4 w-4" />
                    </Button>
                </div>
            </div>

            <!-- Fish Species -->
            <div class="grid gap-2">
                <Label for="fish" class="flex items-center gap-2">
                    <Fish class="h-4 w-4" />
                    Fish Species
                </Label>
                <div class="flex gap-2">
                    <Select v-model="formData.user_fish_id" @update:open="(open: boolean) => { if (open) { timePickerOpen = false; datePickerOpen = false; } }">
                        <SelectTrigger id="fish" class="flex-1">
                            <SelectValue placeholder="Select fish species" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="fish in fishSpecies"
                                :key="fish.id"
                                :value="fish.id.toString()"
                            >
                                {{ fish.species }}
                                <span v-if="fish.water_type" class="text-muted-foreground">
                                    - {{ fish.water_type }}
                                </span>
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <Button type="button" variant="outline" size="icon" @click="showFishModal = true">
                        <Plus class="h-4 w-4" />
                    </Button>
                </div>
            </div>

            <!-- Quantity, Max Size, and Max Weight -->
            <div class="grid gap-4 sm:grid-cols-3">
                <div class="grid gap-2">
                    <Label for="quantity">Quantity Caught</Label>
                    <Input
                        id="quantity"
                        type="number"
                        v-model="formData.quantity"
                        placeholder="e.g., 5"
                        min="0"
                    />
                </div>
                <div class="grid gap-2">
                    <Label for="maxSize">Max Size (inches)</Label>
                    <Input
                        id="maxSize"
                        type="number"
                        v-model="formData.maxSize"
                        placeholder="e.g., 18"
                        min="0"
                        step="0.1"
                    />
                </div>
                <div class="grid gap-2">
                    <Label for="maxWeight">Max Weight (lbs)</Label>
                    <Input
                        id="maxWeight"
                        type="number"
                        v-model="formData.maxWeight"
                        placeholder="e.g., 2.5"
                        min="0"
                        step="0.01"
                    />
                </div>
            </div>

            <!-- Avg Size and Avg Weight (only shown when quantity > 1) -->
            <div v-if="parseInt(formData.quantity) > 1" class="grid gap-4 sm:grid-cols-2">
                <div class="grid gap-2">
                    <Label for="avgSize">Avg Size (inches)</Label>
                    <Input
                        id="avgSize"
                        type="number"
                        v-model="formData.avgSize"
                        placeholder="e.g., 12"
                        min="0"
                        step="0.1"
                    />
                    <p class="text-xs text-muted-foreground">Average size of {{ parseInt(formData.quantity) - 1 }} other fish</p>
                </div>
                <div class="grid gap-2">
                    <Label for="avgWeight">Avg Weight (lbs)</Label>
                    <Input
                        id="avgWeight"
                        type="number"
                        v-model="formData.avgWeight"
                        placeholder="e.g., 1.5"
                        min="0"
                        step="0.01"
                    />
                    <p class="text-xs text-muted-foreground">Average weight of {{ parseInt(formData.quantity) - 1 }} other fish</p>
                </div>
            </div>

            <!-- Fly -->
            <div class="grid gap-2">
                <Label for="fly">Fly Used</Label>
                <div class="flex gap-2">
                    <Select v-model="formData.user_fly_id" @update:open="(open: boolean) => { if (open) { timePickerOpen = false; datePickerOpen = false; } }">
                        <SelectTrigger id="fly" class="flex-1">
                            <SelectValue placeholder="Select a fly" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="fly in flies"
                                :key="fly.id"
                                :value="fly.id.toString()"
                            >
                                {{ fly.name }}
                                <span v-if="fly.size || fly.color" class="text-muted-foreground">
                                    - <span v-if="fly.size">Size {{ fly.size }}</span>
                                    <span v-if="fly.size && fly.color">, </span>
                                    <span v-if="fly.color">{{ fly.color }}</span>
                                </span>
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <Button type="button" variant="outline" size="icon" @click="showFlyModal = true">
                        <Plus class="h-4 w-4" />
                    </Button>
                </div>
            </div>

            <!-- Rod -->
            <div class="grid gap-2">
                <Label for="equipment">Rod</Label>
                <div class="flex gap-2">
                    <Select v-model="formData.user_rod_id" @update:open="(open: boolean) => { if (open) { timePickerOpen = false; datePickerOpen = false; } }">
                        <SelectTrigger id="equipment" class="flex-1">
                            <SelectValue placeholder="Select rod" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="item in equipment"
                                :key="item.id"
                                :value="item.id.toString()"
                            >
                                {{ item.rod_name }}
                                <span v-if="item.rod_weight" class="text-muted-foreground">
                                    - {{ item.rod_weight }}
                                </span>
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <Button type="button" variant="outline" size="icon" @click="showEquipmentModal = true">
                        <Plus class="h-4 w-4" />
                    </Button>
                </div>
            </div>

            <!-- Fishing Style -->
            <div class="grid gap-2">
                <Label for="fishingStyle">Fishing Style</Label>
                <Input
                    id="fishingStyle"
                    v-model="formData.fishingStyle"
                    placeholder="e.g., Dry Fly, Nymphing, Streamer"
                />
            </div>

            <!-- Moon Phase, Moon Position, and Time of Day -->
            <div class="grid grid-cols-3 gap-4">
                <div class="grid gap-2">
                    <Label for="moonPhase">Moon Phase</Label>
                    <Select v-model="formData.moonPhase" @update:open="(open: boolean) => { if (open) { timePickerOpen = false; datePickerOpen = false; } }">
                        <SelectTrigger id="moonPhase">
                            <SelectValue placeholder="Select moon phase" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="phase in moonPhaseOptions" :key="phase" :value="phase">
                                {{ phase }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div class="grid gap-2">
                    <Label for="moonPosition">Moon Position</Label>
                    <Select v-model="formData.moonPosition" @update:open="(open: boolean) => { if (open) { timePickerOpen = false; datePickerOpen = false; } }">
                        <SelectTrigger id="moonPosition">
                            <SelectValue placeholder="Select moon position" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="pos in moonPositionOptions" :key="pos" :value="pos">
                                {{ pos }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div class="grid gap-2">
                    <Label for="timeOfDay">Time of Day</Label>
                    <Select v-model="formData.timeOfDay" @update:open="(open: boolean) => { if (open) { timePickerOpen = false; datePickerOpen = false; } }">
                        <SelectTrigger id="timeOfDay">
                            <SelectValue placeholder="Select time of day" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="tod in timeOfDayOptions" :key="tod" :value="tod">
                                {{ tod }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>

            <!-- Weather and Water Conditions -->
            <div class="grid grid-cols-2 gap-4">
                <div class="grid gap-2">
                    <Label class="flex items-center gap-2">
                        <Cloud class="h-4 w-4" />
                        Weather Conditions
                    </Label>
                    <Button
                        type="button"
                        variant="outline"
                        class="justify-start overflow-hidden"
                        @click="showWeatherModal = true"
                    >
                        <span v-if="hasWeatherData" class="text-foreground truncate">
                            {{ weatherSummary }}
                        </span>
                        <span v-else class="flex items-center gap-1 text-muted-foreground">
                            <Plus class="h-4 w-4" />
                            Add weather conditions...
                        </span>
                    </Button>
                </div>
                <div class="grid gap-2">
                    <Label class="flex items-center gap-2">
                        <Droplets class="h-4 w-4" />
                        Water Conditions
                    </Label>
                    <Button
                        type="button"
                        variant="outline"
                        class="justify-start overflow-hidden"
                        @click="showWaterConditionModal = true"
                    >
                        <span v-if="hasWaterConditionData" class="text-foreground truncate">
                            {{ waterConditionSummary }}
                        </span>
                        <span v-else class="flex items-center gap-1 text-muted-foreground">
                            <Plus class="h-4 w-4" />
                            Add water conditions...
                        </span>
                    </Button>
                </div>
            </div>

            <!-- Friends -->
            <div class="grid gap-2">
                <Label>Fishing Partners</Label>
                <div class="flex gap-2">
                    <Popover>
                        <PopoverTrigger as-child>
                            <Button variant="outline" class="flex-1 justify-between">
                                <span v-if="formData.friend_ids.length === 0" class="text-muted-foreground">
                                    Select partners...
                                </span>
                                <span v-else>
                                    {{ formData.friend_ids.length }} partner{{ formData.friend_ids.length === 1 ? '' : 's' }} selected
                                </span>
                                <ChevronDown class="h-4 w-4 opacity-50 shrink-0" />
                            </Button>
                        </PopoverTrigger>
                        <PopoverContent class="w-[300px] p-0">
                            <div class="p-2 space-y-1">
                                <div
                                    v-for="friend in friends"
                                    :key="friend.id"
                                    class="flex items-center gap-2 p-2 rounded hover:bg-accent cursor-pointer"
                                    @click="formData.friend_ids.includes(friend.id)
                                        ? formData.friend_ids.splice(formData.friend_ids.indexOf(friend.id), 1)
                                        : formData.friend_ids.push(friend.id)"
                                >
                                    <Checkbox :model-value="formData.friend_ids.includes(friend.id)" @click.stop />
                                    <span class="text-sm">{{ friend.name }}</span>
                                </div>
                                <div v-if="friends.length === 0" class="p-2 text-sm text-muted-foreground text-center">
                                    No partners available
                                </div>
                            </div>
                        </PopoverContent>
                    </Popover>
                    <Button type="button" variant="outline" size="icon" @click="showFriendModal = true">
                        <Plus class="h-4 w-4" />
                    </Button>
                </div>
                <!-- Selected partners displayed as removable badges -->
                <div v-if="formData.friend_ids.length > 0" class="flex flex-wrap gap-1.5">
                    <span
                        v-for="friendId in formData.friend_ids"
                        :key="friendId"
                        class="inline-flex items-center gap-1 bg-primary text-primary-foreground px-2 py-1 rounded-md text-xs"
                    >
                        {{ friends.find(f => f.id === friendId)?.name }}
                        <button
                            type="button"
                            class="hover:bg-primary-foreground/20 rounded-full p-0.5"
                            @click="formData.friend_ids.splice(formData.friend_ids.indexOf(friendId), 1)"
                        >
                            <X class="h-3 w-3" />
                        </button>
                    </span>
                </div>
            </div>

            <!-- Notes -->
            <div class="grid gap-2">
                <Label for="notes">Notes</Label>
                <Textarea
                    id="notes"
                    v-model="formData.notes"
                    placeholder="Additional details about your trip..."
                    class="min-h-[120px]"
                />
            </div>

            <!-- Footer Slot or Default Footer -->
            <slot name="footer">
                <div v-if="showFooter" class="flex justify-end gap-2">
                    <Button type="button" variant="outline" @click="handleCancel">
                        {{ cancelLabel }}
                    </Button>
                    <Button type="submit">
                        {{ computedSubmitLabel }}
                    </Button>
                </div>
            </slot>
        </form>

        <!-- Location Form Dialog -->
        <LocationFormDialog
            v-model:open="showLocationModal"
            @success="handleLocationSuccess"
        />

        <!-- Fish Modal -->
        <Dialog v-model:open="showFishModal">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Add New Fish Species</DialogTitle>
                    <DialogDescription>Create a new fish species to add to your log.</DialogDescription>
                </DialogHeader>
                <Alert v-if="fishError" variant="destructive" class="mb-4">
                    <AlertCircle class="h-4 w-4" />
                    <AlertDescription>{{ fishError }}</AlertDescription>
                </Alert>
                <form @submit.prevent="createFish" class="space-y-4">
                    <div class="grid gap-2">
                        <Label for="new-fish-species">Species *</Label>
                        <Input id="new-fish-species" v-model="newFish.species" placeholder="e.g., Rainbow Trout" required />
                    </div>
                    <div class="grid gap-2">
                        <Label for="new-fish-water-type">Water Type</Label>
                        <Input id="new-fish-water-type" v-model="newFish.water_type" placeholder="e.g., Freshwater, Saltwater" />
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="showFishModal = false">Cancel</Button>
                        <Button type="submit">Add Fish Species</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Fly Modal -->
        <Dialog v-model:open="showFlyModal">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Add New Fly</DialogTitle>
                    <DialogDescription>Create a new fly pattern to add to your log.</DialogDescription>
                </DialogHeader>
                <Alert v-if="flyError" variant="destructive" class="mb-4">
                    <AlertCircle class="h-4 w-4" />
                    <AlertDescription>{{ flyError }}</AlertDescription>
                </Alert>
                <form @submit.prevent="createFly" class="space-y-4">
                    <div class="grid gap-2">
                        <Label for="new-fly-name">Fly Name *</Label>
                        <Input id="new-fly-name" v-model="newFly.name" placeholder="e.g., Woolly Bugger" required />
                    </div>
                    <div class="grid gap-2">
                        <Label for="new-fly-color">Color</Label>
                        <Input id="new-fly-color" v-model="newFly.color" placeholder="e.g., Olive" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="new-fly-size">Size</Label>
                        <Input id="new-fly-size" v-model="newFly.size" placeholder="e.g., #12" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="new-fly-type">Type</Label>
                        <Input id="new-fly-type" v-model="newFly.type" placeholder="e.g., Streamer, Dry Fly" />
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="showFlyModal = false">Cancel</Button>
                        <Button type="submit">Add Fly</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Rod Modal -->
        <Dialog v-model:open="showEquipmentModal">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Add New Rod</DialogTitle>
                    <DialogDescription>Create a new rod setup to add to your log.</DialogDescription>
                </DialogHeader>
                <Alert v-if="equipmentError" variant="destructive" class="mb-4">
                    <AlertCircle class="h-4 w-4" />
                    <AlertDescription>{{ equipmentError }}</AlertDescription>
                </Alert>
                <form @submit.prevent="createEquipment" class="space-y-4">
                    <div class="grid gap-2">
                        <Label for="new-equipment-rod-name">Rod Name *</Label>
                        <Input id="new-equipment-rod-name" v-model="newEquipment.rod_name" placeholder="e.g., Orvis Clearwater" required />
                    </div>
                    <div class="grid gap-2">
                        <Label for="new-equipment-rod-weight">Rod Weight</Label>
                        <Input id="new-equipment-rod-weight" v-model="newEquipment.rod_weight" placeholder="e.g., 5wt" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="new-equipment-reel">Reel</Label>
                        <Input id="new-equipment-reel" v-model="newEquipment.reel" placeholder="e.g., Orvis Battenkill" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="new-equipment-line">Line</Label>
                        <Input id="new-equipment-line" v-model="newEquipment.line" placeholder="e.g., WF5F" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="new-equipment-rod-length">Rod Length</Label>
                        <Input id="new-equipment-rod-length" v-model="newEquipment.rod_length" placeholder="e.g., 9ft" />
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="showEquipmentModal = false">Cancel</Button>
                        <Button type="submit">Add Rod</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Friend Modal -->
        <Dialog v-model:open="showFriendModal">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Add New Friend</DialogTitle>
                    <DialogDescription>Add a fishing buddy to your contacts.</DialogDescription>
                </DialogHeader>
                <Alert v-if="friendError" variant="destructive" class="mb-4">
                    <AlertCircle class="h-4 w-4" />
                    <AlertDescription>{{ friendError }}</AlertDescription>
                </Alert>
                <form @submit.prevent="createFriend" class="space-y-4">
                    <div class="grid gap-2">
                        <Label for="new-friend-name">Friend's Name *</Label>
                        <Input id="new-friend-name" v-model="newFriend.name" placeholder="e.g., John Doe" required />
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="showFriendModal = false">Cancel</Button>
                        <Button type="submit">Add Friend</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Weather Conditions Modal -->
        <Dialog v-model:open="showWeatherModal">
            <DialogContent class="max-w-md">
                <DialogHeader>
                    <DialogTitle>Weather Conditions</DialogTitle>
                    <DialogDescription>Record the weather conditions during your fishing trip.</DialogDescription>
                </DialogHeader>
                <div class="space-y-4">
                    <div class="grid gap-2">
                        <Label for="weather-temperature">Air Temperature</Label>
                        <Input
                            id="weather-temperature"
                            v-model="formData.weather.temperature"
                            placeholder="e.g., 72°F or 22°C"
                        />
                    </div>
                    <div class="grid gap-2">
                        <Label for="weather-cloud">Cloud Cover</Label>
                        <Select v-model="formData.weather.cloud" @update:open="(open: boolean) => { if (open) { timePickerOpen = false; datePickerOpen = false; } }">
                            <SelectTrigger id="weather-cloud">
                                <SelectValue placeholder="Select cloud cover" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="option in cloudOptions" :key="option" :value="option">
                                    {{ option }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="grid gap-2">
                        <Label for="weather-wind">Wind</Label>
                        <Select v-model="formData.weather.wind" @update:open="(open: boolean) => { if (open) { timePickerOpen = false; datePickerOpen = false; } }">
                            <SelectTrigger id="weather-wind">
                                <SelectValue placeholder="Select wind conditions" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="option in windOptions" :key="option" :value="option">
                                    {{ option }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="grid gap-2">
                        <Label for="weather-precipitation">Precipitation</Label>
                        <Select v-model="formData.weather.precipitation" @update:open="(open: boolean) => { if (open) { timePickerOpen = false; datePickerOpen = false; } }">
                            <SelectTrigger id="weather-precipitation">
                                <SelectValue placeholder="Select precipitation" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="option in precipitationOptions" :key="option" :value="option">
                                    {{ option }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="grid gap-2">
                        <Label for="weather-pressure">Barometric Pressure</Label>
                        <Input
                            id="weather-pressure"
                            v-model="formData.weather.barometric_pressure"
                            placeholder="e.g., 30.12 inHg or 1020 hPa"
                        />
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="showWeatherModal = false">Done</Button>
                    </DialogFooter>
                </div>
            </DialogContent>
        </Dialog>

        <!-- Water Conditions Modal -->
        <Dialog v-model:open="showWaterConditionModal">
            <DialogContent class="max-w-md">
                <DialogHeader>
                    <DialogTitle>Water Conditions</DialogTitle>
                    <DialogDescription>Record the water conditions during your fishing trip.</DialogDescription>
                </DialogHeader>
                <div class="space-y-4">
                    <div class="grid gap-2">
                        <Label for="water-temperature">Water Temperature</Label>
                        <Input
                            id="water-temperature"
                            v-model="formData.water_condition.temperature"
                            placeholder="e.g., 55°F or 13°C"
                        />
                    </div>
                    <div class="grid gap-2">
                        <Label for="water-clarity">Clarity</Label>
                        <Select v-model="formData.water_condition.clarity" @update:open="(open: boolean) => { if (open) { timePickerOpen = false; datePickerOpen = false; } }">
                            <SelectTrigger id="water-clarity">
                                <SelectValue placeholder="Select water clarity" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="option in clarityOptions" :key="option" :value="option">
                                    {{ option }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="grid gap-2">
                        <Label for="water-level">Water Level</Label>
                        <Select v-model="formData.water_condition.level" @update:open="(open: boolean) => { if (open) { timePickerOpen = false; datePickerOpen = false; } }">
                            <SelectTrigger id="water-level">
                                <SelectValue placeholder="Select water level" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="option in levelOptions" :key="option" :value="option">
                                    {{ option }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="grid gap-2">
                        <Label for="water-speed">Current Speed</Label>
                        <Select v-model="formData.water_condition.speed" @update:open="(open: boolean) => { if (open) { timePickerOpen = false; datePickerOpen = false; } }">
                            <SelectTrigger id="water-speed">
                                <SelectValue placeholder="Select current speed" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="option in speedOptions" :key="option" :value="option">
                                    {{ option }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="grid gap-2">
                        <Label for="water-surface">Surface Condition</Label>
                        <Select v-model="formData.water_condition.surface_condition" @update:open="(open: boolean) => { if (open) { timePickerOpen = false; datePickerOpen = false; } }">
                            <SelectTrigger id="water-surface">
                                <SelectValue placeholder="Select surface condition" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="option in surfaceConditionOptions" :key="option" :value="option">
                                    {{ option }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="grid gap-2">
                        <Label for="water-tide">Tide (if applicable)</Label>
                        <Select v-model="formData.water_condition.tide" @update:open="(open: boolean) => { if (open) { timePickerOpen = false; datePickerOpen = false; } }">
                            <SelectTrigger id="water-tide">
                                <SelectValue placeholder="Select tide" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="option in tideOptions" :key="option" :value="option">
                                    {{ option }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="showWaterConditionModal = false">Done</Button>
                    </DialogFooter>
                </div>
            </DialogContent>
        </Dialog>
    </div>
</template>
