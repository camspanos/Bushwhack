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
import { NativeSelect, NativeSelectOption } from '@/components/ui/native-select';
import FishingLogFormDialog from '@/components/FishingLogFormDialog.vue';
import LocationFormDialog from '@/components/LocationFormDialog.vue';
import FishFormDialog from '@/components/FishFormDialog.vue';
import FlyFormDialog from '@/components/FlyFormDialog.vue';
import RodFormDialog from '@/components/RodFormDialog.vue';
import FriendFormDialog from '@/components/FriendFormDialog.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, computed, nextTick } from 'vue';
import { Fish, MapPin, Calendar as CalendarIcon, Clock, Plus, Pencil, Trash2, ChevronDown, X, FileText, AlertCircle, CheckCircle2, Trophy } from 'lucide-vue-next';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import axios from '@/lib/axios';
import { CalendarDate, DateFormatter, getLocalTimeZone, today } from '@internationalized/date';
import confetti from 'canvas-confetti';

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
const editingLog = ref(null);

// Delete confirmation
const showDeleteConfirm = ref(false);
const logToDelete = ref(null);

// Notes modal
const showNotesModal = ref(false);
const selectedNotes = ref('');

// Success notification
const showSuccessNotification = ref(false);
const newSpeciesName = ref('');
const showPersonalBestNotification = ref(false);
const personalBestSpecies = ref('');
const personalBestSize = ref(0);
const previousBestSize = ref(0);

// Fishing logs data
const fishingLogs = ref([]);

// Pagination state
const currentPage = ref(1);
const totalPages = ref(1);
const perPage = ref(25);
const total = ref(0);

// Date picker state
const selectedDate = ref(today(getLocalTimeZone()));
const dateInput = ref('');
const df = new DateFormatter('en-US', { dateStyle: 'long' });
const isCalendarOpen = ref(false);
let closeCalendar: (() => void) | null = null;

