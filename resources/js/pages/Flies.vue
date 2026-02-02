<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import PremiumFeatureDialog from '@/components/PremiumFeatureDialog.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Pagination, PaginationContent, PaginationItem, PaginationNext, PaginationPrevious } from '@/components/ui/pagination';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { NativeSelect, NativeSelectOption } from '@/components/ui/native-select';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage, router } from '@inertiajs/vue3';
import { ref, onMounted, computed, watch, nextTick } from 'vue';
import { Bug, Plus, Pencil, Trash2, Table as TableIcon, BarChart3, Fish, TrendingUp, Award, Calendar as CalendarIcon, AlertCircle, Palette, Crown } from 'lucide-vue-next';
import { Alert, AlertDescription } from '@/components/ui/alert';
import axios from '@/lib/axios';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Flies', href: '/flies' },
];

const showAddForm = ref(false);
const editingId = ref(null);
const isEditMode = ref(false);
const showDeleteConfirm = ref(false);
const itemToDelete = ref(null);
const flies = ref([]);
const flyStats = ref([]);
const errorMessage = ref('');

const currentPage = ref(1);
const totalPages = ref(1);
const perPage = ref(25);
const total = ref(0);

// Year filter
const currentYear = new Date().getFullYear().toString();
const selectedYearFilter = ref(currentYear);
const availableYears = ref<string[]>([]);
const showPremiumDialog = ref(false);
const page = usePage();

const formData = ref({
    name: '',
    color: '',
    size: '',
    type: '',
});

const fetchFlies = async (page = 1) => {
    try {
        const response = await axios.get('/flies', {
            params: { page: page, per_page: perPage.value }
        });
        flies.value = response.data.data;
        currentPage.value = response.data.current_page;
        totalPages.value = response.data.last_page;
        total.value = response.data.total;
    } catch (error) {
        console.error('Error fetching flies:', error);
    }
};

const goToPage = (page: number) => {
    if (page >= 1 && page <= totalPages.value) {
        fetchFlies(page);
    }
};

const nextPage = () => {
    if (currentPage.value < totalPages.value) {
        fetchFlies(currentPage.value + 1);
    }
};

const previousPage = () => {
    if (currentPage.value > 1) {
        fetchFlies(currentPage.value - 1);
    }
};

// Handle per page change
const handlePerPageChange = () => {
    currentPage.value = 1; // Reset to first page when changing per page
    fetchFlies(1);
};

const editItem = (item: any) => {
    editingId.value = item.id;
    isEditMode.value = true;
    formData.value = {
        name: item.name,
        color: item.color || '',
        size: item.size || '',
        type: item.type || '',
    };
    showAddForm.value = true;
};

const resetForm = () => {
    formData.value = {
        name: '',
        color: '',
        size: '',
        type: '',
    };
    editingId.value = null;
    isEditMode.value = false;
    showAddForm.value = false;
    errorMessage.value = '';
};

const handleSubmit = async () => {
    errorMessage.value = '';
    try {
        if (isEditMode.value && editingId.value) {
            await axios.put(`/flies/${editingId.value}`, formData.value);
        } else {
            await axios.post('/flies', formData.value);
        }
        await fetchFlies(currentPage.value);
        resetForm();
    } catch (error: any) {
        console.error('Error saving fly:', error);
        if (error.response?.status === 409) {
            errorMessage.value = error.response.data.message || 'This fly already exists.';
            showAddForm.value = false;
        }
    }
};

const confirmDelete = (item: any) => {
    itemToDelete.value = item;
    showDeleteConfirm.value = true;
};

const handleDelete = async () => {
    if (!itemToDelete.value) return;
    try {
        await axios.delete(`/flies/${itemToDelete.value.id}`);
        await fetchFlies(currentPage.value);
        showDeleteConfirm.value = false;
        itemToDelete.value = null;
    } catch (error) {
        console.error('Error deleting fly:', error);
    }
};

const cancelDelete = () => {
    showDeleteConfirm.value = false;
    itemToDelete.value = null;
};

