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
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, computed, watch } from 'vue';
import { Bug, Plus, Pencil, Trash2, Table as TableIcon, BarChart3, Fish, TrendingUp, Award, Calendar as CalendarIcon } from 'lucide-vue-next';
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

const currentPage = ref(1);
const totalPages = ref(1);
const perPage = ref(15);
const total = ref(0);

// Year filter
const currentYear = new Date().getFullYear().toString();
const selectedYearFilter = ref(currentYear);
const availableYears = ref<string[]>([]);

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
};

const handleSubmit = async () => {
    try {
        if (isEditMode.value && editingId.value) {
            await axios.put(`/flies/${editingId.value}`, formData.value);
        } else {
            await axios.post('/flies', formData.value);
        }
        await fetchFlies(currentPage.value);
        resetForm();
    } catch (error) {
        console.error('Error saving fly:', error);
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
        const response = await axios.get('/fishing-logs');
        const logs = response.data.data;
        const years = [...new Set(logs.map((log: any) => new Date(log.date).getFullYear().toString()))];
        availableYears.value = years.sort((a, b) => parseInt(b) - parseInt(a));
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
watch(selectedYearFilter, () => {
    fetchFlyStats();
});

// Display year label
const yearLabel = computed(() => {
    return selectedYearFilter.value === 'lifetime' ? 'Lifetime' : selectedYearFilter.value;
});

onMounted(() => {
    fetchFlies();
    fetchAvailableYears();
    fetchFlyStats();
});
</script>

<template>
    <Head title="Flies" />
    <AppLayout title="Flies" :breadcrumbs="breadcrumbs">
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
                        <Card>
                            <CardHeader>
                                <div class="flex items-center justify-between">
                                    <div>
                                        <CardTitle class="flex items-center gap-2">
                                            <Bug class="h-6 w-6" />
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
                                <div class="rounded-md border">
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
                                        :class="{ 'bg-accent': page === currentPage }" class="h-9 w-9">
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

                            <!-- Fly Stats Grid -->
                            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                                <Card v-for="fly in flyStats" :key="fly.id">
                                    <CardHeader>
                                        <CardTitle class="text-lg">{{ fly.name }}</CardTitle>
                                        <CardDescription>
                                            {{ fly.color }} - Size {{ fly.size }} - {{ fly.type }}
                                        </CardDescription>
                                    </CardHeader>
                                    <CardContent class="space-y-3">
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-muted-foreground flex items-center gap-2">
                                                <Fish class="h-4 w-4" />
                                                Total Caught
                                            </span>
                                            <span class="font-bold">{{ fly.totalCaught }}</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-muted-foreground flex items-center gap-2">
                                                <CalendarIcon class="h-4 w-4" />
                                                Total Trips
                                            </span>
                                            <span class="font-bold">{{ fly.totalTrips }}</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-muted-foreground flex items-center gap-2">
                                                <Award class="h-4 w-4" />
                                                Biggest Fish
                                            </span>
                                            <span class="font-bold">{{ formatSize(fly.biggestFish) }}"</span>
                                        </div>
                                    </CardContent>
                                </Card>
                            </div>

                            <!-- Empty State -->
                            <Card v-if="flyStats.length === 0">
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


