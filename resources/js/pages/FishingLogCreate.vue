<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Calendar as CalendarComponent } from '@/components/ui/calendar';
import { Checkbox } from '@/components/ui/checkbox';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import { Fish, MapPin, Calendar as CalendarIcon, Plus, ArrowLeft, ChevronDown, X } from 'lucide-vue-next';
import axios from '@/lib/axios';
import { CalendarDate, DateFormatter, getLocalTimeZone, today } from '@internationalized/date';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Fishing Log',
        href: '/fishing-log',
    },
    {
        title: 'Add New Log',
        href: '/fishing-log/create',
    },
];

// Date picker state
const selectedDate = ref(today(getLocalTimeZone()));
const dateInput = ref('');
const df = new DateFormatter('en-US', { dateStyle: 'long' });
const isCalendarOpen = ref(false);

// Form data
const formData = ref({
    location_id: '',
    fish_id: '',
    quantity: '',
    maxSize: '',
    fly_id: '',
    equipment_id: '',
    fishingStyle: '',
    friend_ids: [] as number[],
    notes: '',
});

// Computed formatted date
const formattedDate = computed(() => {
    if (!selectedDate.value) return '';
    const { year, month, day } = selectedDate.value;
    return `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
});

// Initialize date input with today's date
const initializeDateInput = () => {
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    dateInput.value = `${year}-${month}-${day}`;
    selectedDate.value = new CalendarDate(year, parseInt(month), parseInt(day));
};

// Handle date input change
const handleInputChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const [year, month, day] = target.value.split('-').map(Number);
    selectedDate.value = new CalendarDate(year, month, day);
};

// Handle calendar date selection
const handleDateSelect = (date: CalendarDate) => {
    if (date) {
        selectedDate.value = date;
        const { year, month, day } = date;
        dateInput.value = `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        isCalendarOpen.value = false; // Close the calendar popover
    }
};

// Dynamic data from API
const locations = ref([]);
const fishSpecies = ref([]);
const flies = ref([]);
const equipment = ref([]);
const friends = ref([]);

// Modal states
const showLocationModal = ref(false);
const showFishModal = ref(false);
const showFlyModal = ref(false);
const showEquipmentModal = ref(false);
const showFriendModal = ref(false);

// New item forms
const newLocation = ref({ name: '', city: '', state: '', country: '' });
const newFish = ref({ species: '', water_type: '' });
const newFly = ref({ name: '', color: '', size: '', type: '' });
const newEquipment = ref({ rod_name: '', rod_weight: '', rod_length: '', reel: '', line: '' });
const newFriend = ref({ name: '' });

// Fetch data from API
const fetchLocations = async () => {
    try {
        const response = await axios.get('/locations', {
            params: { per_page: 1000 }
        });
        // Handle paginated response
        const data = response.data.data || response.data;
        const locationsArray = Array.isArray(data) ? data : [];
        locations.value = locationsArray
            .filter((loc: any) => loc !== null && loc !== undefined)
            .sort((a: any, b: any) => a.name.localeCompare(b.name));
    } catch (error) {
        console.error('Error fetching locations:', error);
    }
};

const fetchEquipment = async () => {
    try {
        const response = await axios.get('/rods', {
            params: { per_page: 1000 }
        });
        // Handle paginated response
        const data = response.data.data || response.data;
        const equipmentArray = Array.isArray(data) ? data : [];
        equipment.value = equipmentArray
            .filter((eq: any) => eq !== null && eq !== undefined)
            .sort((a: any, b: any) => a.rod_name.localeCompare(b.rod_name));
    } catch (error) {
        console.error('Error fetching equipment:', error);
    }
};

const fetchFish = async () => {
    try {
        const response = await axios.get('/fish', {
            params: { per_page: 1000 }
        });
        // Handle paginated response
        const data = response.data.data || response.data;
        const fishArray = Array.isArray(data) ? data : [];
        fishSpecies.value = fishArray
            .filter((fish: any) => fish !== null && fish !== undefined)
            .sort((a: any, b: any) => a.species.localeCompare(b.species));
    } catch (error) {
        console.error('Error fetching fish:', error);
    }
};

