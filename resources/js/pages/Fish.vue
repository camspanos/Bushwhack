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
import { Fish as FishIcon, Plus, Pencil, Trash2, Table as TableIcon, BarChart3, TrendingUp, Award, Calendar as CalendarIcon } from 'lucide-vue-next';
import axios from '@/lib/axios';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Fish', href: '/fish' },
];

const showAddForm = ref(false);
const editingId = ref(null);
const isEditMode = ref(false);
const showDeleteConfirm = ref(false);
const itemToDelete = ref(null);
const fish = ref([]);
const fishStats = ref([]);

const currentPage = ref(1);
const totalPages = ref(1);
const perPage = ref(15);
const total = ref(0);

// Year filter
const currentYear = new Date().getFullYear().toString();
const selectedYearFilter = ref(currentYear);
const availableYears = ref<string[]>([]);

const formData = ref({
    species: '',
    water_type: '',
});

const fetchFish = async (page = 1) => {
    try {
        const response = await axios.get('/fish', {
            params: { page: page, per_page: perPage.value }
        });
        fish.value = response.data.data;
        currentPage.value = response.data.current_page;
        totalPages.value = response.data.last_page;
        total.value = response.data.total;
    } catch (error) {
        console.error('Error fetching fish:', error);
    }
};

const goToPage = (page: number) => {
    if (page >= 1 && page <= totalPages.value) {
        fetchFish(page);
    }
};

const nextPage = () => {
    if (currentPage.value < totalPages.value) {
        fetchFish(currentPage.value + 1);
    }
};

const previousPage = () => {
    if (currentPage.value > 1) {
        fetchFish(currentPage.value - 1);
    }
};

const editItem = (item: any) => {
    editingId.value = item.id;
    isEditMode.value = true;
    formData.value = {
        species: item.species,
        water_type: item.water_type || '',
    };
    showAddForm.value = true;
};

const resetForm = () => {
    formData.value = {
        species: '',
        water_type: '',
    };
    editingId.value = null;
    isEditMode.value = false;
    showAddForm.value = false;
};

const handleSubmit = async () => {
    try {
        if (isEditMode.value && editingId.value) {
            await axios.put(`/fish/${editingId.value}`, formData.value);
        } else {
            await axios.post('/fish', formData.value);
        }
        await fetchFish(currentPage.value);
        resetForm();
    } catch (error) {
        console.error('Error saving fish:', error);
    }
};

const confirmDelete = (item: any) => {
    itemToDelete.value = item;
    showDeleteConfirm.value = true;
};

