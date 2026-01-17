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

import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import { Fish, MapPin, Calendar as CalendarIcon, Plus, Pencil } from 'lucide-vue-next';
import axios from '@/lib/axios';
import { CalendarDate, DateFormatter, getLocalTimeZone, today } from '@internationalized/date';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Fishing Log',
        href: '/fishing-log',
    },
];

// Show/hide add form dialog
const showAddForm = ref(false);

// Edit mode
const editingLogId = ref(null);
const isEditMode = ref(false);

// Fishing logs data
const fishingLogs = ref([]);

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

// Fetch fishing logs
const fetchFishingLogs = async () => {
    try {
        const response = await axios.get('/fishing-logs');
        fishingLogs.value = response.data;
    } catch (error) {
        console.error('Error fetching fishing logs:', error);
    }
};

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
        formData.value.location_id = response.data.id;
        showLocationModal.value = false;
        newLocation.value = { name: '', city: '', state: '', country: '' };
    } catch (error) {
        console.error('Error creating location:', error);
    }
};

const createFish = async () => {
    try {
        const response = await axios.post('/fish', newFish.value);
        fishSpecies.value.push(response.data);
        formData.value.fish_id = response.data.id;
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
        formData.value.fly_id = response.data.id;
        showFlyModal.value = false;
        newFly.value = { name: '', color: '', size: '', type: '' };
    } catch (error) {
        console.error('Error creating fly:', error);
    }
};

const createEquipment = async () => {
    try {
        const response = await axios.post('/equipment', newEquipment.value);
        equipment.value.push(response.data);
        formData.value.equipment_id = response.data.id;
        showEquipmentModal.value = false;
        newEquipment.value = { rod_name: '', rod_weight: '', lure: '', line: '', tippet: '' };
    } catch (error) {
        console.error('Error creating equipment:', error);
    }
};

const createFriend = async () => {
    try {
        const response = await axios.post('/friends', newFriend.value);
        friends.value.push(response.data);
        showFriendModal.value = false;
        newFriend.value = { name: '' };
    } catch (error) {
        console.error('Error creating friend:', error);
    }
};

// Load all data on mount
onMounted(() => {
    initializeDateInput();
    fetchFishingLogs();
    fetchLocations();
    fetchEquipment();
    fetchFish();
    fetchFlies();
    fetchFriends();
});

// Open edit dialog with log data
const editLog = (log: any) => {
    isEditMode.value = true;
    editingLogId.value = log.id;

    // Parse the date
    const logDate = new Date(log.date);
    const year = logDate.getFullYear();
    const month = String(logDate.getMonth() + 1).padStart(2, '0');
    const day = String(logDate.getDate()).padStart(2, '0');
    dateInput.value = `${year}-${month}-${day}`;
    selectedDate.value = new CalendarDate(year, parseInt(month), parseInt(day));

    // Populate form with log data
    formData.value = {
        location_id: log.location_id?.toString() || '',
        fish_id: log.fish_id?.toString() || '',
        quantity: log.quantity?.toString() || '',
        maxSize: log.max_size?.toString() || '',
        fly_id: log.fly_id?.toString() || '',
        equipment_id: log.equipment_id?.toString() || '',
        fishingStyle: log.style || '',
        friend_ids: log.friends?.map((f: any) => f.id) || [],
        notes: log.notes || '',
    };

    showAddForm.value = true;
};

// Reset form to add mode
const resetForm = () => {
    isEditMode.value = false;
    editingLogId.value = null;
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
    initializeDateInput();
};

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

        let response;
        if (isEditMode.value && editingLogId.value) {
            // Update existing log
            response = await axios.put(`/fishing-logs/${editingLogId.value}`, submitData);
            console.log('Fishing log updated:', response.data);
            alert('Fishing log updated successfully!');
        } else {
            // Create new log
            response = await axios.post('/fishing-logs', submitData);
            console.log('Fishing log created:', response.data);
            alert('Fishing log created successfully!');
        }

        // Reset form
        resetForm();

        // Refresh fishing logs and close dialog
        await fetchFishingLogs();
        showAddForm.value = false;
    } catch (error) {
        console.error('Error saving fishing log:', error);
        alert('Error saving fishing log. Please try again.');
    }
};

// Format date for display
const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};


</script>