// Calculate moon phase based on date
const calculateMoonPhase = (year: number, month: number, day: number): string => {
    // Known new moon: January 6, 2000
    const knownNewMoon = new Date(2000, 0, 6, 18, 14);
    const targetDate = new Date(year, month - 1, day);

    // Lunar cycle is approximately 29.53 days
    const lunarCycle = 29.53058867;

    // Calculate days since known new moon
    const daysSinceNewMoon = (targetDate.getTime() - knownNewMoon.getTime()) / (1000 * 60 * 60 * 24);

    // Calculate position in current lunar cycle (0-29.53)
    const phase = ((daysSinceNewMoon % lunarCycle) + lunarCycle) % lunarCycle;

    // Determine moon phase name
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

// Initialize date input with today's date
const initializeDateInput = () => {
    const todayDate = today(getLocalTimeZone());
    const year = todayDate.year;
    const month = String(todayDate.month).padStart(2, '0');
    const day = String(todayDate.day).padStart(2, '0');
    dateInput.value = `${year}-${month}-${day}`;
    // Set moon phase for today
    formData.value.moonPhase = calculateMoonPhase(todayDate.year, todayDate.month, todayDate.day);
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

    // Automatically set moon phase
    formData.value.moonPhase = calculateMoonPhase(date.year, date.month, date.day);

    // Close the calendar popover
    isCalendarOpen.value = false;
    if (closeCalendar) {
        closeCalendar();
    }
};

// Update calendar when input changes
const handleInputChange = () => {
    if (!dateInput.value) return;
    try {
        const [year, month, day] = dateInput.value.split('-').map(Number);
        if (year && month && day) {
            selectedDate.value = new CalendarDate(year, month, day);
            // Automatically set moon phase
            formData.value.moonPhase = calculateMoonPhase(year, month, day);
        }
    } catch (error) {
        console.error('Invalid date format:', error);
    }
};

// Form state
const formData = ref({
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

// Form validation errors
const formErrors = ref<Record<string, string[]>>({});
const formErrorMessage = ref('');

// Check for new species notification on mount
const checkForNewSpecies = () => {
    const newSpecies = sessionStorage.getItem('newSpecies');
    if (newSpecies) {
        newSpeciesName.value = newSpecies;
        showSuccessNotification.value = true;
        triggerConfetti();

        // Clear from sessionStorage
        sessionStorage.removeItem('newSpecies');

        // Auto-hide notification after 20 seconds
        setTimeout(() => {
            showSuccessNotification.value = false;
        }, 20000);
    }

    // Check for personal best notification
    const personalBest = sessionStorage.getItem('personalBest');
    if (personalBest) {
        const pbData = JSON.parse(personalBest);
        personalBestSpecies.value = pbData.species;
        personalBestSize.value = pbData.size;
        previousBestSize.value = pbData.previousBest;
        showPersonalBestNotification.value = true;
        triggerConfetti();

        // Clear from sessionStorage
        sessionStorage.removeItem('personalBest');

        // Auto-hide notification after 20 seconds
        setTimeout(() => {
            showPersonalBestNotification.value = false;
        }, 20000);
    }
};

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

// Handle per page change
const handlePerPageChange = () => {
    currentPage.value = 1; // Reset to first page when changing per page
    fetchFishingLogs(1);
};

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

// Handle location form success
const handleLocationSuccess = (location: any) => {
    locations.value.push(location);
    formData.value.user_location_id = location.id.toString();
};

// Success handlers for form dialogs
const handleFishSuccess = (fish: any) => {
    fishSpecies.value.push(fish);
    formData.value.user_fish_id = fish.id.toString();
};

const handleFlySuccess = (fly: any) => {
    flies.value.push(fly);
    formData.value.user_fly_id = fly.id.toString();
};

const handleRodSuccess = (rod: any) => {
    equipment.value.push(rod);
    formData.value.user_rod_id = rod.id.toString();
};

const handleFriendSuccess = (friend: any) => {
    friends.value.push(friend);
    formData.value.friend_ids.push(friend.id);
};

// Handle fishing log form success
const handleFishingLogSuccess = async (data: any) => {
    // Check if it's a new species
    if (data.is_new_species && data.fishing_log?.fish) {
        newSpeciesName.value = data.fishing_log.fish.species;
        showSuccessNotification.value = true;
        triggerConfetti();

        // Auto-hide notification after 20 seconds
        setTimeout(() => {
            showSuccessNotification.value = false;
        }, 20000);
    }
    // Check if it's a personal best (only show if not a new species)
    else if (data.is_personal_best && data.fishing_log?.fish) {
        personalBestSpecies.value = data.fishing_log.fish.species;
        personalBestSize.value = data.fishing_log.max_size;
        previousBestSize.value = data.previous_best_size;
        showPersonalBestNotification.value = true;
        triggerConfetti();

        // Auto-hide notification after 20 seconds
        setTimeout(() => {
            showPersonalBestNotification.value = false;
        }, 20000);
    }

    // Reset edit mode
    isEditMode.value = false;
    editingLogId.value = null;

    // Refresh fishing logs
    await fetchFishingLogs();
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
    checkForNewSpecies();
});

// Clear form errors
const clearFormErrors = () => {
    formErrors.value = {};
    formErrorMessage.value = '';
};

// Open edit dialog with log data
const editLog = (log: any) => {
    isEditMode.value = true;
    editingLogId.value = log.id;
    editingLog.value = log;
    clearFormErrors();
    showAddForm.value = true;
};

// Open add form
const openAddForm = () => {
    isEditMode.value = false;
    editingLogId.value = null;
    editingLog.value = null;
    clearFormErrors();
    showAddForm.value = true;
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
            time: formData.value.time ? formData.value.time : null,
            user_location_id: formData.value.user_location_id ? parseInt(formData.value.user_location_id) : null,
            user_fish_id: formData.value.user_fish_id ? parseInt(formData.value.user_fish_id) : null,
            quantity: formData.value.quantity ? parseInt(formData.value.quantity) : null,
            max_size: formData.value.maxSize ? parseFloat(formData.value.maxSize) : null,
            user_fly_id: formData.value.user_fly_id ? parseInt(formData.value.user_fly_id) : null,
            user_rod_id: formData.value.user_rod_id ? parseInt(formData.value.user_rod_id) : null,
            style: formData.value.fishingStyle ? formData.value.fishingStyle : null,
            moon_phase: formData.value.moonPhase ? formData.value.moonPhase : null,
            barometric_pressure: formData.value.barometricPressure ? formData.value.barometricPressure : null,
            friend_ids: formData.value.friend_ids,
            notes: formData.value.notes ? formData.value.notes : null,
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

            // Check if it's a new species
            if (response.data.is_new_species && response.data.fishing_log.fish) {
                newSpeciesName.value = response.data.fishing_log.fish.species;
                showSuccessNotification.value = true;
                triggerConfetti();

                // Auto-hide notification after 20 seconds
                setTimeout(() => {
                    showSuccessNotification.value = false;
                }, 20000);
            }
            // Check if it's a personal best (only show if not a new species)
            else if (response.data.is_personal_best && response.data.fishing_log.fish) {
                personalBestSpecies.value = response.data.fishing_log.fish.species;
                personalBestSize.value = response.data.fishing_log.max_size;
                previousBestSize.value = response.data.previous_best_size;
                showPersonalBestNotification.value = true;
                triggerConfetti();

                // Auto-hide notification after 20 seconds
                setTimeout(() => {
                    showPersonalBestNotification.value = false;
                }, 20000);
            }
        }

        // Reset form
        resetForm();

        // Refresh fishing logs and close dialog
        await fetchFishingLogs();
        showAddForm.value = false;
    } catch (error: any) {
        console.error('Error saving fishing log:', error);
        console.error('Validation errors:', error.response?.data?.errors);
        console.error('Error message:', error.response?.data?.message);

        // Set error state for display to user
        if (error.response?.status === 422) {
            formErrors.value = error.response.data.errors || {};
            formErrorMessage.value = error.response.data.message || 'Validation failed. Please check the form.';
        } else {
            formErrorMessage.value = 'An error occurred while saving the fishing log. Please try again.';
        }
    }
};

