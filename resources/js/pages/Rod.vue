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
import { Wrench, Plus, Pencil, Trash2, Table as TableIcon, BarChart3, Fish, TrendingUp, Award, Calendar as CalendarIcon, AlertCircle } from 'lucide-vue-next';
import { Alert, AlertDescription } from '@/components/ui/alert';
import axios from '@/lib/axios';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Rods', href: '/rods-page' },
];

const showAddForm = ref(false);
const editingId = ref(null);
const isEditMode = ref(false);
const showDeleteConfirm = ref(false);
const itemToDelete = ref(null);
const equipment = ref([]);
const equipmentStats = ref([]);
const errorMessage = ref('');

const currentPage = ref(1);
const totalPages = ref(1);
const perPage = ref(15);
const total = ref(0);

// Year filter
const currentYear = new Date().getFullYear().toString();
const selectedYearFilter = ref(currentYear);
const availableYears = ref<string[]>([]);

const formData = ref({
    rod_name: '',
    rod_weight: '',
    rod_length: '',
    reel: '',
    line: '',
});

const fetchEquipment = async (page = 1) => {
    try {
        const response = await axios.get('/rods', {
            params: { page: page, per_page: perPage.value }
        });
        equipment.value = response.data.data;
        currentPage.value = response.data.current_page;
        totalPages.value = response.data.last_page;
        total.value = response.data.total;
    } catch (error) {
        console.error('Error fetching equipment:', error);
    }
};

const goToPage = (page: number) => {
    if (page >= 1 && page <= totalPages.value) {
        fetchEquipment(page);
    }
};

const nextPage = () => {
    if (currentPage.value < totalPages.value) {
        fetchEquipment(currentPage.value + 1);
    }
};

const previousPage = () => {
    if (currentPage.value > 1) {
        fetchEquipment(currentPage.value - 1);
    }
};

const editItem = (item: any) => {
    editingId.value = item.id;
    isEditMode.value = true;
    formData.value = {
        rod_name: item.rod_name,
        rod_weight: item.rod_weight || '',
        rod_length: item.rod_length || '',
        reel: item.reel || '',
        line: item.line || '',
    };
    showAddForm.value = true;
};

