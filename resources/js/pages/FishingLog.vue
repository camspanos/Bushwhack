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
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import { Fish, MapPin, Calendar as CalendarIcon, Plus } from 'lucide-vue-next';
import axios from '@/lib/axios';
import { CalendarDate, DateFormatter, getLocalTimeZone, today } from '@internationalized/date';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Fishing Log',
        href: '/fishing-log',
    },
];

// Date picker state
const selectedDate = ref(today(getLocalTimeZone()));
const dateInput = ref('');
const df = new DateFormatter('en-US', { dateStyle: 'long' });

// Initialize date input with today's date
const initializeDateInput = () => {
    const todayDate = today(getLocalTimeZone());
    const year = todayDate.year;
    const month = String(todayDate.month).padStart(2, '0');
    const day = String(todayDate.day).padStart(2, '0');
    dateInput.value = `${year}-${month}-${day}`;
};

// Computed property to format date for form submission
const formattedDate = computed(() => {
    // Use the input field value if available, otherwise use selectedDate
    if (dateInput.value) return dateInput.value;
    if (!selectedDate.value) return '';
    const year = selectedDate.value.year;
    const month = String(selectedDate.value.month).padStart(2, '0');
    const day = String(selectedDate.value.day).padStart(2, '0');
    return `${year}-${month}-${day}`;
});

// Update input when calendar date is selected
const handleDateSelect = (date: CalendarDate | undefined) => {
    if (!date) return;
    selectedDate.value = date;
    const year = date.year;
    const month = String(date.month).padStart(2, '0');
    const day = String(date.day).padStart(2, '0');
    dateInput.value = `${year}-${month}-${day}`;
};

// Update calendar when input changes
const handleInputChange = () => {
    if (!dateInput.value) return;
    try {
        const [year, month, day] = dateInput.value.split('-').map(Number);
        if (year && month && day) {
            selectedDate.value = new CalendarDate(year, month, day);
        }
    } catch (error) {
        console.error('Invalid date format:', error);
    }
};

// Form state
const formData = ref({
    location_id: '',
    fish_id: '',
    quantity: '',
    maxSize: '',
    fly_id: '',
    equipment_id: '',
    fishingStyle: '',
    friend_ids: [],
    notes: '',
});

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
const newEquipment = ref({ rod_name: '', rod_weight: '', lure: '', line: '', tippet: '' });
const newFriend = ref({ name: '' });

// Fetch data from API
const fetchLocations = async () => {
    try {
        const response = await axios.get('/locations');
        locations.value = response.data;
    } catch (error) {
        console.error('Error fetching locations:', error);
    }
};

const fetchEquipment = async () => {
    try {
        const response = await axios.get('/equipment');
        equipment.value = response.data;
    } catch (error) {
        console.error('Error fetching equipment:', error);
    }
};

const fetchFish = async () => {
    try {
        const response = await axios.get('/fish');
        fishSpecies.value = response.data;
    } catch (error) {
        console.error('Error fetching fish:', error);
    }
};

const fetchFlies = async () => {
    try {
        const response = await axios.get('/flies');
        flies.value = response.data;
    } catch (error) {
        console.error('Error fetching flies:', error);
    }
};

const fetchFriends = async () => {
    try {
        const response = await axios.get('/friends');
        friends.value = response.data;
    } catch (error) {
        console.error('Error fetching friends:', error);
    }
};

// Create new items
const createLocation = async () => {
    try {
        const response = await axios.post('/locations', newLocation.value);
        locations.value.push(response.data);
        formData.value.location_id = response.data.id.toString();
        showLocationModal.value = false;
        newLocation.value = { name: '', city: '', state: '', country: '' };
    } catch (error) {
        console.error('Error creating location:', error);
    }
};

const createEquipment = async () => {
    try {
        const response = await axios.post('/equipment', newEquipment.value);
        equipment.value.push(response.data);
        formData.value.equipment_id = response.data.id.toString();
        showEquipmentModal.value = false;
        newEquipment.value = { rod_name: '', rod_weight: '', lure: '', line: '', tippet: '' };
    } catch (error) {
        console.error('Error creating equipment:', error);
    }
};

const createFish = async () => {
    try {
        const response = await axios.post('/fish', newFish.value);
        fishSpecies.value.push(response.data);
        formData.value.fish_id = response.data.id.toString();
        showFishModal.value = false;
        newFish.value = { species: '', water_type: '' };
    } catch (error) {
        console.error('Error creating fish:', error);
    }
};