// Format size for display (remove .0 decimals)
const formatSize = (size: number) => {
    if (!size) return '0';
    const num = parseFloat(size.toString());
    return num % 1 === 0 ? Math.floor(num).toString() : num.toString();
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

// Fetch fly statistics
const fetchFlyStats = async () => {
    try {
        const response = await axios.get('/flies/stats/all', {
            params: { year: selectedYearFilter.value }
        });
        flyStats.value = response.data;
    } catch (error) {
        console.error('Error fetching fly statistics:', error);
    }
};

// Watch for year filter changes
watch(selectedYearFilter, async (newYear, oldYear) => {
    // Check if user is trying to select a non-current year and is not premium
    if (!page.props.auth.isPremium && newYear !== currentYear) {
        // Show premium dialog
        showPremiumDialog.value = true;
        // Revert the selection after showing dialog
        await nextTick();
        selectedYearFilter.value = oldYear;
        return;
    }

    fetchFlyStats();
});

// Display year label
const yearLabel = computed(() => {
    return selectedYearFilter.value === 'lifetime' ? 'Lifetime' : selectedYearFilter.value;
});

onMounted(async () => {
    fetchFlies();
    await fetchAvailableYears(); // Wait for years to be fetched and default year to be set
    fetchFlyStats(); // Now fetch stats with the correct default year
});
</script>

<template>
    <Head title="Flies" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="w-full">
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
                        <Card class="bg-gradient-to-br from-teal-50/30 to-transparent dark:from-teal-950/10">
                            <CardHeader>
                                <div class="flex items-center justify-between">
                                    <div>
                                        <CardTitle class="flex items-center gap-2">
                                            <div class="rounded-full bg-teal-100 p-1.5 dark:bg-teal-900/30">
                                                <Bug class="h-5 w-5 text-teal-600 dark:text-teal-400" />
                                            </div>
                                            Your Flies
                                        </CardTitle>
                                        <CardDescription>
                                            View and manage your fly collection
                                        </CardDescription>
                                    </div>
                                    <Button @click="resetForm(); showAddForm = true;" class="flex items-center gap-2">
                                        <Plus class="h-4 w-4" />
                                        Add New Fly
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
                                                <TableHead>Color</TableHead>
                                                <TableHead>Size</TableHead>
                                                <TableHead>Type</TableHead>
                                                <TableHead class="text-right">Actions</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            <TableRow v-if="flies.length === 0">
                                                <TableCell colspan="5" class="text-center text-muted-foreground py-8">
                                                    No flies yet. Click "Add New" to create your first fly!
                                                </TableCell>
                                            </TableRow>
                                            <TableRow v-for="item in flies" :key="item.id">
                                                <TableCell class="font-medium">{{ item.name }}</TableCell>
                                                <TableCell>{{ item.color || '-' }}</TableCell>
                                                <TableCell>{{ item.size || '-' }}</TableCell>
                                                <TableCell>{{ item.type || '-' }}</TableCell>
                                                <TableCell class="text-right">
                                                    <div class="flex items-center justify-end gap-0">
                                                        <Button variant="ghost" size="icon" @click="editItem(item)" class="h-8 w-8">
                                                            <Pencil class="h-4 w-4" />
                                                        </Button>
                                                        <Button variant="ghost" size="icon" @click="confirmDelete(item)"
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
                                    <div v-if="flies.length === 0" class="text-center text-muted-foreground py-8 border rounded-md">
                                        No flies yet. Click "Add New" to create your first fly!
                                    </div>
                                    <Card v-for="item in flies" :key="item.id" class="overflow-hidden">
                                        <CardContent class="p-4">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="flex-1 space-y-2">
                                                    <div class="font-semibold text-base">{{ item.name }}</div>
                                                    <div class="grid grid-cols-2 gap-2 text-sm">
                                                        <div>
                                                            <span class="text-muted-foreground">Color:</span>
                                                            <span class="ml-1">{{ item.color || '-' }}</span>
                                                        </div>
                                                        <div>
                                                            <span class="text-muted-foreground">Size:</span>
                                                            <span class="ml-1">{{ item.size || '-' }}</span>
                                                        </div>
                                                        <div class="col-span-2">
                                                            <span class="text-muted-foreground">Type:</span>
                                                            <span class="ml-1">{{ item.type || '-' }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="flex flex-col gap-1">
                                                    <Button variant="ghost" size="icon-sm" @click="editItem(item)">
                                                        <Pencil class="h-4 w-4" />
                                                    </Button>
                                                    <Button variant="ghost" size="icon-sm" @click="confirmDelete(item)"
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
                                        <Bug class="h-6 w-6" />
                                        Fly Performance
                                    </h3>
                                    <p class="text-muted-foreground">Statistics {{ yearLabel === 'Lifetime' ? 'across all time' : 'for ' + yearLabel }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <label for="fly-year-filter" class="text-sm font-medium">Filter by:</label>
                                    <NativeSelect v-model="selectedYearFilter" id="fly-year-filter" class="w-[140px]">
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

                            <!-- AdSense & Premium Upgrade Row (shown for non-premium users) -->
                            <div v-if="!page.props.auth.isPremium" class="grid gap-4 md:grid-cols-2 mb-4">
                                <!-- Google AdSense Card -->
                                <Card class="bg-gradient-to-br from-slate-50/50 to-transparent dark:from-slate-950/20">
                                    <CardHeader class="pb-2">
                                        <CardTitle class="text-sm font-medium text-muted-foreground">Advertisement</CardTitle>
                                    </CardHeader>
                                    <CardContent class="pt-0 pb-4">
                                        <div class="flex items-center justify-center min-h-[200px] bg-muted/30 rounded-lg border-2 border-dashed border-muted-foreground/20">
                                            <p class="text-sm text-muted-foreground">Google AdSense Placeholder</p>
                                        </div>
                                    </CardContent>
                                </Card>

                                <!-- Premium Upgrade Card -->
                                <Card class="bg-gradient-to-br from-amber-50/50 to-amber-100/30 dark:from-amber-950/20 dark:to-amber-900/10 border-amber-200/50 dark:border-amber-800/30">
                                    <CardHeader class="pb-2">
                                        <CardTitle class="flex items-center gap-2 text-lg">
                                            <div class="rounded-full bg-amber-100 p-2 dark:bg-amber-900/30">
                                                <Crown class="h-5 w-5 text-amber-600 dark:text-amber-400" />
                                            </div>
                                            <span class="text-amber-900 dark:text-amber-100">Upgrade to Premium</span>
                                        </CardTitle>
                                        <CardDescription class="text-amber-700/80 dark:text-amber-300/80">
                                            Unlock the full Bushwhack experience
                                        </CardDescription>
                                    </CardHeader>
                                    <CardContent class="pt-0 pb-4 space-y-4">
                                        <div class="space-y-2">
                                            <p class="text-sm text-amber-900/90 dark:text-amber-100/90 font-medium">
                                                Remove all advertisements and enjoy:
                                            </p>
                                            <ul class="text-sm text-amber-800/80 dark:text-amber-200/80 space-y-1 ml-4">
                                                <li class="flex items-start gap-2">
                                                    <span class="text-amber-600 dark:text-amber-400 mt-0.5">✓</span>
                                                    <span>Ad-free experience across all pages</span>
                                                </li>
                                                <li class="flex items-start gap-2">
                                                    <span class="text-amber-600 dark:text-amber-400 mt-0.5">✓</span>
                                                    <span>Historical data & year filtering</span>
                                                </li>
                                                <li class="flex items-start gap-2">
                                                    <span class="text-amber-600 dark:text-amber-400 mt-0.5">✓</span>
                                                    <span>Dashboard customization</span>
                                                </li>
                                            </ul>
                                        </div>
                                        <Button
                                            class="w-full bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white shadow-md"
                                            @click="router.visit('/settings/subscription')"
                                        >
                                            <Crown class="mr-2 h-4 w-4" />
                                            Upgrade Now
                                        </Button>
                                    </CardContent>
                                </Card>
                            </div>

                            <!-- Fly Stats Grid -->
                            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                                <Card v-for="fly in flyStats" :key="fly.id" class="bg-gradient-to-br from-teal-50/30 to-transparent dark:from-teal-950/10">
                                    <CardHeader>
                                        <CardTitle class="text-lg flex items-center gap-2">
                                            <div class="rounded-full bg-teal-100 p-1.5 dark:bg-teal-900/30">
                                                <Bug class="h-4 w-4 text-teal-600 dark:text-teal-400" />
                                            </div>
                                            {{ fly.name }}
                                        </CardTitle>
                                        <CardDescription>
                                            {{ fly.type }}
                                        </CardDescription>
                                    </CardHeader>
                                    <CardContent class="space-y-3">
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-muted-foreground flex items-center gap-2">
                                                <Fish class="h-4 w-4 text-emerald-500 dark:text-emerald-400" />
                                                Total Caught
                                            </span>
                                            <span class="font-bold text-emerald-700 dark:text-emerald-300">{{ fly.totalCaught }}</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-muted-foreground flex items-center gap-2">
                                                <CalendarIcon class="h-4 w-4 text-blue-500 dark:text-blue-400" />
                                                Total Trips
                                            </span>
                                            <span class="font-bold text-blue-700 dark:text-blue-300">{{ fly.totalTrips }}</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-muted-foreground flex items-center gap-2">
                                                <Award class="h-4 w-4 text-amber-500 dark:text-amber-400" />
                                                Biggest Fish
                                            </span>
                                            <span class="font-bold text-amber-700 dark:text-amber-300">{{ formatSize(fly.biggestFish) }}"</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-muted-foreground flex items-center gap-2">
                                                <Palette class="h-4 w-4 text-purple-500 dark:text-purple-400" />
                                                Favorite Color
                                            </span>
                                            <span class="font-bold text-purple-700 dark:text-purple-300">{{ fly.favoriteColor }}</span>
                                        </div>
                                    </CardContent>
                                </Card>
                            </div>

                            <!-- Empty State -->
                            <Card v-if="flyStats.length === 0" class="bg-gradient-to-br from-gray-50/30 to-transparent dark:from-gray-950/10">
                                <CardContent class="py-8">
                                    <div class="text-center text-muted-foreground">
                                        No fly statistics available yet. Start logging your fishing trips!
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
                        <Bug class="h-6 w-6" />
                        {{ isEditMode ? 'Edit Fly' : 'Add New Fly' }}
                    </DialogTitle>
                    <DialogDescription>
                        {{ isEditMode ? 'Update the fly details below.' : 'Enter the fly details below.' }}
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="handleSubmit">
                    <div class="grid gap-4 py-4">
                        <div class="grid gap-2">
                            <Label for="name">Name *</Label>
                            <Input id="name" v-model="formData.name" required placeholder="e.g., Woolly Bugger" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="color">Color</Label>
                            <Input id="color" v-model="formData.color" placeholder="e.g., Black" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="size">Size</Label>
                            <Input id="size" v-model="formData.size" placeholder="e.g., #8" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="type">Type</Label>
                            <Input id="type" v-model="formData.type" placeholder="e.g., Streamer" />
                        </div>
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="resetForm">Cancel</Button>
                        <Button type="submit">{{ isEditMode ? 'Update Fly' : 'Add Fly' }}</Button>
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
                        Delete Fly
                    </DialogTitle>
                    <DialogDescription>
                        Are you sure you want to delete this fly? This action cannot be undone.
                    </DialogDescription>
                </DialogHeader>
                <div v-if="itemToDelete" class="py-4">
                    <div class="rounded-lg bg-muted p-4 space-y-2">
                        <p class="text-sm font-medium">Fly Details:</p>
                        <p class="text-sm text-muted-foreground">
                            <strong>Name:</strong> {{ itemToDelete.name }}
                        </p>
                        <p class="text-sm text-muted-foreground" v-if="itemToDelete.color">
                            <strong>Color:</strong> {{ itemToDelete.color }}
                        </p>
                        <p class="text-sm text-muted-foreground" v-if="itemToDelete.size">
                            <strong>Size:</strong> {{ itemToDelete.size }}
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


