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

import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Pagination, PaginationContent, PaginationItem, PaginationNext, PaginationPrevious } from '@/components/ui/pagination';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import { Fish, MapPin, Calendar as CalendarIcon, Plus, Pencil, Trash2, ChevronDown, X, FileText } from 'lucide-vue-next';
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

// Delete confirmation
const showDeleteConfirm = ref(false);
const logToDelete = ref(null);

// Notes modal
const showNotesModal = ref(false);
const selectedNotes = ref('');

// Fishing logs data
const fishingLogs = ref([]);

// Pagination state
const currentPage = ref(1);
const totalPages = ref(1);
const perPage = ref(15);
const total = ref(0);

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
const newEquipment = ref({ rod_name: '', rod_weight: '', reel: '', line: '', tippet: '' });
const newFriend = ref({ name: '' });

// Fetch fishing logs
const fetchFishingLogs = async (page = 1) => {
    try {
        const response = await axios.get('/fishing-logs', {
            params: {
                page: page,
                per_page: perPage.value
            }
        });
        fishingLogs.value = response.data.data;
        currentPage.value = response.data.current_page;
        totalPages.value = response.data.last_page;
        total.value = response.data.total;
    } catch (error) {
        console.error('Error fetching fishing logs:', error);
    }
};

// Pagination handlers
const goToPage = (page: number) => {
    if (page >= 1 && page <= totalPages.value) {
        fetchFishingLogs(page);
    }
};

const nextPage = () => {
    if (currentPage.value < totalPages.value) {
        fetchFishingLogs(currentPage.value + 1);
    }
};

const previousPage = () => {
    if (currentPage.value > 1) {
        fetchFishingLogs(currentPage.value - 1);
    }
};

// Fetch data from API
const fetchLocations = async () => {
    try {
        const response = await axios.get('/locations');
        // Handle paginated response
        const data = response.data.data || response.data;
        const locationsArray = Array.isArray(data) ? data : [];
        locations.value = locationsArray.filter((loc: any) => loc !== null && loc !== undefined);
    } catch (error) {
        console.error('Error fetching locations:', error);
    }
};

const fetchEquipment = async () => {
    try {
        const response = await axios.get('/equipment');
        // Handle paginated response
        const data = response.data.data || response.data;
        const equipmentArray = Array.isArray(data) ? data : [];
        equipment.value = equipmentArray.filter((eq: any) => eq !== null && eq !== undefined);
    } catch (error) {
        console.error('Error fetching equipment:', error);
    }
};

const fetchFish = async () => {
    try {
        const response = await axios.get('/fish');
        // Handle paginated response
        const data = response.data.data || response.data;
        const fishArray = Array.isArray(data) ? data : [];
        fishSpecies.value = fishArray.filter((fish: any) => fish !== null && fish !== undefined);
    } catch (error) {
        console.error('Error fetching fish:', error);
    }
};

const fetchFlies = async () => {
    try {
        const response = await axios.get('/flies');
        // Handle paginated response
        const data = response.data.data || response.data;
        const fliesArray = Array.isArray(data) ? data : [];
        flies.value = fliesArray.filter((fly: any) => fly !== null && fly !== undefined);
    } catch (error) {
        console.error('Error fetching flies:', error);
    }
};

const fetchFriends = async () => {
    try {
        const response = await axios.get('/friends');
        // Handle paginated response
        const data = response.data.data || response.data;
        const friendsArray = Array.isArray(data) ? data : [];
        friends.value = friendsArray.filter((friend: any) => friend !== null && friend !== undefined);
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
        newEquipment.value = { rod_name: '', rod_weight: '', reel: '', line: '', tippet: '' };
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

// Open add form
const openAddForm = () => {
    resetForm();
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

// Open delete confirmation dialog
const confirmDelete = (log: any) => {
    logToDelete.value = log;
    showDeleteConfirm.value = true;
};

// Handle delete action
const handleDelete = async () => {
    if (!logToDelete.value) return;

    try {
        await axios.delete(`/fishing-logs/${logToDelete.value.id}`);

        // Refresh fishing logs
        await fetchFishingLogs();

        // Close dialog and reset
        showDeleteConfirm.value = false;
        logToDelete.value = null;
    } catch (error) {
        console.error('Error deleting fishing log:', error);
    }
};

// Cancel delete
const cancelDelete = () => {
    showDeleteConfirm.value = false;
    logToDelete.value = null;
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
        } else {
            // Create new log
            response = await axios.post('/fishing-logs', submitData);
            console.log('Fishing log created:', response.data);
        }

        // Reset form
        resetForm();

        // Refresh fishing logs and close dialog
        await fetchFishingLogs();
        showAddForm.value = false;
    } catch (error) {
        console.error('Error saving fishing log:', error);
    }
};

// Format date for display
const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};

