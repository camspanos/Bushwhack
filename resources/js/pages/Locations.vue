<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import PremiumFeatureDialog from '@/components/PremiumFeatureDialog.vue';
import LocationFormDialog from '@/components/LocationFormDialog.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Pagination, PaginationContent, PaginationItem, PaginationNext, PaginationPrevious } from '@/components/ui/pagination';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { NativeSelect, NativeSelectOption } from '@/components/ui/native-select';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { ref, onMounted, computed, watch } from 'vue';
import { MapPin, Plus, Pencil, Trash2, Table as TableIcon, BarChart3, Fish, TrendingUp, Award, Calendar as CalendarIcon } from 'lucide-vue-next';
import axios from '@/lib/axios';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Locations', href: '/locations' },
];

// State
const showLocationForm = ref(false);
const editingLocation = ref(null);
const showDeleteConfirm = ref(false);
const itemToDelete = ref(null);
const locations = ref([]);
const locationStats = ref([]);

// Pagination
const currentPage = ref(1);
const totalPages = ref(1);
const perPage = ref(25);
const total = ref(0);

// Year filter
const currentYear = new Date().getFullYear().toString();
const selectedYearFilter = ref('lifetime'); // Will be set after fetching available years
const availableYears = ref<string[]>([]);
const showPremiumDialog = ref(false);
const page = usePage();



// Fetch locations
const fetchLocations = async (page = 1) => {
    try {
        const response = await axios.get('/locations', {
            params: { page: page, per_page: perPage.value }
        });
        locations.value = response.data.data;
        currentPage.value = response.data.current_page;
        totalPages.value = response.data.last_page;
        total.value = response.data.total;
    } catch (error) {
        console.error('Error fetching locations:', error);
    }
};

// Pagination handlers
const goToPage = (page: number) => {
    if (page >= 1 && page <= totalPages.value) {
        fetchLocations(page);
    }
};

const nextPage = () => {
    if (currentPage.value < totalPages.value) {
        fetchLocations(currentPage.value + 1);
    }
};

const previousPage = () => {
    if (currentPage.value > 1) {
        fetchLocations(currentPage.value - 1);
    }
};

// Handle per page change
const handlePerPageChange = () => {
    currentPage.value = 1; // Reset to first page when changing per page
    fetchLocations(1);
};

// Delete confirmation
const confirmDelete = (location: any) => {
    itemToDelete.value = location;
    showDeleteConfirm.value = true;
};

const handleDelete = async () => {
    if (!itemToDelete.value) return;
    try {
        await axios.delete(`/locations/${itemToDelete.value.id}`);
        await fetchLocations(currentPage.value);
        showDeleteConfirm.value = false;
        itemToDelete.value = null;
    } catch (error) {
        console.error('Error deleting location:', error);
    }
};

const cancelDelete = () => {
    showDeleteConfirm.value = false;
    itemToDelete.value = null;
};

// Fetch available years
const fetchAvailableYears = async () => {
    try {
        const response = await axios.get('/fishing-logs/available-years');
        availableYears.value = response.data;

        // Set default year to current year (always included in response now)
        selectedYearFilter.value = currentYear;
    } catch (error) {
        console.error('Error fetching available years:', error);
    }
};

// Fetch location statistics
const fetchLocationStats = async () => {
    try {
        const response = await axios.get('/locations/stats/all', {
            params: { year: selectedYearFilter.value }
        });
        locationStats.value = response.data;
    } catch (error) {
        console.error('Error fetching location statistics:', error);
    }
};

// Watch for year filter changes
watch(selectedYearFilter, async (newYear, oldYear) => {
    // Check if user is trying to select a non-current year and is not premium
    if (!page.props.auth.isPremium && newYear !== currentYear) {
        // Show premium dialog
        showPremiumDialog.value = true;
        // Revert the selection
        selectedYearFilter.value = oldYear;
        return;
    }

    fetchLocationStats();
});

// Display year label
const yearLabel = computed(() => {
    return selectedYearFilter.value === 'lifetime' ? 'Lifetime' : selectedYearFilter.value;
});

// Handle location form success
const handleLocationSuccess = (location: any) => {
    fetchLocations(currentPage.value);
    fetchLocationStats();
};

// Open form for adding new location
const openAddForm = () => {
    editingLocation.value = null;
    showLocationForm.value = true;
};

// Open form for editing location
const editItem = (location: any) => {
    editingLocation.value = location;
    showLocationForm.value = true;
};

onMounted(async () => {
    fetchLocations();
    await fetchAvailableYears(); // Wait for years to be fetched and default year to be set
    fetchLocationStats(); // Now fetch stats with the correct default year
});
</script>