const fetchFlies = async () => {
    try {
        const response = await axios.get('/flies', {
            params: { per_page: 1000 }
        });
        // Handle paginated response
        const data = response.data.data || response.data;
        const fliesArray = Array.isArray(data) ? data : [];
        flies.value = fliesArray
            .filter((fly: any) => fly !== null && fly !== undefined)
            .sort((a: any, b: any) => a.name.localeCompare(b.name));
    } catch (error) {
        console.error('Error fetching flies:', error);
    }
};

const fetchFriends = async () => {
    try {
        const response = await axios.get('/friends', {
            params: { per_page: 1000 }
        });
        // Handle paginated response
        const data = response.data.data || response.data;
        const friendsArray = Array.isArray(data) ? data : [];
        friends.value = friendsArray
            .filter((friend: any) => friend !== null && friend !== undefined)
            .sort((a: any, b: any) => a.name.localeCompare(b.name));
    } catch (error) {
        console.error('Error fetching friends:', error);
    }
};

// Load all data on mount
onMounted(() => {
    initializeDateInput();
    fetchLocations();
    fetchEquipment();
    fetchFish();
    fetchFlies();
    fetchFriends();
});

// Create new location
const createLocation = async () => {
    try {
        const response = await axios.post('/locations', newLocation.value);
        locations.value.push(response.data);
        formData.value.location_id = response.data.id.toString();
        newLocation.value = { name: '', city: '', state: '', country: '' };
        showLocationModal.value = false;
    } catch (error) {
        console.error('Error creating location:', error);
    }
};

// Create new fish
const createFish = async () => {
    try {
        const response = await axios.post('/fish', newFish.value);
        fishSpecies.value.push(response.data);
        formData.value.fish_id = response.data.id.toString();
        newFish.value = { species: '', water_type: '' };
        showFishModal.value = false;
    } catch (error) {
        console.error('Error creating fish:', error);
    }
};

// Create new fly
const createFly = async () => {
    try {
        const response = await axios.post('/flies', newFly.value);
        flies.value.push(response.data);
        formData.value.fly_id = response.data.id.toString();
        newFly.value = { name: '', color: '', size: '', type: '' };
        showFlyModal.value = false;
    } catch (error) {
        console.error('Error creating fly:', error);
    }
};

// Create new equipment
const createEquipment = async () => {
    try {
        const response = await axios.post('/rods', newEquipment.value);
        equipment.value.push(response.data);
        formData.value.equipment_id = response.data.id.toString();
        newEquipment.value = { rod_name: '', rod_weight: '', rod_length: '', reel: '', line: '' };
        showEquipmentModal.value = false;
    } catch (error) {
        console.error('Error creating equipment:', error);
    }
};

// Create new friend
const createFriend = async () => {
    try {
        const response = await axios.post('/friends', newFriend.value);
        friends.value.push(response.data);
        formData.value.friend_ids.push(response.data.id);
        newFriend.value = { name: '' };
        showFriendModal.value = false;
    } catch (error) {
        console.error('Error creating friend:', error);
    }
};

// Handle form submission
const handleSubmit = async () => {
    try {
        const submitData = {
            date: formattedDate.value,
            location_id: formData.value.location_id ? parseInt(formData.value.location_id) : null,
            fish_id: formData.value.fish_id ? parseInt(formData.value.fish_id) : null,
            quantity: formData.value.quantity ? parseInt(formData.value.quantity) : null,
            max_size: formData.value.maxSize ? parseFloat(formData.value.maxSize) : null,
            fly_id: formData.value.fly_id ? parseInt(formData.value.fly_id) : null,
            equipment_id: formData.value.equipment_id ? parseInt(formData.value.equipment_id) : null,
            style: formData.value.fishingStyle || null,
            friend_ids: formData.value.friend_ids,
            notes: formData.value.notes || null,
        };

        const response = await axios.post('/fishing-logs', submitData);
        console.log('Fishing log created:', response.data);

        // Navigate back to fishing log page
        router.visit('/fishing-log');
    } catch (error) {
        console.error('Error saving fishing log:', error);
    }
};