// Format size for display (remove .0 decimals)
const formatSize = (size: number) => {
    const num = parseFloat(size.toString());
    return num % 1 === 0 ? Math.floor(num).toString() : num.toString();
};

// Open notes modal
const viewNotes = (notes: string) => {
    selectedNotes.value = notes;
    showNotesModal.value = true;
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
                            <Button as="a" href="/fishing-log/create" class="flex items-center gap-2">
                                <Plus class="h-4 w-4" />
                                Add New Log
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent class="p-6">
                        <!-- Desktop Table View -->
                        <div class="hidden lg:block rounded-md border">
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
                                        <TableHead>Notes</TableHead>
                                        <TableHead class="text-right">Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-if="fishingLogs.length === 0">
                                        <TableCell colspan="9" class="text-center text-muted-foreground py-8">
                                            No fishing logs yet. Click "Add New" to create your first log!
                                        </TableCell>
                                    </TableRow>
                                    <TableRow v-for="log in fishingLogs" :key="log.id">
                                        <TableCell class="font-medium">{{ formatDate(log.date) }}</TableCell>
                                        <TableCell>{{ log.location?.name || '-' }}</TableCell>
                                        <TableCell>{{ log.fish?.species || '-' }}</TableCell>
                                        <TableCell>{{ log.quantity || '-' }}</TableCell>
                                        <TableCell>{{ log.max_size ? `${formatSize(log.max_size)}"` : '-' }}</TableCell>
                                        <TableCell>{{ log.fly?.name || '-' }}</TableCell>
                                        <TableCell>{{ log.style || '-' }}</TableCell>
                                        <TableCell>
                                            <Button
                                                v-if="log.notes"
                                                variant="ghost"
                                                size="icon"
                                                @click="viewNotes(log.notes)"
                                                class="h-8 w-8"
                                            >
                                                <FileText class="h-4 w-4" />
                                            </Button>
                                            <span v-else class="text-muted-foreground">-</span>
                                        </TableCell>
                                        <TableCell class="text-right">
                                            <div class="flex items-center justify-end gap-0">
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    @click="editLog(log)"
                                                    class="h-8 w-8"
                                                >
                                                    <Pencil class="h-4 w-4" />
                                                </Button>
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    @click="confirmDelete(log)"
                                                    class="h-8 w-8 text-destructive hover:text-destructive -mr-2"
                                                >
                                                    <Trash2 class="h-4 w-4" />
                                                </Button>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>

                        <!-- Mobile Card View -->
                        <div class="lg:hidden space-y-3">
                            <div v-if="fishingLogs.length === 0" class="text-center text-muted-foreground py-8 border rounded-md">
                                No fishing logs yet. Click "Add New" to create your first log!
                            </div>
                            <Card v-for="log in fishingLogs" :key="log.id" class="overflow-hidden">
                                <CardContent class="p-4">
                                    <div class="space-y-3">
                                        <!-- Header with Date and Actions -->
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="flex-1">
                                                <div class="font-semibold text-base">{{ formatDate(log.date) }}</div>
                                                <div class="text-sm text-muted-foreground">{{ log.location?.name || 'No location' }}</div>
                                            </div>
                                            <div class="flex gap-1">
                                                <Button variant="ghost" size="icon-sm" @click="editLog(log)">
                                                    <Pencil class="h-4 w-4" />
                                                </Button>
                                                <Button variant="ghost" size="icon-sm" @click="confirmDelete(log)"
                                                    class="text-destructive hover:text-destructive">
                                                    <Trash2 class="h-4 w-4" />
                                                </Button>
                                            </div>
                                        </div>

                                        <!-- Details Grid -->
                                        <div class="grid grid-cols-2 gap-2 text-sm">
                                            <div>
                                                <span class="text-muted-foreground">Fish:</span>
                                                <span class="ml-1">{{ log.fish?.species || '-' }}</span>
                                            </div>
                                            <div>
                                                <span class="text-muted-foreground">Quantity:</span>
                                                <span class="ml-1">{{ log.quantity || '-' }}</span>
                                            </div>
                                            <div>
                                                <span class="text-muted-foreground">Max Size:</span>
                                                <span class="ml-1">{{ log.max_size ? `${formatSize(log.max_size)}"` : '-' }}</span>
                                            </div>
                                            <div>
                                                <span class="text-muted-foreground">Fly:</span>
                                                <span class="ml-1">{{ log.fly?.name || '-' }}</span>
                                            </div>
                                            <div class="col-span-2">
                                                <span class="text-muted-foreground">Style:</span>
                                                <span class="ml-1">{{ log.style || '-' }}</span>
                                            </div>
                                        </div>

                                        <!-- Notes Button -->
                                        <div v-if="log.notes" class="pt-1">
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                @click="viewNotes(log.notes)"
                                                class="w-full flex items-center justify-center gap-2"
                                            >
                                                <FileText class="h-4 w-4" />
                                                View Notes
                                            </Button>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                    </CardContent>
                </Card>

                <!-- Pagination -->
                <div v-if="totalPages > 1" class="mt-4 flex items-center justify-between">
                            <div class="text-sm text-muted-foreground">
                                Showing {{ ((currentPage - 1) * perPage) + 1 }} to {{ Math.min(currentPage * perPage, total) }} of {{ total }} entries
                            </div>
                            <Pagination :total="totalPages" :sibling-count="1" show-edges :default-page="1" v-model:page="currentPage" @update:page="goToPage">
                                <PaginationContent>
                                    <PaginationItem>
                                        <PaginationPrevious @click="previousPage" :disabled="currentPage === 1" />
                                    </PaginationItem>

                                    <PaginationItem v-for="page in totalPages" :key="page">
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            @click="goToPage(page)"
                                            :class="{ 'bg-accent dark:bg-accent dark:border-border': page === currentPage }"
                                            class="h-9 w-9"
                                        >
                                            {{ page }}
                                        </Button>
                                    </PaginationItem>

                                    <PaginationItem>
                                        <PaginationNext @click="nextPage" :disabled="currentPage === totalPages" />
                                    </PaginationItem>
                                </PaginationContent>
                            </Pagination>
                </div>
            </div>
        </div>

        <!-- Add/Edit Log Dialog -->
        <Dialog v-model:open="showAddForm">
            <DialogContent class="max-w-5xl max-h-[90vh] overflow-y-auto">
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
                                                        class="px-3"
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

        <!-- Delete Confirmation Dialog -->
        <Dialog v-model:open="showDeleteConfirm">
            <DialogContent class="max-w-md">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2 text-destructive">
                        <Trash2 class="h-5 w-5" />
                        Delete Fishing Log
                    </DialogTitle>
                    <DialogDescription>
                        Are you sure you want to delete this fishing log? This action cannot be undone.
                    </DialogDescription>
                </DialogHeader>
                <div v-if="logToDelete" class="py-4">
                    <div class="rounded-lg bg-muted p-4 space-y-2">
                        <p class="text-sm font-medium">Log Details:</p>
                        <p class="text-sm text-muted-foreground">
                            <strong>Date:</strong> {{ formatDate(logToDelete.date) }}
                        </p>
                        <p class="text-sm text-muted-foreground" v-if="logToDelete.location">
                            <strong>Location:</strong> {{ logToDelete.location.name }}
                        </p>
                        <p class="text-sm text-muted-foreground" v-if="logToDelete.fish">
                            <strong>Fish:</strong> {{ logToDelete.fish.species }}
                        </p>
                    </div>
                </div>
                <DialogFooter>
                    <Button type="button" variant="outline" @click="cancelDelete">
                        Cancel
                    </Button>
                    <Button type="button" variant="destructive" @click="handleDelete">
                        Delete
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Notes Modal -->
        <Dialog v-model:open="showNotesModal">
            <DialogContent class="max-w-2xl">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2">
                        <FileText class="h-5 w-5" />
                        Trip Notes
                    </DialogTitle>
                    <DialogDescription>
                        Notes from this fishing trip
                    </DialogDescription>
                </DialogHeader>
                <div class="py-4">
                    <div class="rounded-lg border bg-muted/50 p-4">
                        <p class="whitespace-pre-wrap text-sm">{{ selectedNotes }}</p>
                    </div>
                </div>
                <DialogFooter>
                    <Button type="button" @click="showNotesModal = false">
                        Close
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>