<template>
    <Head title="Fishing Log" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="mx-auto w-full max-w-6xl">
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div>
                                <CardTitle class="flex items-center gap-2">
                                    <Fish class="h-6 w-6" />
                                    Your Fishing Logs
                                </CardTitle>
                                <CardDescription>
                                    View and manage your fishing trip records
                                </CardDescription>
                            </div>
                            <Button @click="resetForm(); showAddForm = true;" class="flex items-center gap-2">
                                <Plus class="h-4 w-4" />
                                Add New Log
                            </Button>
                        </div>
                    </CardHeader>
                            <CardContent>
                                <div class="rounded-md border">
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>Date</TableHead>
                                                <TableHead>Location</TableHead>
                                                <TableHead>Fish</TableHead>
                                                <TableHead>Quantity</TableHead>
                                                <TableHead>Max Size</TableHead>
                                                <TableHead>Fly</TableHead>
                                                <TableHead>Style</TableHead>
                                                <TableHead class="text-right">Actions</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            <TableRow v-if="fishingLogs.length === 0">
                                                <TableCell colspan="8" class="text-center text-muted-foreground py-8">
                                                    No fishing logs yet. Click "Add New" to create your first log!
                                                </TableCell>
                                            </TableRow>
                                            <TableRow v-for="log in fishingLogs" :key="log.id">
                                                <TableCell class="font-medium">{{ formatDate(log.date) }}</TableCell>
                                                <TableCell>{{ log.location?.name || '-' }}</TableCell>
                                                <TableCell>{{ log.fish?.species || '-' }}</TableCell>
                                                <TableCell>{{ log.quantity || '-' }}</TableCell>
                                                <TableCell>{{ log.max_size ? `${log.max_size}"` : '-' }}</TableCell>
                                                <TableCell>{{ log.fly?.name || '-' }}</TableCell>
                                                <TableCell>{{ log.style || '-' }}</TableCell>
                                                <TableCell class="text-right">
                                                    <Button
                                                        variant="ghost"
                                                        size="icon"
                                                        @click="editLog(log)"
                                                        class="h-8 w-8"
                                                    >
                                                        <Pencil class="h-4 w-4" />
                                                    </Button>
                                                </TableCell>
                                            </TableRow>
                                        </TableBody>
                                    </Table>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>

        <!-- Add/Edit Log Dialog -->
        <Dialog v-model:open="showAddForm" @update:open="(open) => { if (!open) resetForm(); }">
            <DialogContent class="max-w-2xl max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2">
                        <Fish class="h-6 w-6" />
                        {{ isEditMode ? 'Edit Fishing Trip' : 'Log Your Fishing Trip' }}
                    </DialogTitle>
                    <DialogDescription>
                        {{ isEditMode ? 'Update details about your fishing adventure' : 'Record details about your fishing adventure' }}
                    </DialogDescription>
                </DialogHeader>
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
                                            <Select v-model="formData.location_id">
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
                                        <Label for="fish">Fish Species</Label>
                                        <div class="flex gap-2">
                                            <Select v-model="formData.fish_id">
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
                                            <Select v-model="formData.fly_id">
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

                                    <!-- Equipment -->
                                    <div class="grid gap-2">
                                        <Label for="equipment">Equipment</Label>
                                        <div class="flex gap-2">
                                            <Select v-model="formData.equipment_id">
                                                <SelectTrigger id="equipment" class="flex-1">
                                                    <SelectValue placeholder="Select equipment" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem
                                                        v-for="item in equipment"
                                                        :key="item.id"
                                                        :value="item.id.toString()"
                                                    >
                                                        {{ item.rod_name }}
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

                                    <!-- Fishing Style -->
                                    <div class="grid gap-2">
                                        <Label for="fishingStyle">Fishing Style</Label>
                                        <Input
                                            id="fishingStyle"
                                            v-model="formData.fishingStyle"
                                            placeholder="e.g., Dry Fly, Nymphing, Streamer"
                                        />
                                    </div>

                                    <!-- Friends -->
                                    <div class="grid gap-2">
                                        <Label>Fishing Buddies</Label>
                                        <div class="flex gap-2">
                                            <div class="flex-1 space-y-2">
                                                <div v-for="friend in friends" :key="friend.id" class="flex items-center space-x-2">
                                                    <input
                                                        type="checkbox"
                                                        :id="`friend-${friend.id}`"
                                                        :value="friend.id"
                                                        v-model="formData.friend_ids"
                                                        class="rounded border-gray-300"
                                                    />
                                                    <label :for="`friend-${friend.id}`" class="text-sm">
                                                        {{ friend.name }}
                                                    </label>
                                                </div>
                                            </div>
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
                                    <DialogFooter>
                                        <Button type="button" variant="outline" @click="showAddForm = false">
                                            Cancel
                                        </Button>
                                        <Button type="submit">
                                            {{ isEditMode ? 'Update Fishing Log' : 'Save Fishing Log' }}
                                        </Button>
                                    </DialogFooter>
                                </form>
            </DialogContent>
        </Dialog>

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
                            placeholder="e.g., Streamer, Dry Fly"
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
                        Create a new equipment setup to add to your log.
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
                            placeholder="e.g., WF5F"
                        />
                    </div>
                    <div class="grid gap-2">
                        <Label for="new-equipment-tippet">Tippet</Label>
                        <Input
                            id="new-equipment-tippet"
                            v-model="newEquipment.tippet"
                            placeholder="e.g., 5X"
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