const createFly = async () => {
    try {
        const response = await axios.post('/flies', newFly.value);
        flies.value.push(response.data);
        formData.value.fly_id = response.data.id.toString();
        showFlyModal.value = false;
        newFly.value = { name: '', color: '', size: '', type: '' };
    } catch (error) {
        console.error('Error creating fly:', error);
    }
};

const createFriend = async () => {
    try {
        const response = await axios.post('/friends', newFriend.value);
        friends.value.push(response.data);
        // Add the new friend to the selected friends array
        formData.value.friend_ids.push(response.data.id.toString());
        showFriendModal.value = false;
        newFriend.value = { name: '' };
    } catch (error) {
        console.error('Error creating friend:', error);
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

const handleSubmit = async () => {
    try {
        const submitData = {
            date: formattedDate.value,
            location_id: formData.value.location_id || null,
            fish_id: formData.value.fish_id || null,
            quantity: formData.value.quantity || null,
            max_size: formData.value.maxSize || null,
            fly_id: formData.value.fly_id || null,
            equipment_id: formData.value.equipment_id || null,
            style: formData.value.fishingStyle || null,
            friend_ids: formData.value.friend_ids,
            notes: formData.value.notes || null,
        };

        const response = await axios.post('/fishing-logs', submitData);
        console.log('Fishing log created:', response.data);

        // Reset form
        formData.value = {
            location_id: '',
            fish_id: '',
            quantity: '',
            maxSize: '',
            fly_id: '',
            equipment_id: '',
            fishingStyle: '',
            friend_ids: [],
            notes: '',
        };
        dateInput.value = '';

        // Show success message (you can add a toast notification here)
        alert('Fishing log created successfully!');
    } catch (error) {
        console.error('Error creating fishing log:', error);
        alert('Error creating fishing log. Please try again.');
    }
};
</script>

<template>
    <Head title="Fishing Log" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="mx-auto w-full max-w-4xl">
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Fish class="h-6 w-6" />
                            Log Your Fishing Trip
                        </CardTitle>
                        <CardDescription>
                            Record details about your fishing adventure
                        </CardDescription>
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
                                        required
                                        class="flex-1"
                                    />
                                    <Popover>
                                        <PopoverTrigger as-child>
                                            <Button
                                                type="button"
                                                variant="outline"
                                                size="icon"
                                            >
                                                <CalendarIcon class="h-4 w-4" />
                                            </Button>
                                        </PopoverTrigger>
                                        <PopoverContent class="w-auto p-0">
                                            <CalendarComponent
                                                v-model="selectedDate"
                                                initial-focus
                                                @update:model-value="handleDateSelect"
                                            />
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
                                    <Select v-model="formData.location_id" class="flex-1">
                                        <SelectTrigger id="location">
                                            <SelectValue placeholder="Select location" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem
                                                v-for="location in locations"
                                                :key="location.id"
                                                :value="location.id.toString()"
                                            >
                                                {{ location.name }}
                                                <span v-if="location.city" class="text-muted-foreground text-sm">
                                                    - {{ location.city }}
                                                </span>
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        size="icon"
                                        @click="showLocationModal = true"
                                    >
                                        <Plus class="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>

                            <!-- Fish Species -->
                            <div class="grid gap-2">
                                <Label for="species" class="flex items-center gap-2">
                                    <Fish class="h-4 w-4" />
                                    Fish Species
                                </Label>
                                <div class="flex gap-2">
                                    <Select v-model="formData.fish_id" class="flex-1">
                                        <SelectTrigger id="species">
                                            <SelectValue placeholder="Select species" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem
                                                v-for="fish in fishSpecies"
                                                :key="fish.id"
                                                :value="fish.id.toString()"
                                            >
                                                {{ fish.species }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        size="icon"
                                        @click="showFishModal = true"
                                    >
                                        <Plus class="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>

                            <!-- Quantity and Max Size -->
                            <div class="grid gap-4 md:grid-cols-2">
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
                                        placeholder="Largest fish"
                                        min="0"
                                        step="0.1"
                                    />
                                </div>
                            </div>

                            <!-- Fly and Rod -->
                            <div class="grid gap-4 md:grid-cols-2">
                                <div class="grid gap-2">
                                    <Label for="fly">Fly</Label>
                                    <div class="flex gap-2">
                                        <Select v-model="formData.fly_id" class="flex-1">
                                            <SelectTrigger id="fly">
                                                <SelectValue placeholder="Select fly" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem
                                                    v-for="fly in flies"
                                                    :key="fly.id"
                                                    :value="fly.id.toString()"
                                                >
                                                    {{ fly.name }}
                                                    <span v-if="fly.size || fly.color" class="text-muted-foreground text-sm">
                                                        - <span v-if="fly.size">Size {{ fly.size }}</span>
                                                        <span v-if="fly.size && fly.color">, </span>
                                                        <span v-if="fly.color">{{ fly.color }}</span>
                                                    </span>
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                        <Button
                                            type="button"
                                            variant="outline"
                                            size="icon"
                                            @click="showFlyModal = true"
                                        >
                                            <Plus class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </div>
                                <div class="grid gap-2">
                                    <Label for="rod">Rod/Equipment</Label>
                                    <div class="flex gap-2">
                                        <Select v-model="formData.equipment_id" class="flex-1">
                                            <SelectTrigger id="rod">
                                                <SelectValue placeholder="Select equipment" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem
                                                    v-for="equip in equipment"
                                                    :key="equip.id"
                                                    :value="equip.id.toString()"
                                                >
                                                    {{ equip.rod_name }}
                                                    <span v-if="equip.rod_weight" class="text-muted-foreground text-sm">
                                                        - {{ equip.rod_weight }}
                                                    </span>
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                        <Button
                                            type="button"
                                            variant="outline"
                                            size="icon"
                                            @click="showEquipmentModal = true"
                                        >
                                            <Plus class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </div>
                            </div>

                            <!-- Style of Fishing -->
                            <div class="grid gap-2">
                                <Label for="fishingStyle">Style of Fishing</Label>
                                <Input
                                    id="fishingStyle"
                                    v-model="formData.fishingStyle"
                                    placeholder="e.g., Dry fly, Nymphing, Streamer, Spin casting"
                                />
                            </div>

                            <!-- Fished With -->
                            <div class="grid gap-2">
                                <Label for="fishedWith">Fished With</Label>
                                <div class="flex gap-2">
                                    <Select v-model="formData.friend_ids" multiple class="flex-1">
                                        <SelectTrigger id="fishedWith">
                                            <SelectValue placeholder="Select friends (multiple)" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem
                                                v-for="friend in friends"
                                                :key="friend.id"
                                                :value="friend.id.toString()"
                                            >
                                                {{ friend.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        size="icon"
                                        @click="showFriendModal = true"
                                    >
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

                            <!-- Submit Button -->
                            <div class="flex justify-end gap-4">
                                <Button type="button" variant="outline">
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
                            placeholder="e.g., Lake Superior"
                            required
                        />
                    </div>
                    <div class="grid gap-2">
                        <Label for="new-location-city">City</Label>
                        <Input
                            id="new-location-city"
                            v-model="newLocation.city"
                            placeholder="e.g., Duluth"
                        />
                    </div>
                    <div class="grid gap-2">
                        <Label for="new-location-state">State</Label>
                        <Input
                            id="new-location-state"
                            v-model="newLocation.state"
                            placeholder="e.g., Minnesota"
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
                            placeholder="e.g., Freshwater, Saltwater"
                        />
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="showFishModal = false">
                            Cancel
                        </Button>
                        <Button type="submit">
                            Add Fish Species
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
                            placeholder="e.g., Nymph, Dry Fly, Streamer"
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

        <!-- Equipment Modal -->
        <Dialog v-model:open="showEquipmentModal">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Add New Equipment</DialogTitle>
                    <DialogDescription>
                        Create a new rod/equipment setup to add to your log.
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
                            placeholder="e.g., 5wt 9ft"
                        />
                    </div>
                    <div class="grid gap-2">
                        <Label for="new-equipment-lure">Lure</Label>
                        <Input
                            id="new-equipment-lure"
                            v-model="newEquipment.lure"
                            placeholder="e.g., Spinner"
                        />
                    </div>
                    <div class="grid gap-2">
                        <Label for="new-equipment-line">Line</Label>
                        <Input
                            id="new-equipment-line"
                            v-model="newEquipment.line"
                            placeholder="e.g., 5X Tippet"
                        />
                    </div>
                    <div class="grid gap-2">
                        <Label for="new-equipment-tippet">Tippet</Label>
                        <Input
                            id="new-equipment-tippet"
                            v-model="newEquipment.tippet"
                            placeholder="e.g., 6X"
                        />
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="showEquipmentModal = false">
                            Cancel
                        </Button>
                        <Button type="submit">
                            Add Equipment
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
                        Add a fishing buddy to your contacts.
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="createFriend" class="space-y-4">
                    <div class="grid gap-2">
                        <Label for="new-friend-name">Friend's Name *</Label>
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