<template>
    <Head title="Locations" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="mx-auto w-full max-w-6xl">
                <!-- Tab Navigation -->
                <Tabs default-value="table" class="w-full">
                    <TabsList class="grid w-full grid-cols-2 mb-4">
                        <TabsTrigger value="table" class="flex items-center gap-2">
                            <TableIcon class="h-4 w-4" />
                            Table View
                        </TabsTrigger>
                        <TabsTrigger value="dashboard" class="flex items-center gap-2">
                            <BarChart3 class="h-4 w-4" />
                            Dashboard
                        </TabsTrigger>
                    </TabsList>

                    <!-- Table View -->
                    <TabsContent value="table" class="mt-0">
                        <Card class="bg-gradient-to-br from-teal-50/30 to-transparent dark:from-teal-950/10">
                            <CardHeader>
                                <div class="flex items-center justify-between">
                                    <div>
                                        <CardTitle class="flex items-center gap-2">
                                            <div class="rounded-full bg-teal-100 p-1.5 dark:bg-teal-900/30">
                                                <MapPin class="h-5 w-5 text-teal-600 dark:text-teal-400" />
                                            </div>
                                            Your Locations
                                        </CardTitle>
                                        <CardDescription>
                                            View and manage your fishing locations
                                        </CardDescription>
                                    </div>
                                    <Button @click="openAddForm()" class="flex items-center gap-2">
                                        <Plus class="h-4 w-4" />
                                        Add New Location
                                    </Button>
                                </div>
                            </CardHeader>
                            <CardContent class="p-6">
                                <!-- Desktop Table View -->
                                <div class="hidden md:block rounded-md border">
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>Name</TableHead>
                                                <TableHead>Water Type</TableHead>
                                                <TableHead>City</TableHead>
                                                <TableHead>State</TableHead>
                                                <TableHead>Country</TableHead>
                                                <TableHead>Latitude</TableHead>
                                                <TableHead>Longitude</TableHead>
                                                <TableHead class="text-right">Actions</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            <TableRow v-if="locations.length === 0">
                                                <TableCell colspan="8" class="text-center text-muted-foreground py-8">
                                                    No locations yet. Click "Add New" to create your first location!
                                                </TableCell>
                                            </TableRow>
                                            <TableRow v-for="location in locations" :key="location.id">
                                                <TableCell class="font-medium">{{ location.name }}</TableCell>
                                                <TableCell>{{ location.water_type || '-' }}</TableCell>
                                                <TableCell>{{ location.city || '-' }}</TableCell>
                                                <TableCell>{{ location.state || '-' }}</TableCell>
                                                <TableCell>{{ location.country?.name || '-' }}</TableCell>
                                                <TableCell>{{ location.latitude || '-' }}</TableCell>
                                                <TableCell>{{ location.longitude || '-' }}</TableCell>
                                                <TableCell class="text-right">
                                                    <div class="flex items-center justify-end gap-0">
                                                        <Button variant="ghost" size="icon" @click="editItem(location)" class="h-8 w-8">
                                                            <Pencil class="h-4 w-4" />
                                                        </Button>
                                                        <Button variant="ghost" size="icon" @click="confirmDelete(location)"
                                                            class="h-8 w-8 text-destructive hover:text-destructive -mr-2">
                                                            <Trash2 class="h-4 w-4" />
                                                        </Button>
                                                    </div>
                                                </TableCell>
                                            </TableRow>
                                        </TableBody>
                                    </Table>
                                </div>

                                <!-- Mobile Card View -->
                                <div class="md:hidden space-y-3">
                                    <div v-if="locations.length === 0" class="text-center text-muted-foreground py-8 border rounded-md">
                                        No locations yet. Click "Add New" to create your first location!
                                    </div>
                                    <Card v-for="location in locations" :key="location.id" class="overflow-hidden">
                                        <CardContent class="p-4">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="flex-1 space-y-2">
                                                    <div class="font-semibold text-base">{{ location.name }}</div>
                                                    <div class="grid grid-cols-2 gap-2 text-sm">
                                                        <div>
                                                            <span class="text-muted-foreground">City:</span>
                                                            <span class="ml-1">{{ location.city || '-' }}</span>
                                                        </div>
                                                        <div>
                                                            <span class="text-muted-foreground">State:</span>
                                                            <span class="ml-1">{{ location.state || '-' }}</span>
                                                        </div>
                                                        <div class="col-span-2">
                                                            <span class="text-muted-foreground">Country:</span>
                                                            <span class="ml-1">{{ location.country?.name || '-' }}</span>
                                                        </div>
                                                        <div>
                                                            <span class="text-muted-foreground">Lat:</span>
                                                            <span class="ml-1">{{ location.latitude || '-' }}</span>
                                                        </div>
                                                        <div>
                                                            <span class="text-muted-foreground">Lon:</span>
                                                            <span class="ml-1">{{ location.longitude || '-' }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="flex flex-col gap-1">
                                                    <Button variant="ghost" size="icon-sm" @click="editItem(location)">
                                                        <Pencil class="h-4 w-4" />
                                                    </Button>
                                                    <Button variant="ghost" size="icon-sm" @click="confirmDelete(location)"
                                                        class="text-destructive hover:text-destructive">
                                                        <Trash2 class="h-4 w-4" />
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
                                                <Button variant="ghost" size="icon" @click="goToPage(page)"
                                                    :class="{ 'bg-accent dark:bg-accent dark:border-border': page === currentPage }" class="h-9 w-9">
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
                    </TabsContent>

                    <!-- Dashboard View -->
                    <TabsContent value="dashboard" class="mt-0">
                        <div class="space-y-6">
                            <!-- Header with Year Filter -->
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-2xl font-bold tracking-tight flex items-center gap-2">
                                        <MapPin class="h-6 w-6" />
                                        Location Performance
                                    </h3>
                                    <p class="text-muted-foreground">Statistics {{ yearLabel === 'Lifetime' ? 'across all time' : 'for ' + yearLabel }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <label for="location-year-filter" class="text-sm font-medium">Filter by:</label>
                                    <NativeSelect v-model="selectedYearFilter" id="location-year-filter" class="w-[140px]">
                                        <NativeSelectOption value="lifetime">Lifetime</NativeSelectOption>
                                        <NativeSelectOption v-for="year in availableYears" :key="year" :value="year">
                                            {{ year }}
                                        </NativeSelectOption>
                                    </NativeSelect>
                                </div>
                            </div>

                            <!-- Premium Feature Dialog -->
                            <PremiumFeatureDialog
                                v-model:open="showPremiumDialog"
                                title="Year Filtering is a Premium Feature"
                                description="Access to historical data and year filtering is only available to premium users. Upgrade to premium to view your fishing statistics from previous years and lifetime totals."
                            />

                            <!-- Location Stats Grid -->
                            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                                <Card v-for="location in locationStats" :key="location.id" class="bg-gradient-to-br from-teal-50/30 to-transparent dark:from-teal-950/10">
                                    <CardHeader>
                                        <CardTitle class="text-lg flex items-center gap-2">
                                            <div class="rounded-full bg-teal-100 p-1.5 dark:bg-teal-900/30">
                                                <MapPin class="h-4 w-4 text-teal-600 dark:text-teal-400" />
                                            </div>
                                            {{ location.name }}
                                        </CardTitle>
                                        <CardDescription>
                                            {{ location.city }}{{ location.state ? ', ' + location.state : (location.country?.name ? ', ' + location.country.name : '') }}
                                        </CardDescription>
                                    </CardHeader>
                                    <CardContent class="space-y-3">
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-muted-foreground flex items-center gap-2">
                                                <CalendarIcon class="h-4 w-4 text-amber-500 dark:text-amber-400" />
                                                Total Trips
                                            </span>
                                            <span class="font-bold text-amber-700 dark:text-amber-300">{{ location.totalTrips }}</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-muted-foreground flex items-center gap-2">
                                                <Fish class="h-4 w-4 text-emerald-500 dark:text-emerald-400" />
                                                Total Fish
                                            </span>
                                            <span class="font-bold text-emerald-700 dark:text-emerald-300">{{ location.totalFish }}</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-muted-foreground flex items-center gap-2">
                                                <Award class="h-4 w-4 text-blue-500 dark:text-blue-400" />
                                                Biggest Fish
                                            </span>
                                            <span class="font-bold text-blue-700 dark:text-blue-300">{{ location.biggestFish }}"</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-muted-foreground flex items-center gap-2">
                                                <TrendingUp class="h-4 w-4 text-red-500 dark:text-red-400" />
                                                Success Rate
                                            </span>
                                            <span class="font-bold text-red-700 dark:text-red-300">{{ location.successRate }}%</span>
                                        </div>
                                    </CardContent>
                                </Card>
                            </div>

                            <!-- Empty State -->
                            <Card v-if="locationStats.length === 0" class="bg-gradient-to-br from-gray-50/30 to-transparent dark:from-gray-950/10">
                                <CardContent class="py-8">
                                    <div class="text-center text-muted-foreground">
                                        No location statistics available yet. Start logging your fishing trips!
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                    </TabsContent>
                </Tabs>
            </div>
        </div>

        <!-- Location Form Dialog -->
        <LocationFormDialog
            v-model:open="showLocationForm"
            :editing-location="editingLocation"
            @success="handleLocationSuccess"
        />

        <!-- Delete Confirmation Dialog -->
        <Dialog v-model:open="showDeleteConfirm">
            <DialogContent class="max-w-md">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2 text-destructive">
                        <Trash2 class="h-5 w-5" />
                        Delete Location
                    </DialogTitle>
                    <DialogDescription>
                        Are you sure you want to delete this location? This action cannot be undone.
                    </DialogDescription>
                </DialogHeader>
                <div v-if="itemToDelete" class="py-4">
                    <div class="rounded-lg bg-muted p-4 space-y-2">
                        <p class="text-sm font-medium">Location Details:</p>
                        <p class="text-sm text-muted-foreground">
                            <strong>Name:</strong> {{ itemToDelete.name }}
                        </p>
                        <p class="text-sm text-muted-foreground" v-if="itemToDelete.city">
                            <strong>City:</strong> {{ itemToDelete.city }}
                        </p>
                        <p class="text-sm text-muted-foreground" v-if="itemToDelete.state">
                            <strong>State:</strong> {{ itemToDelete.state }}
                        </p>
                    </div>
                </div>
                <DialogFooter>
                    <Button type="button" variant="outline" @click="cancelDelete">Cancel</Button>
                    <Button type="button" variant="destructive" @click="handleDelete">Delete</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>


