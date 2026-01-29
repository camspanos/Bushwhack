<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Checkbox } from '@/components/ui/checkbox';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import LocationFormDialog from '@/components/LocationFormDialog.vue';
import { Fish, MapPin, Calendar as CalendarIcon, Clock, Plus, ChevronDown, X, AlertCircle } from 'lucide-vue-next';
import { CalendarDate, getLocalTimeZone, today } from '@internationalized/date';
import axios from '@/lib/axios';

// Types
export interface FishingLogFormData {
    date: string;
    time: string;
    user_location_id: string;
    user_fish_id: string;
    quantity: string;
    maxSize: string;
    user_fly_id: string;
    user_rod_id: string;
    fishingStyle: string;
    moonPhase: string;
    barometricPressure: string;
    friend_ids: number[];
    notes: string;
}

export interface FishingLogInitialData {
    id?: number;
    date?: string;
    time?: string;
    user_location_id?: number;
    user_fish_id?: number;
    quantity?: number;
    max_size?: number;
    user_fly_id?: number;
    user_rod_id?: number;
    style?: string;
    moon_phase?: string;
    barometric_pressure?: string;
    friends?: { id: number; name: string }[];
    notes?: string;
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

// Form data
const formData = ref<FishingLogFormData>({
    date: '',
    time: '',
    user_location_id: '',
    user_fish_id: '',
    quantity: '',
    maxSize: '',
    user_fly_id: '',
    user_rod_id: '',
    fishingStyle: '',
    moonPhase: '',
    barometricPressure: '',
    friend_ids: [],
    notes: '',
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

// Barometric pressure options
const barometricPressureOptions = [
    'Falling Pressure',
    'Steady Pressure',
    'Rising Pressure',
];

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
        }
    } catch (error) {
        console.error('Invalid date format:', error);
    }
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

    formData.value = {
        date: dateInput.value,
        time: data.time ? data.time.substring(0, 5) : '',
        user_location_id: data.user_location_id?.toString() || '',
        user_fish_id: data.user_fish_id?.toString() || '',
        quantity: data.quantity?.toString() || '',
        maxSize: data.max_size?.toString() || '',
        user_fly_id: data.user_fly_id?.toString() || '',
        user_rod_id: data.user_rod_id?.toString() || '',
        fishingStyle: data.style || '',
        moonPhase: data.moon_phase || '',
        barometricPressure: data.barometric_pressure || '',
        friend_ids: data.friends?.map((f) => f.id) || [],
        notes: data.notes || '',
    };
};

// Clear form errors
const clearFormErrors = () => {
    formErrors.value = {};
    formErrorMessage.value = '';
};

// Reset form
const resetForm = () => {
    formData.value = {
        date: '',
        time: '',
        user_location_id: '',
        user_fish_id: '',
        quantity: '',
        maxSize: '',
        user_fly_id: '',
        user_rod_id: '',
        fishingStyle: '',
        moonPhase: '',
        barometricPressure: '',
        friend_ids: [],
        notes: '',
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
        user_fly_id: formData.value.user_fly_id ? parseInt(formData.value.user_fly_id) : null,
        user_rod_id: formData.value.user_rod_id ? parseInt(formData.value.user_rod_id) : null,
        style: formData.value.fishingStyle || null,
        moon_phase: formData.value.moonPhase || null,
        barometric_pressure: formData.value.barometricPressure || null,
        friend_ids: formData.value.friend_ids,
        notes: formData.value.notes || null,
    };

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
                    <Input
                        id="date"
                        type="date"
                        v-model="dateInput"
                        @change="handleInputChange"
                        required
                    />
                </div>

                <div class="grid gap-2">
                    <Label for="time" class="flex items-center gap-2">
                        <Clock class="h-4 w-4" />
                        Time
                    </Label>
                    <Input
                        id="time"
                        type="time"
                        v-model="formData.time"
                    />
                </div>
            </div>

            <!-- Location -->
            <div class="grid gap-2">
                <Label for="location" class="flex items-center gap-2">
                    <MapPin class="h-4 w-4" />
                    Location
                </Label>
                <div class="flex gap-2">
                    <Select v-model="formData.user_location_id">
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
                    <Select v-model="formData.user_fish_id">
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

            <!-- Quantity and Max Size -->
            <div class="grid gap-4 sm:grid-cols-2">
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
            </div>

            <!-- Fly -->
            <div class="grid gap-2">
                <Label for="fly">Fly Used</Label>
                <div class="flex gap-2">
                    <Select v-model="formData.user_fly_id">
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
                    <Select v-model="formData.user_rod_id">
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

            <!-- Moon Phase and Barometric Pressure -->
            <div class="grid grid-cols-2 gap-4">
                <div class="grid gap-2">
                    <Label for="moonPhase">Moon Phase</Label>
                    <Select v-model="formData.moonPhase">
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
                    <Label for="barometricPressure">Barometric Pressure</Label>
                    <Select v-model="formData.barometricPressure">
                        <SelectTrigger id="barometricPressure">
                            <SelectValue placeholder="Select pressure" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="pressure in barometricPressureOptions" :key="pressure" :value="pressure">
                                {{ pressure }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>

            <!-- Friends -->
            <div class="grid gap-2">
                <Label>Fishing Buddies</Label>
                <div class="flex gap-2">
                    <Popover>
                        <PopoverTrigger as-child>
                            <Button variant="outline" class="flex-1 justify-between">
                                <span v-if="formData.friend_ids.length === 0" class="text-muted-foreground">
                                    Select friends...
                                </span>
                                <span v-else class="flex flex-wrap gap-1">
                                    <span
                                        v-for="friendId in formData.friend_ids"
                                        :key="friendId"
                                        class="inline-flex items-center gap-1 bg-primary text-primary-foreground px-2 py-0.5 rounded text-xs"
                                    >
                                        {{ friends.find(f => f.id === friendId)?.name }}
                                        <X
                                            class="h-3 w-3 cursor-pointer hover:opacity-70"
                                            @click.stop="formData.friend_ids.splice(formData.friend_ids.indexOf(friendId), 1)"
                                        />
                                    </span>
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
                                    No friends available
                                </div>
                            </div>
                        </PopoverContent>
                    </Popover>
                    <Button type="button" variant="outline" size="icon" @click="showFriendModal = true">
                        <Plus class="h-4 w-4" />
                    </Button>
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
    </div>
</template>

