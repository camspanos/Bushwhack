<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Pagination, PaginationContent, PaginationItem, PaginationNext, PaginationPrevious } from '@/components/ui/pagination';
import { NativeSelect, NativeSelectOption } from '@/components/ui/native-select';
import FishingLogForm from '@/components/fishing-log/FishingLogForm.vue';
import type { FishingLogInitialData, FishingLogFormData } from '@/components/fishing-log/FishingLogForm.vue';
import { type BreadcrumbItem, type FishingLog } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import { Fish, Pencil, Trash2, X, FileText, CheckCircle2, Trophy, Plus } from 'lucide-vue-next';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import axios from '@/lib/axios';
import confetti from 'canvas-confetti';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Fishing Log',
        href: '/fishing-log',
    },
];

// Constants
const NOTIFICATION_TIMEOUT_MS = 20000;



// Show/hide add form dialog
const showAddForm = ref(false);

// Edit mode
const editingLogId = ref<number | null>(null);
const isEditMode = ref(false);

// Delete confirmation
const showDeleteConfirm = ref(false);
const logToDelete = ref<FishingLog | null>(null);

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
const fishingLogs = ref<FishingLog[]>([]);

// Pagination state
const currentPage = ref(1);
const totalPages = ref(1);
const perPage = ref(25);
const total = ref(0);

// Form component ref
const fishingLogFormRef = ref<InstanceType<typeof FishingLogForm> | null>(null);

// Initial data for edit mode
const editFormData = ref<FishingLogInitialData | undefined>(undefined);

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
        }, NOTIFICATION_TIMEOUT_MS);
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
        }, NOTIFICATION_TIMEOUT_MS);
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

// Load all data on mount
onMounted(() => {
    fetchFishingLogs();
    checkForNewSpecies();
});

// Open edit dialog with log data
const editLog = (log: FishingLog) => {
    isEditMode.value = true;
    editingLogId.value = log.id;

    // Set initial data for the form component
    editFormData.value = {
        id: log.id,
        date: log.date,
        time: log.time,
        user_location_id: log.user_location_id,
        user_fish_id: log.user_fish_id,
        quantity: log.quantity,
        max_size: log.max_size,
        max_weight: log.max_weight,
        user_fly_id: log.user_fly_id,
        user_rod_id: log.user_rod_id,
        style: log.style,
        moon_phase: log.moon_phase,
        moon_altitude: log.moon_altitude,
        moon_position: log.moon_position,
        time_of_day: log.time_of_day,
        friends: log.friends,
        notes: log.notes,
        weather: log.weather,
        water_condition: log.water_condition,
    };

    showAddForm.value = true;
};

// Open add form
const openAddForm = () => {
    isEditMode.value = false;
    editingLogId.value = null;
    editFormData.value = undefined;
    showAddForm.value = true;
};

// Open delete confirmation dialog
const confirmDelete = (log: FishingLog) => {
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

// Handle form submission from FishingLogForm component
const handleFormSubmit = async (submitData: FishingLogFormData) => {
    try {
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
                }, NOTIFICATION_TIMEOUT_MS);
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
                }, NOTIFICATION_TIMEOUT_MS);
            }
        }

        // Reset form and close dialog
        if (fishingLogFormRef.value) {
            fishingLogFormRef.value.resetForm();
        }
        isEditMode.value = false;
        editingLogId.value = null;
        editFormData.value = undefined;

        // Refresh fishing logs and close dialog
        await fetchFishingLogs();
        showAddForm.value = false;
    } catch (error: any) {
        console.error('Error saving fishing log:', error);
        console.error('Validation errors:', error.response?.data?.errors);
        console.error('Error message:', error.response?.data?.message);

        // Set error state on the form component
        if (fishingLogFormRef.value) {
            if (error.response?.status === 422) {
                fishingLogFormRef.value.setErrors(
                    error.response.data.errors || {},
                    error.response.data.message || 'Validation failed. Please check the form.'
                );
            } else {
                fishingLogFormRef.value.setErrors(
                    {},
                    'An error occurred while saving the fishing log. Please try again.'
                );
            }
        }
    }
};

// Handle form cancel
const handleFormCancel = () => {
    showAddForm.value = false;
    isEditMode.value = false;
    editingLogId.value = null;
    editFormData.value = undefined;
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
                                        <TableHead>Max Weight</TableHead>
                                        <TableHead>Fly</TableHead>
                                        <TableHead>Style</TableHead>
                                        <TableHead>Notes</TableHead>
                                        <TableHead class="text-right">Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-if="fishingLogs.length === 0">
                                        <TableCell colspan="10" class="text-center text-muted-foreground py-8">
                                            No fishing logs yet. Click "Add New" to create your first log!
                                        </TableCell>
                                    </TableRow>
                                    <TableRow v-for="log in fishingLogs" :key="log.id">
                                        <TableCell class="font-medium">{{ formatDate(log.date) }}</TableCell>
                                        <TableCell>{{ log.location?.name || '-' }}</TableCell>
                                        <TableCell>{{ log.fish?.species || '-' }}</TableCell>
                                        <TableCell>{{ log.quantity || '-' }}</TableCell>
                                        <TableCell>{{ log.max_size ? `${formatSize(log.max_size)}"` : '-' }}</TableCell>
                                        <TableCell>{{ log.max_weight ? `${formatSize(log.max_weight)} lbs` : '-' }}</TableCell>
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
                                                <span class="text-muted-foreground">Max Weight:</span>
                                                <span class="ml-1">{{ log.max_weight ? `${formatSize(log.max_weight)} lbs` : '-' }}</span>
                                            </div>
                                            <div>
                                                <span class="text-muted-foreground">Fly:</span>
                                                <span class="ml-1">{{ log.fly?.name || '-' }}</span>
                                            </div>
                                            <div>
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

                <FishingLogForm
                    ref="fishingLogFormRef"
                    :mode="isEditMode ? 'edit' : 'create'"
                    :initial-data="editFormData"
                    :submit-label="isEditMode ? 'Update Fishing Log' : 'Save Fishing Log'"
                    @submit="handleFormSubmit"
                    @cancel="handleFormCancel"
                />
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