const resetForm = () => {
    formData.value = {
        rod_name: '',
        rod_weight: '',
        rod_length: '',
        reel: '',
        line: '',
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
            await axios.put(`/rods/${editingId.value}`, formData.value);
        } else {
            await axios.post('/rods', formData.value);
        }
        await fetchEquipment(currentPage.value);
        resetForm();
    } catch (error: any) {
        console.error('Error saving equipment:', error);
        if (error.response?.status === 409) {
            errorMessage.value = error.response.data.message || 'This rod already exists.';
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
        await axios.delete(`/rods/${itemToDelete.value.id}`);
        await fetchEquipment(currentPage.value);
        showDeleteConfirm.value = false;
        itemToDelete.value = null;
    } catch (error) {
        console.error('Error deleting equipment:', error);
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

// Fetch equipment statistics
const fetchEquipmentStats = async () => {
    try {
        const response = await axios.get('/rods/stats/all', {
            params: { year: selectedYearFilter.value }
        });
        equipmentStats.value = response.data;
    } catch (error) {
        console.error('Error fetching equipment statistics:', error);
    }
};

// Watch for year filter changes
watch(selectedYearFilter, () => {
    fetchEquipmentStats();
});

// Display year label
const yearLabel = computed(() => {
    return selectedYearFilter.value === 'lifetime' ? 'Lifetime' : selectedYearFilter.value;
});

// Format size to remove unnecessary decimals
const formatSize = (size: number) => {
    return size % 1 === 0 ? size.toString() : size.toFixed(2).replace(/\.?0+$/, '');
};

onMounted(() => {
    fetchEquipment();
    fetchAvailableYears();
    fetchEquipmentStats();
});
</script>

<template>
    <Head title="Rods" />
    <AppLayout title="Rods" :breadcrumbs="breadcrumbs">
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
                        <Card class="bg-gradient-to-br from-teal-50/30 to-transparent dark:from-teal-950/10">
                            <CardHeader>
                                <div class="flex items-center justify-between">
                                    <div>
                                        <CardTitle class="flex items-center gap-2">
                                            <div class="rounded-full bg-teal-100 p-1.5 dark:bg-teal-900/30">
                                                <Wrench class="h-5 w-5 text-teal-600 dark:text-teal-400" />
                                            </div>
                                            Your Rods
                                        </CardTitle>
                                        <CardDescription>
                                            View and manage your fishing rods
                                        </CardDescription>
                                    </div>
                                    <Button @click="resetForm(); showAddForm = true;" class="flex items-center gap-2">
                                        <Plus class="h-4 w-4" />
                                        Add New Rod
                                    </Button>
                                </div>
                            </CardHeader>
                            <CardContent class="p-6">
                                <!-- Desktop Table View -->
                                <div class="hidden md:block rounded-md border">
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>Rod Name</TableHead>
                                                <TableHead>Rod Weight</TableHead>
                                                <TableHead>Rod Length</TableHead>
                                                <TableHead>Reel</TableHead>
                                                <TableHead>Line</TableHead>
                                                <TableHead class="text-right">Actions</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            <TableRow v-if="equipment.length === 0">
                                                <TableCell colspan="6" class="text-center text-muted-foreground py-8">
                                                    No rods yet. Click "Add New" to create your first rod!
                                                </TableCell>
                                            </TableRow>
                                            <TableRow v-for="item in equipment" :key="item.id">
                                                <TableCell class="font-medium">{{ item.rod_name }}</TableCell>
                                                <TableCell>{{ item.rod_weight || '-' }}</TableCell>
                                                <TableCell>{{ item.rod_length || '-' }}</TableCell>
                                                <TableCell>{{ item.reel || '-' }}</TableCell>
                                                <TableCell>{{ item.line || '-' }}</TableCell>
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
                                    <div v-if="equipment.length === 0" class="text-center text-muted-foreground py-8 border rounded-md">
                                        No rods yet. Click "Add New" to create your first rod!
                                    </div>
                                    <Card v-for="item in equipment" :key="item.id" class="overflow-hidden">
                                        <CardContent class="p-4">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="flex-1 space-y-2">
                                                    <div class="font-semibold text-base">{{ item.rod_name }}</div>
                                                    <div class="grid grid-cols-2 gap-2 text-sm">
                                                        <div>
                                                            <span class="text-muted-foreground">Weight:</span>
                                                            <span class="ml-1">{{ item.rod_weight || '-' }}</span>
                                                        </div>
                                                        <div>
                                                            <span class="text-muted-foreground">Length:</span>
                                                            <span class="ml-1">{{ item.rod_length || '-' }}</span>
                                                        </div>
                                                        <div>
                                                            <span class="text-muted-foreground">Reel:</span>
                                                            <span class="ml-1">{{ item.reel || '-' }}</span>
                                                        </div>
                                                        <div>
                                                            <span class="text-muted-foreground">Line:</span>
                                                            <span class="ml-1">{{ item.line || '-' }}</span>
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
                                        <Wrench class="h-6 w-6" />
                                        Rod Performance
                                    </h3>
                                    <p class="text-muted-foreground">Statistics {{ yearLabel === 'Lifetime' ? 'across all time' : 'for ' + yearLabel }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <label for="equipment-year-filter" class="text-sm font-medium">Filter by:</label>
                                    <NativeSelect v-model="selectedYearFilter" id="equipment-year-filter" class="w-[140px]">
                                        <NativeSelectOption value="lifetime">Lifetime</NativeSelectOption>
                                        <NativeSelectOption v-for="year in availableYears" :key="year" :value="year">
                                            {{ year }}
                                        </NativeSelectOption>
                                    </NativeSelect>
                                </div>
                            </div>

                            <!-- Equipment Stats Grid -->
                            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                                <Card v-for="equip in equipmentStats" :key="equip.id" class="bg-gradient-to-br from-teal-50/30 to-transparent dark:from-teal-950/10">
                                    <CardHeader>
                                        <CardTitle class="text-lg flex items-center gap-2">
                                            <div class="rounded-full bg-teal-100 p-1.5 dark:bg-teal-900/30">
                                                <Wrench class="h-4 w-4 text-teal-600 dark:text-teal-400" />
                                            </div>
                                            {{ equip.rod_name }}
                                        </CardTitle>
                                        <CardDescription>
                                            {{ equip.rod_weight ? equip.rod_weight + ' wt' : 'No weight specified' }}
                                        </CardDescription>
                                    </CardHeader>
                                    <CardContent class="space-y-3">
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-muted-foreground flex items-center gap-2">
                                                <CalendarIcon class="h-4 w-4" />
                                                Total Trips
                                            </span>
                                            <span class="font-bold">{{ equip.totalTrips }}</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-muted-foreground flex items-center gap-2">
                                                <Fish class="h-4 w-4" />
                                                Total Fish
                                            </span>
                                            <span class="font-bold">{{ equip.totalFish }}</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-muted-foreground flex items-center gap-2">
                                                <Award class="h-4 w-4" />
                                                Biggest Fish
                                            </span>
                                            <span class="font-bold">{{ formatSize(equip.biggestFish) }}"</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-muted-foreground flex items-center gap-2">
                                                <TrendingUp class="h-4 w-4" />
                                                Success Rate
                                            </span>
                                            <span class="font-bold">{{ equip.successRate }}%</span>
                                        </div>
                                    </CardContent>
                                </Card>
                            </div>

                            <!-- Empty State -->
                            <Card v-if="equipmentStats.length === 0">
                                <CardContent class="py-8">
                                    <div class="text-center text-muted-foreground">
                                        No rod statistics available yet. Start logging your fishing trips!
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
                        <Wrench class="h-6 w-6" />
                        {{ isEditMode ? 'Edit Rod' : 'Add New Rod' }}
                    </DialogTitle>
                    <DialogDescription>
                        {{ isEditMode ? 'Update the rod details below.' : 'Enter the rod details below.' }}
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="handleSubmit">
                    <div class="grid gap-4 py-4">
                        <div class="grid gap-2">
                            <Label for="rod_name">Rod Name *</Label>
                            <Input id="rod_name" v-model="formData.rod_name" required placeholder="e.g., Orvis Clearwater" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="rod_weight">Rod Weight</Label>
                            <Input id="rod_weight" v-model="formData.rod_weight" placeholder="e.g., 5wt" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="rod_length">Rod Length</Label>
                            <Input id="rod_length" v-model="formData.rod_length" placeholder="e.g., 9ft" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="reel">Reel</Label>
                            <Input id="reel" v-model="formData.reel" placeholder="e.g., Orvis Battenkill" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="line">Line</Label>
                            <Input id="line" v-model="formData.line" placeholder="e.g., WF5F" />
                        </div>
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="resetForm">Cancel</Button>
                        <Button type="submit">{{ isEditMode ? 'Update Rod' : 'Add Rod' }}</Button>
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
                        Delete Rod
                    </DialogTitle>
                    <DialogDescription>
                        Are you sure you want to delete this rod? This action cannot be undone.
                    </DialogDescription>
                </DialogHeader>
                <div v-if="itemToDelete" class="py-4">
                    <div class="rounded-lg bg-muted p-4 space-y-2">
                        <p class="text-sm font-medium">Rod Details:</p>
                        <p class="text-sm text-muted-foreground">
                            <strong>Rod Name:</strong> {{ itemToDelete.rod_name }}
                        </p>
                        <p class="text-sm text-muted-foreground" v-if="itemToDelete.rod_weight">
                            <strong>Rod Weight:</strong> {{ itemToDelete.rod_weight }}
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