// Confetti celebration
const triggerConfetti = () => {
    const duration = 3000;
    const animationEnd = Date.now() + duration;
    const defaults = { startVelocity: 30, spread: 360, ticks: 60, zIndex: 9999 };

    const randomInRange = (min: number, max: number) => {
        return Math.random() * (max - min) + min;
    };

    const interval = setInterval(() => {
        const timeLeft = animationEnd - Date.now();

        if (timeLeft <= 0) {
            return clearInterval(interval);
        }

        const particleCount = 50 * (timeLeft / duration);

        confetti({
            ...defaults,
            particleCount,
            origin: { x: randomInRange(0.1, 0.3), y: Math.random() - 0.2 }
        });
        confetti({
            ...defaults,
            particleCount,
            origin: { x: randomInRange(0.7, 0.9), y: Math.random() - 0.2 }
        });
    }, 250);
};

// Format date for display
const formatDate = (dateString: string) => {
    if (!dateString) return 'Invalid date';

    // Parse the date string as local date to avoid timezone issues
    const parts = dateString.split('T')[0].split('-'); // Handle both "2026-01-02" and "2026-01-02T00:00:00"
    const [year, month, day] = parts.map(Number);

    if (!year || !month || !day) return 'Invalid date';

    const date = new Date(year, month - 1, day);
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
                <!-- Success Notification -->
                <Transition
                    enter-active-class="transition ease-out duration-300"
                    enter-from-class="opacity-0 transform translate-y-2"
                    enter-to-class="opacity-100 transform translate-y-0"
                    leave-active-class="transition ease-in duration-200"
                    leave-from-class="opacity-100 transform translate-y-0"
                    leave-to-class="opacity-0 transform translate-y-2"
                >
                    <Alert v-if="showSuccessNotification" variant="success" class="mb-4 relative !gap-x-6">
                        <CheckCircle2 class="h-5 w-5" />
                        <button
                            @click="showSuccessNotification = false"
                            class="absolute right-2 top-2 rounded-sm opacity-70 ring-offset-background transition-opacity hover:opacity-100 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                        >
                            <X class="h-4 w-4" />
                            <span class="sr-only">Close</span>
                        </button>
                        <AlertTitle class="text-base font-semibold pr-6">🎉&nbsp;&nbsp;Congratulations! New Species Caught!</AlertTitle>
                        <AlertDescription class="text-sm pr-6">
                            <span class="inline">You've logged your first <span class="font-bold">{{ newSpeciesName }}</span>!</span> This is a new species for you to track.
                        </AlertDescription>
                    </Alert>
                </Transition>

                <!-- Personal Best Notification -->
                <Transition
                    enter-active-class="transition ease-out duration-300"
                    enter-from-class="opacity-0 transform translate-y-2"
                    enter-to-class="opacity-100 transform translate-y-0"
                    leave-active-class="transition ease-in duration-200"
                    leave-from-class="opacity-100 transform translate-y-0"
                    leave-to-class="opacity-0 transform translate-y-2"
                >
                    <Alert v-if="showPersonalBestNotification" variant="success" class="mb-4 relative !gap-x-6">
                        <Trophy class="h-5 w-5" />
                        <button
                            @click="showPersonalBestNotification = false"
                            class="absolute right-2 top-2 rounded-sm opacity-70 ring-offset-background transition-opacity hover:opacity-100 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                        >
                            <X class="h-4 w-4" />
                            <span class="sr-only">Close</span>
                        </button>
                        <AlertTitle class="text-base font-semibold pr-6">🏆&nbsp;&nbsp;Personal Best! New Record!</AlertTitle>
                        <AlertDescription class="text-sm pr-6">
                            <span class="inline">You caught a <span class="font-bold">{{ personalBestSize }}"</span> <span class="font-bold">{{ personalBestSpecies }}</span>!</span> That beats your previous best of {{ previousBestSize }}".
                        </AlertDescription>
                    </Alert>
                </Transition>

                <Card class="bg-gradient-to-br from-teal-50/30 to-transparent dark:from-teal-950/10">
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div>
                                <CardTitle class="flex items-center gap-2">
                                    <div class="rounded-full bg-teal-100 p-1.5 dark:bg-teal-900/30">
                                        <Fish class="h-5 w-5 text-teal-600 dark:text-teal-400" />
                                    </div>
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
                            <Card v-for="log in fishingLogs" :key="log.id" class="overflow-hidden bg-gradient-to-br from-teal-50/30 to-transparent dark:from-teal-950/10">
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

                        <!-- Pagination -->
                        <div class="mt-4 flex flex-wrap items-center justify-between gap-4">
                            <div class="flex items-center gap-2 whitespace-nowrap">
                                <span class="text-sm text-muted-foreground">Rows per page:</span>
                                <NativeSelect v-model="perPage" @change="handlePerPageChange" class="w-[80px]">
                                    <NativeSelectOption :value="15">15</NativeSelectOption>
                                    <NativeSelectOption :value="25">25</NativeSelectOption>
                                    <NativeSelectOption :value="50">50</NativeSelectOption>
                                    <NativeSelectOption :value="100">100</NativeSelectOption>
                                    <NativeSelectOption :value="150">150</NativeSelectOption>
                                </NativeSelect>
                            </div>
                            <Pagination v-if="totalPages > 1" :total="totalPages" :sibling-count="1" show-edges :default-page="1" v-model:page="currentPage" @update:page="goToPage" class="order-3 w-full sm:order-2 sm:w-auto sm:flex-1 justify-center">
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
                            <div class="text-sm text-muted-foreground whitespace-nowrap order-2 sm:order-3">
                                Showing {{ ((currentPage - 1) * perPage) + 1 }} to {{ Math.min(currentPage * perPage, total) }} of {{ total }} entries
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>

        <!-- Add/Edit Log Dialog -->
        <FishingLogFormDialog
            v-model:open="showAddForm"
            :editing-log="editingLog"
            :locations="locations"
            :fish="fish"
            :flies="flies"
            :equipment="equipment"
            :friends="friends"
            @success="handleFishingLogSuccess"
            @open-location-modal="showLocationModal = true"
            @open-fish-modal="showFishModal = true"
            @open-fly-modal="showFlyModal = true"
            @open-equipment-modal="showEquipmentModal = true"
            @open-friend-modal="showFriendModal = true"
        />

        <!-- Location Form Dialog -->
        <LocationFormDialog
            v-model:open="showLocationModal"
            @success="handleLocationSuccess"
        />

        <!-- Fish Form Dialog -->
        <FishFormDialog
            v-model:open="showFishModal"
            @success="handleFishSuccess"
        />

        <!-- Fly Form Dialog -->
        <FlyFormDialog
            v-model:open="showFlyModal"
            @success="handleFlySuccess"
        />

        <!-- Rod Form Dialog -->
        <RodFormDialog
            v-model:open="showEquipmentModal"
            @success="handleRodSuccess"
        />

        <!-- Friend Form Dialog -->
        <FriendFormDialog
            v-model:open="showFriendModal"
            @success="handleFriendSuccess"
        />

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