// Cancel and go back
const handleCancel = () => {
    router.visit('/fishing-log');
};
</script>

<template>
    <Head title="Add Fishing Log" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="mx-auto w-full max-w-4xl">
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div>
                                <CardTitle class="flex items-center gap-2">
                                    <Fish class="h-6 w-6" />
                                    Log Your Fishing Trip
                                </CardTitle>
                                <CardDescription>
                                    Record details about your fishing adventure
                                </CardDescription>
                            </div>
                            <Button variant="outline" @click="handleCancel" class="flex items-center gap-2">
                                <ArrowLeft class="h-4 w-4" />
                                Back to Logs
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="handleSubmit" class="space-y-6">
                            <!-- Date -->
                            <div class="grid gap-2">
                                <Label for="date" class="flex items-center gap-2">
                                    <CalendarIcon class="h-4 w-4" />
                                    Date
                                </Label>
                                <div class="flex gap-2">
                                    <Input
                                        id="date"
                                        type="date"
                                        v-model="dateInput"
                                        @change="handleInputChange"
                                        class="flex-1"
                                    />
                                    <Popover v-model:open="isCalendarOpen">
                                        <PopoverTrigger as-child>
                                            <Button variant="outline" class="px-3">
                                                <CalendarIcon class="h-4 w-4" />
                                            </Button>
                                        </PopoverTrigger>
                                        <PopoverContent class="w-auto p-0">
                                            <CalendarComponent v-model="selectedDate" @update:model-value="handleDateSelect" />
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
                                    <Select v-model="formData.location_id">
                                        <SelectTrigger id="location" class="flex-1">
                                            <SelectValue placeholder="Select a location" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="location in locations" :key="location.id" :value="location.id.toString()">
                                                {{ location.name }}
                                                <span v-if="location.city" class="text-muted-foreground">
                                                    - {{ location.city }}
                                                </span>
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <Button type="button" variant="outline" @click="showLocationModal = true">
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
                                    <Select v-model="formData.fish_id">
                                        <SelectTrigger id="fish" class="flex-1">
                                            <SelectValue placeholder="Select fish species" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="fish in fishSpecies" :key="fish.id" :value="fish.id.toString()">
                                                {{ fish.species }}
                                                <span v-if="fish.water_type" class="text-muted-foreground">
                                                    - {{ fish.water_type }}
                                                </span>
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <Button type="button" variant="outline" @click="showFishModal = true">
                                        <Plus class="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>

                            <!-- Quantity and Max Size -->
                            <div class="grid grid-cols-2 gap-4">
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
                                        step="0.1"
                                        v-model="formData.maxSize"
                                        placeholder="e.g., 18.5"
                                        min="0"
                                    />
                                </div>
                            </div>

                            <!-- Fly -->
                            <div class="grid gap-2">
                                <Label for="fly">Fly Used</Label>
                                <div class="flex gap-2">
                                    <Select v-model="formData.fly_id">
                                        <SelectTrigger id="fly" class="flex-1">
                                            <SelectValue placeholder="Select a fly" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="fly in flies" :key="fly.id" :value="fly.id.toString()">
                                                {{ fly.name }}
                                                <span v-if="fly.size || fly.color" class="text-muted-foreground">
                                                    - <span v-if="fly.size">Size {{ fly.size }}</span><span v-if="fly.size && fly.color">, </span><span v-if="fly.color">{{ fly.color }}</span>
                                                </span>
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <Button type="button" variant="outline" @click="showFlyModal = true">
                                        <Plus class="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>

                            <!-- Rod -->
                            <div class="grid gap-2">
                                <Label for="equipment">Rod</Label>
                                <div class="flex gap-2">
                                    <Select v-model="formData.equipment_id">
                                        <SelectTrigger id="equipment" class="flex-1">
                                            <SelectValue placeholder="Select rod" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="eq in equipment" :key="eq.id" :value="eq.id.toString()">
                                                {{ eq.rod_name }}
                                                <span v-if="eq.rod_weight" class="text-muted-foreground">
                                                    - {{ eq.rod_weight }}
                                                </span>
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <Button type="button" variant="outline" @click="showEquipmentModal = true">
                                        <Plus class="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>

                            <!-- Fishing Style -->
                            <div class="grid gap-2">
                                <Label for="fishingStyle">Fishing Style</Label>
                                <Input
                                    id="fishingStyle"
                                    type="text"
                                    v-model="formData.fishingStyle"
                                    placeholder="e.g., Dry Fly, Nymph, Streamer"
                                />
                            </div>

                            <!-- Friends -->
                            <div class="grid gap-2">
                                <Label>Friends</Label>
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
                                                            @click.stop="() => {
                                                                const index = formData.friend_ids.indexOf(friendId);
                                                                if (index > -1) {
                                                                    formData.friend_ids.splice(index, 1);
                                                                }
                                                            }"
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
                                                    @click="() => {
                                                        const index = formData.friend_ids.indexOf(friend.id);
                                                        if (index > -1) {
                                                            formData.friend_ids.splice(index, 1);
                                                        } else {
                                                            formData.friend_ids.push(friend.id);
                                                        }
                                                    }"
                                                >
                                                    <Checkbox
                                                        :model-value="formData.friend_ids.includes(friend.id)"
                                                        @click.stop
                                                    />
                                                    <span class="text-sm">{{ friend.name }}</span>
                                                </div>
                                                <div v-if="friends.length === 0" class="p-2 text-sm text-muted-foreground text-center">
                                                    No friends available
                                                </div>
                                            </div>
                                        </PopoverContent>
                                    </Popover>
                                    <Button type="button" variant="outline" @click="showFriendModal = true">
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
                                <Button type="button" variant="outline" @click="handleCancel">
                                    Cancel
                                </Button>
                                <Button type="submit">
                                    Save Fishing Log
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </div>

        <!-- Location Modal -->
        <Dialog v-model:open="showLocationModal">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Add New Location</DialogTitle>
                    <DialogDescription>
                        Create a new fishing location to add to your log.
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="createLocation" class="space-y-4">
                    <div class="grid gap-2">
                        <Label for="new-location-name">Location Name *</Label>
                        <Input
                            id="new-location-name"
                            v-model="newLocation.name"
                            placeholder="e.g., Yellowstone River"
                            required
                        />
                    </div>
                    <div class="grid gap-2">
                        <Label for="new-location-city">City</Label>
                        <Input
                            id="new-location-city"
                            v-model="newLocation.city"
                            placeholder="e.g., Livingston"
                        />
                    </div>
                    <div class="grid gap-2">
                        <Label for="new-location-state">State</Label>
                        <Input
                            id="new-location-state"
                            v-model="newLocation.state"
                            placeholder="e.g., Montana"
                        />
                    </div>
                    <div class="grid gap-2">
                        <Label for="new-location-country">Country</Label>
                        <Input
                            id="new-location-country"
                            v-model="newLocation.country"
                            placeholder="e.g., USA"
                        />
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="showLocationModal = false">
                            Cancel
                        </Button>
                        <Button type="submit">
                            Add Location
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Fish Modal -->
        <Dialog v-model:open="showFishModal">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Add New Fish Species</DialogTitle>
                    <DialogDescription>
                        Create a new fish species to add to your log.
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="createFish" class="space-y-4">
                    <div class="grid gap-2">
                        <Label for="new-fish-species">Species *</Label>
                        <Input
                            id="new-fish-species"
                            v-model="newFish.species"
                            placeholder="e.g., Rainbow Trout"
                            required
                        />
                    </div>
                    <div class="grid gap-2">
                        <Label for="new-fish-water-type">Water Type</Label>
                        <Input
                            id="new-fish-water-type"
                            v-model="newFish.water_type"
                            placeholder="e.g., Freshwater"
                        />
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="showFishModal = false">
                            Cancel
                        </Button>
                        <Button type="submit">
                            Add Fish
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Fly Modal -->
        <Dialog v-model:open="showFlyModal">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Add New Fly</DialogTitle>
                    <DialogDescription>
                        Create a new fly pattern to add to your log.
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="createFly" class="space-y-4">
                    <div class="grid gap-2">
                        <Label for="new-fly-name">Fly Name *</Label>
                        <Input
                            id="new-fly-name"
                            v-model="newFly.name"
                            placeholder="e.g., Woolly Bugger"
                            required
                        />
                    </div>
                    <div class="grid gap-2">
                        <Label for="new-fly-color">Color</Label>
                        <Input
                            id="new-fly-color"
                            v-model="newFly.color"
                            placeholder="e.g., Olive"
                        />
                    </div>
                    <div class="grid gap-2">
                        <Label for="new-fly-size">Size</Label>
                        <Input
                            id="new-fly-size"
                            v-model="newFly.size"
                            placeholder="e.g., #12"
                        />
                    </div>
                    <div class="grid gap-2">
                        <Label for="new-fly-type">Type</Label>
                        <Input
                            id="new-fly-type"
                            v-model="newFly.type"
                            placeholder="e.g., Streamer"
                        />
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="showFlyModal = false">
                            Cancel
                        </Button>
                        <Button type="submit">
                            Add Fly
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Rod Modal -->
        <Dialog v-model:open="showEquipmentModal">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Add New Rod</DialogTitle>
                    <DialogDescription>
                        Create a new rod setup to add to your log.
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="createEquipment" class="space-y-4">
                    <div class="grid gap-2">
                        <Label for="new-equipment-rod-name">Rod Name *</Label>
                        <Input
                            id="new-equipment-rod-name"
                            v-model="newEquipment.rod_name"
                            placeholder="e.g., Orvis Clearwater"
                            required
                        />
                    </div>
                    <div class="grid gap-2">
                        <Label for="new-equipment-rod-weight">Rod Weight</Label>
                        <Input
                            id="new-equipment-rod-weight"
                            v-model="newEquipment.rod_weight"
                            placeholder="e.g., 5wt"
                        />
                    </div>
                    <div class="grid gap-2">
                        <Label for="new-equipment-reel">Reel</Label>
                        <Input
                            id="new-equipment-reel"
                            v-model="newEquipment.reel"
                            placeholder="e.g., Orvis Battenkill"
                        />
                    </div>
                    <div class="grid gap-2">
                        <Label for="new-equipment-line">Line</Label>
                        <Input
                            id="new-equipment-line"
                            v-model="newEquipment.line"
                            placeholder="e.g., WF5F"
                        />
                    </div>
                    <div class="grid gap-2">
                        <Label for="new-equipment-rod-length">Rod Length</Label>
                        <Input
                            id="new-equipment-rod-length"
                            v-model="newEquipment.rod_length"
                            placeholder="e.g., 9ft"
                        />
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="showEquipmentModal = false">
                            Cancel
                        </Button>
                        <Button type="submit">
                            Add Rod
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Friend Modal -->
        <Dialog v-model:open="showFriendModal">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Add New Friend</DialogTitle>
                    <DialogDescription>
                        Add a friend to your fishing log.
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="createFriend" class="space-y-4">
                    <div class="grid gap-2">
                        <Label for="new-friend-name">Friend Name *</Label>
                        <Input
                            id="new-friend-name"
                            v-model="newFriend.name"
                            placeholder="e.g., John Doe"
                            required
                        />
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="showFriendModal = false">
                            Cancel
                        </Button>
                        <Button type="submit">
                            Add Friend
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>




