<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Pagination, PaginationContent, PaginationItem, PaginationNext, PaginationPrevious } from '@/components/ui/pagination';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { NativeSelect, NativeSelectOption } from '@/components/ui/native-select';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, computed, watch } from 'vue';
import { MapPin, Plus, Pencil, Trash2, Table as TableIcon, BarChart3, Fish, TrendingUp, Award, Calendar as CalendarIcon, AlertCircle } from 'lucide-vue-next';
import axios from '@/lib/axios';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Locations', href: '/locations' },
];

// State
const showAddForm = ref(false);
const editingId = ref(null);
const isEditMode = ref(false);
const showDeleteConfirm = ref(false);
const itemToDelete = ref(null);
const locations = ref([]);
const locationStats = ref([]);
const errorMessage = ref('');

// Pagination
const currentPage = ref(1);
const totalPages = ref(1);
const perPage = ref(15);
const total = ref(0);

// Year filter
const currentYear = new Date().getFullYear().toString();
const selectedYearFilter = ref(currentYear);
const availableYears = ref<string[]>([]);

// Form data
const formData = ref({
    name: '',
    city: '',
    state: '',
    country: '',
});

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

// Edit location
const editItem = (location: any) => {
    editingId.value = location.id;
    isEditMode.value = true;
    formData.value = {
        name: location.name,
        city: location.city || '',
        state: location.state || '',
        country: location.country || '',
    };
    showAddForm.value = true;
};

// Reset form
const resetForm = () => {
    formData.value = {
        name: '',
        city: '',
        state: '',
        country: '',
    };
    editingId.value = null;
    isEditMode.value = false;
    showAddForm.value = false;
    errorMessage.value = '';
};

// Submit form
const handleSubmit = async () => {
    errorMessage.value = '';
    try {
        let response;
        if (isEditMode.value && editingId.value) {
            response = await axios.put(`/locations/${editingId.value}`, formData.value);
        } else {
            response = await axios.post('/locations', formData.value);
        }
        await fetchLocations(currentPage.value);
        resetForm();
    } catch (error: any) {
        console.error('Error saving location:', error);
        if (error.response?.status === 409) {
            errorMessage.value = error.response.data.message || 'This location already exists.';
            showAddForm.value = false;
        }
    }
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
        const response = await axios.get('/fishing-logs');
        const logs = response.data.data;
        const years = [...new Set(logs.map((log: any) => new Date(log.date).getFullYear().toString()))];
        availableYears.value = years.sort((a, b) => parseInt(b) - parseInt(a));
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
watch(selectedYearFilter, () => {
    fetchLocationStats();
});

// Display year label
const yearLabel = computed(() => {
    return selectedYearFilter.value === 'lifetime' ? 'Lifetime' : selectedYearFilter.value;
});

onMounted(() => {
    fetchLocations();
    fetchAvailableYears();
    fetchLocationStats();
});
</script>

<template>
    <Head title="Locations" />
    <AppLayout title="Locations" :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="mx-auto w-full max-w-6xl">
                <!-- Error Alert -->
                <Alert v-if="errorMessage" variant="destructive" class="mb-4">
                    <AlertCircle class="h-4 w-4" />
                    <AlertDescription>{{ errorMessage }}</AlertDescription>
                </Alert>

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
                        <Card>
                            <CardHeader>
                                <div class="flex items-center justify-between">
                                    <div>
                                        <CardTitle class="flex items-center gap-2">
                                            <MapPin class="h-6 w-6" />
                                            Your Locations
                                        </CardTitle>
                                        <CardDescription>
                                            View and manage your fishing locations
                                        </CardDescription>
                                    </div>
                                    <Button @click="resetForm(); showAddForm = true;" class="flex items-center gap-2">
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
                                                <TableHead>City</TableHead>
                                                <TableHead>State</TableHead>
                                                <TableHead>Country</TableHead>
                                                <TableHead class="text-right">Actions</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            <TableRow v-if="locations.length === 0">
                                                <TableCell colspan="5" class="text-center text-muted-foreground py-8">
                                                    No locations yet. Click "Add New" to create your first location!
                                                </TableCell>
                                            </TableRow>
                                            <TableRow v-for="location in locations" :key="location.id">
                                                <TableCell class="font-medium">{{ location.name }}</TableCell>
                                                <TableCell>{{ location.city || '-' }}</TableCell>
                                                <TableCell>{{ location.state || '-' }}</TableCell>
                                                <TableCell>{{ location.country || '-' }}</TableCell>
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
                                                            <span class="ml-1">{{ location.country || '-' }}</span>
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

                            <!-- Location Stats Grid -->
                            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                                <Card v-for="location in locationStats" :key="location.id">
                                    <CardHeader>
                                        <CardTitle class="text-lg">{{ location.name }}</CardTitle>
                                        <CardDescription>
                                            {{ location.city }}{{ location.state ? ', ' + location.state : '' }}
                                        </CardDescription>
                                    </CardHeader>
                                    <CardContent class="space-y-3">
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-muted-foreground flex items-center gap-2">
                                                <CalendarIcon class="h-4 w-4" />
                                                Total Trips
                                            </span>
                                            <span class="font-bold">{{ location.totalTrips }}</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-muted-foreground flex items-center gap-2">
                                                <Fish class="h-4 w-4" />
                                                Total Fish
                                            </span>
                                            <span class="font-bold">{{ location.totalFish }}</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-muted-foreground flex items-center gap-2">
                                                <Award class="h-4 w-4" />
                                                Biggest Fish
                                            </span>
                                            <span class="font-bold">{{ location.biggestFish }}"</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-muted-foreground flex items-center gap-2">
                                                <TrendingUp class="h-4 w-4" />
                                                Success Rate
                                            </span>
                                            <span class="font-bold">{{ location.successRate }}%</span>
                                        </div>
                                    </CardContent>
                                </Card>
                            </div>

                            <!-- Empty State -->
                            <Card v-if="locationStats.length === 0">
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

        <!-- Add/Edit Dialog -->
        <Dialog v-model:open="showAddForm">
            <DialogContent class="max-w-2xl">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2">
                        <MapPin class="h-6 w-6" />
                        {{ isEditMode ? 'Edit Location' : 'Add New Location' }}
                    </DialogTitle>
                    <DialogDescription>
                        {{ isEditMode ? 'Update the location details below.' : 'Enter the location details below.' }}
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="handleSubmit">
                    <div class="grid gap-4 py-4">
                        <div class="grid gap-2">
                            <Label for="name">Name *</Label>
                            <Input id="name" v-model="formData.name" required placeholder="e.g., Snake River" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="city">City</Label>
                            <Input id="city" v-model="formData.city" placeholder="e.g., Jackson" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="state">State</Label>
                            <Input id="state" v-model="formData.state" placeholder="e.g., Wyoming" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="country">Country</Label>
                            <Input id="country" v-model="formData.country" placeholder="e.g., USA" />
                        </div>
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="resetForm">Cancel</Button>
                        <Button type="submit">{{ isEditMode ? 'Update Location' : 'Add Location' }}</Button>
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