const handleDelete = async () => {
    if (!itemToDelete.value) return;
    try {
        await axios.delete(`/fish/${itemToDelete.value.id}`);
        await fetchFish(currentPage.value);
        showDeleteConfirm.value = false;
        itemToDelete.value = null;
    } catch (error) {
        console.error('Error deleting fish:', error);
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

// Fetch fish statistics
const fetchFishStats = async () => {
    try {
        const response = await axios.get('/fish/stats/all', {
            params: { year: selectedYearFilter.value }
        });
        fishStats.value = response.data;
    } catch (error) {
        console.error('Error fetching fish statistics:', error);
    }
};

// Watch for year filter changes
watch(selectedYearFilter, () => {
    fetchFishStats();
});

// Display year label
const yearLabel = computed(() => {
    return selectedYearFilter.value === 'lifetime' ? 'Lifetime' : selectedYearFilter.value;
});

// Format size for display (remove .0 decimals)
const formatSize = (size: number) => {
    const num = parseFloat(size.toString());
    return num % 1 === 0 ? Math.floor(num).toString() : num.toString();
};

onMounted(() => {
    fetchFish();
    fetchAvailableYears();
    fetchFishStats();
});
</script>

<template>
    <Head title="Fish" />
    <AppLayout title="Fish" :breadcrumbs="breadcrumbs">
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
                                            <FishIcon class="h-6 w-6" />
                                            Your Fish Species
                                        </CardTitle>
                                        <CardDescription>
                                            View and manage your fish species
                                        </CardDescription>
                                    </div>
                                    <Button @click="resetForm(); showAddForm = true;" class="flex items-center gap-2">
                                        <Plus class="h-4 w-4" />
                                        Add New Fish Species
                                    </Button>
                                </div>
                            </CardHeader>
                            <CardContent class="p-6">
                                <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Species</TableHead>
                                    <TableHead>Water Type</TableHead>
                                    <TableHead class="text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-if="fish.length === 0">
                                    <TableCell colspan="3" class="text-center text-muted-foreground py-8">
                                        No fish species yet. Click "Add New" to create your first fish species!
                                    </TableCell>
                                </TableRow>
                                <TableRow v-for="item in fish" :key="item.id">
                                    <TableCell class="font-medium">{{ item.species }}</TableCell>
                                    <TableCell>{{ item.water_type || '-' }}</TableCell>
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
                                        <FishIcon class="h-6 w-6" />
                                        Fish Species Performance
                                    </h3>
                                    <p class="text-muted-foreground">Statistics {{ yearLabel === 'Lifetime' ? 'across all time' : 'for ' + yearLabel }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <label for="fish-year-filter" class="text-sm font-medium">Filter by:</label>
                                    <NativeSelect v-model="selectedYearFilter" id="fish-year-filter" class="w-[140px]">
                                        <NativeSelectOption value="lifetime">Lifetime</NativeSelectOption>
                                        <NativeSelectOption v-for="year in availableYears" :key="year" :value="year">
                                            {{ year }}
                                        </NativeSelectOption>
                                    </NativeSelect>
                                </div>
                            </div>

                            <!-- Fish Stats Grid -->
                            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                                <Card v-for="fishSpecies in fishStats" :key="fishSpecies.id">
                                    <CardHeader>
                                        <CardTitle class="text-lg">{{ fishSpecies.species }}</CardTitle>
                                        <CardDescription>
                                            {{ fishSpecies.water_type || 'No water type specified' }}
                                        </CardDescription>
                                    </CardHeader>
                                    <CardContent class="space-y-3">
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-muted-foreground flex items-center gap-2">
                                                <FishIcon class="h-4 w-4" />
                                                Total Caught
                                            </span>
                                            <span class="font-bold">{{ fishSpecies.totalCaught }}</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-muted-foreground flex items-center gap-2">
                                                <CalendarIcon class="h-4 w-4" />
                                                Total Trips
                                            </span>
                                            <span class="font-bold">{{ fishSpecies.totalTrips }}</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-muted-foreground flex items-center gap-2">
                                                <Award class="h-4 w-4" />
                                                Biggest Catch
                                            </span>
                                            <span class="font-bold">{{ formatSize(fishSpecies.biggestFish) }}"</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-muted-foreground flex items-center gap-2">
                                                <TrendingUp class="h-4 w-4" />
                                                Avg Size
                                            </span>
                                            <span class="font-bold">{{ formatSize(fishSpecies.avgSize) }}"</span>
                                        </div>
                                    </CardContent>
                                </Card>
                            </div>

                            <!-- Empty State -->
                            <Card v-if="fishStats.length === 0">
                                <CardContent class="py-8">
                                    <div class="text-center text-muted-foreground">
                                        No fish species statistics available yet. Start logging your fishing trips!
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
                        <FishIcon class="h-6 w-6" />
                        {{ isEditMode ? 'Edit Fish Species' : 'Add New Fish Species' }}
                    </DialogTitle>
                    <DialogDescription>
                        {{ isEditMode ? 'Update the fish species details below.' : 'Enter the fish species details below.' }}
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="handleSubmit">
                    <div class="grid gap-4 py-4">
                        <div class="grid gap-2">
                            <Label for="species">Species *</Label>
                            <Input id="species" v-model="formData.species" required placeholder="e.g., Rainbow Trout" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="water_type">Water Type</Label>
                            <Input id="water_type" v-model="formData.water_type" placeholder="e.g., Freshwater" />
                        </div>
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="resetForm">Cancel</Button>
                        <Button type="submit">{{ isEditMode ? 'Update Fish Species' : 'Add Fish Species' }}</Button>
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
                        Delete Fish Species
                    </DialogTitle>
                    <DialogDescription>
                        Are you sure you want to delete this fish species? This action cannot be undone.
                    </DialogDescription>
                </DialogHeader>
                <div v-if="itemToDelete" class="py-4">
                    <div class="rounded-lg bg-muted p-4 space-y-2">
                        <p class="text-sm font-medium">Fish Species Details:</p>
                        <p class="text-sm text-muted-foreground">
                            <strong>Species:</strong> {{ itemToDelete.species }}
                        </p>
                        <p class="text-sm text-muted-foreground" v-if="itemToDelete.water_type">
                            <strong>Water Type:</strong> {{ itemToDelete.water_type }}
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


