<script setup lang="ts">
import { ref, watch, computed, onMounted } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Checkbox } from '@/components/ui/checkbox';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Fish, MapPin, Calendar as CalendarIcon, Clock, Plus, ChevronDown, X, AlertCircle } from 'lucide-vue-next';
import axios from '@/lib/axios';

interface FishingLogFormData {
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

const props = defineProps<{
    editingLog?: any;
    locations: any[];
    fish: any[];
    flies: any[];
    equipment: any[];
    friends: any[];
    showCancelButton?: boolean;
}>();

const emit = defineEmits<{
    'success': [log: any];
    'cancel': [];
    'open-location-modal': [];
    'open-fish-modal': [];
    'open-fly-modal': [];
    'open-equipment-modal': [];
    'open-friend-modal': [];
}>();

const isEditMode = computed(() => !!props.editingLog?.id);
const formErrorMessage = ref('');
const dateInput = ref('');

// Get today's date in YYYY-MM-DD format for max date validation
const maxDate = computed(() => {
    const today = new Date();
    const year = today.getFullYear();
    const month = String(today.getMonth() + 1).padStart(2, '0');
    const day = String(today.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
});

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

const fishingStyleOptions = [
    'Dry Fly',
    'Nymph',
    'Streamer',
    'Wet Fly',
    'Emerger',
];

const formData = ref<FishingLogFormData>({
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

const resetForm = () => {
    formData.value = {
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
    dateInput.value = '';
    formErrorMessage.value = '';
};

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

const handleInputChange = () => {
    if (!dateInput.value) return;
    const [year, month, day] = dateInput.value.split('-').map(Number);
    if (year && month && day) {
        formData.value.moonPhase = calculateMoonPhase(year, month, day);
    }
};

const toggleFriend = (friendId: number) => {
    const index = formData.value.friend_ids.indexOf(friendId);
    if (index > -1) {
        formData.value.friend_ids.splice(index, 1);
    } else {
        formData.value.friend_ids.push(friendId);
    }
};

const removeFriend = (friendId: number) => {
    const index = formData.value.friend_ids.indexOf(friendId);
    if (index > -1) {
        formData.value.friend_ids.splice(index, 1);
    }
};

// Watch for editing log changes
watch(() => props.editingLog, (newLog) => {
    if (newLog) {
        dateInput.value = newLog.date || '';
        formData.value = {
            time: newLog.time ? newLog.time.substring(0, 5) : '',
            user_location_id: newLog.user_location_id?.toString() || '',
            user_fish_id: newLog.user_fish_id?.toString() || '',
            quantity: newLog.quantity?.toString() || '',
            maxSize: newLog.max_size?.toString() || '',
            user_fly_id: newLog.user_fly_id?.toString() || '',
            user_rod_id: newLog.user_rod_id?.toString() || '',
            fishingStyle: newLog.style || '',
            moonPhase: newLog.moon_phase || '',
            barometricPressure: newLog.barometric_pressure || '',
            friend_ids: newLog.friends?.map((f: any) => f.id) || [],
            notes: newLog.notes || '',
        };
    } else {
        resetForm();
    }
}, { immediate: true });

// Set today's date on mount if not in edit mode
onMounted(() => {
    if (!isEditMode.value && !dateInput.value) {
        const today = new Date();
        const year = today.getFullYear();
        const month = String(today.getMonth() + 1).padStart(2, '0');
        const day = String(today.getDate()).padStart(2, '0');
        dateInput.value = `${year}-${month}-${day}`;
        handleInputChange(); // Calculate moon phase for today
    }
});

const handleSubmit = async () => {
    formErrorMessage.value = '';
    try {
        const submitData = {
            date: dateInput.value,
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

        const url = isEditMode.value
            ? `/fishing-logs/${props.editingLog!.id}`
            : '/fishing-logs';
        const method = isEditMode.value ? 'put' : 'post';

        const response = await axios[method](url, submitData);
        emit('success', response.data);
        resetForm();
    } catch (error: any) {
        console.error('Error saving fishing log:', error);
        if (error.response?.data?.message) {
            formErrorMessage.value = error.response.data.message;
        } else if (error.response?.data?.errors) {
            const errors = error.response.data.errors;
            const firstError = Object.values(errors)[0];
            formErrorMessage.value = Array.isArray(firstError) ? firstError[0] : 'Validation error occurred.';
        } else {
            formErrorMessage.value = 'An error occurred while saving the fishing log.';
        }
    }
};

const handleCancel = () => {
    emit('cancel');
    resetForm();
};

defineExpose({
    resetForm,
});
</script>

<template>
    <!-- Error Alert -->
    <Alert v-if="formErrorMessage" variant="destructive" class="mb-4">
        <AlertCircle class="h-4 w-4" />
        <AlertDescription>{{ formErrorMessage }}</AlertDescription>
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
                    :max="maxDate"
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
            <Label class="flex items-center gap-2">
                <MapPin class="h-4 w-4" />
                Location
            </Label>
            <div class="flex gap-2">
                <Select v-model="formData.user_location_id" class="flex-1">
                    <SelectTrigger>
                        <SelectValue placeholder="Select location" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem v-for="location in locations" :key="location.id" :value="location.id.toString()">
                            {{ location.name }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <Button type="button" variant="outline" @click="$emit('open-location-modal')">
                    <Plus class="h-4 w-4" />
                </Button>
            </div>
        </div>

        <!-- Fish Species -->
        <div class="grid gap-2">
            <Label>Fish Species</Label>
            <div class="flex gap-2">
                <Select v-model="formData.user_fish_id" class="flex-1">
                    <SelectTrigger>
                        <SelectValue placeholder="Select fish species" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem v-for="fishItem in fish" :key="fishItem.id" :value="fishItem.id.toString()">
                            {{ fishItem.species }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <Button type="button" variant="outline" @click="$emit('open-fish-modal')">
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
                    placeholder="Number of fish"
                    min="0"
                />
            </div>

            <div class="grid gap-2">
                <Label for="maxSize">Max Size (inches)</Label>
                <Input
                    id="maxSize"
                    type="number"
                    v-model="formData.maxSize"
                    placeholder="Largest fish size"
                    step="0.1"
                    min="0"
                />
            </div>
        </div>

        <!-- Fly Pattern -->
        <div class="grid gap-2">
            <Label>Fly Pattern</Label>
            <div class="flex gap-2">
                <Select v-model="formData.user_fly_id" class="flex-1">
                    <SelectTrigger>
                        <SelectValue placeholder="Select fly pattern" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem v-for="fly in flies" :key="fly.id" :value="fly.id.toString()">
                            {{ fly.name }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <Button type="button" variant="outline" @click="$emit('open-fly-modal')">
                    <Plus class="h-4 w-4" />
                </Button>
            </div>
        </div>

        <!-- Rod -->
        <div class="grid gap-2">
            <Label>Rod</Label>
            <div class="flex gap-2">
                <Select v-model="formData.user_rod_id" class="flex-1">
                    <SelectTrigger>
                        <SelectValue placeholder="Select rod" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem v-for="rod in equipment" :key="rod.id" :value="rod.id.toString()">
                            {{ rod.rod_name }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <Button type="button" variant="outline" @click="$emit('open-equipment-modal')">
                    <Plus class="h-4 w-4" />
                </Button>
            </div>
        </div>

        <!-- Fishing Style and Moon Phase -->
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="grid gap-2">
                <Label for="fishingStyle">Fishing Style</Label>
                <Select v-model="formData.fishingStyle">
                    <SelectTrigger>
                        <SelectValue placeholder="Select fishing style" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem v-for="style in fishingStyleOptions" :key="style" :value="style">
                            {{ style }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <div class="grid gap-2">
                <Label for="moonPhase">Moon Phase</Label>
                <Select v-model="formData.moonPhase">
                    <SelectTrigger>
                        <SelectValue placeholder="Select moon phase" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem v-for="phase in moonPhaseOptions" :key="phase" :value="phase">
                            {{ phase }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>
        </div>

        <!-- Barometric Pressure -->
        <div class="grid gap-2">
            <Label for="barometricPressure">Barometric Pressure (inHg)</Label>
            <Input
                id="barometricPressure"
                type="number"
                v-model="formData.barometricPressure"
                placeholder="e.g., 29.92"
                step="0.01"
                min="0"
            />
        </div>

        <!-- Friends -->
        <div class="grid gap-2">
            <Label>Friends</Label>
            <div class="flex gap-2">
                <Popover>
                    <PopoverTrigger as-child>
                        <Button type="button" variant="outline" class="flex-1 justify-between">
                            <span v-if="formData.friend_ids.length === 0" class="text-muted-foreground">
                                Select friends
                            </span>
                            <span v-else>
                                {{ formData.friend_ids.length }} friend{{ formData.friend_ids.length !== 1 ? 's' : '' }} selected
                            </span>
                            <ChevronDown class="ml-2 h-4 w-4 opacity-50" />
                        </Button>
                    </PopoverTrigger>
                    <PopoverContent class="w-80 p-0">
                        <div class="max-h-60 overflow-y-auto">
                            <!-- Selected Friends -->
                            <div v-if="formData.friend_ids.length > 0" class="border-b p-2">
                                <div class="text-sm font-medium mb-2">Selected:</div>
                                <div class="flex flex-wrap gap-1">
                                    <div
                                        v-for="friendId in formData.friend_ids"
                                        :key="friendId"
                                        class="inline-flex items-center gap-1 bg-primary/10 text-primary px-2 py-1 rounded text-sm"
                                    >
                                        {{ friends.find(f => f.id === friendId)?.name }}
                                        <button
                                            type="button"
                                            @click="removeFriend(friendId)"
                                            class="hover:bg-primary/20 rounded-full p-0.5"
                                        >
                                            <X class="h-3 w-3" />
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Friend List -->
                            <div v-if="friends.length > 0" class="p-2">
                                <div
                                    v-for="friend in friends"
                                    :key="friend.id"
                                    @click="toggleFriend(friend.id)"
                                    class="flex items-center gap-2 p-2 hover:bg-accent rounded cursor-pointer"
                                >
                                    <Checkbox
                                        :checked="formData.friend_ids.includes(friend.id)"
                                        @click.stop="toggleFriend(friend.id)"
                                    />
                                    <span class="flex-1">{{ friend.name }}</span>
                                </div>
                            </div>
                            <div v-if="friends.length === 0" class="p-2 text-sm text-muted-foreground text-center">
                                No friends available
                            </div>
                        </div>
                    </PopoverContent>
                </Popover>
                <Button type="button" variant="outline" @click="$emit('open-friend-modal')">
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

        <!-- Submit Buttons -->
        <div class="flex justify-end gap-2">
            <Button v-if="showCancelButton" type="button" variant="outline" @click="handleCancel">
                Cancel
            </Button>
            <Button type="submit">
                {{ isEditMode ? 'Update Fishing Log' : 'Save Fishing Log' }}
            </Button>
        </div>
    </form>
</template>



