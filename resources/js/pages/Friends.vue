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
import { Users, Plus, Pencil, Trash2, Table as TableIcon, BarChart3, Fish, TrendingUp, Award, Calendar as CalendarIcon, AlertCircle } from 'lucide-vue-next';
import { Alert, AlertDescription } from '@/components/ui/alert';
import axios from '@/lib/axios';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Friends', href: '/friends' },
];

const showAddForm = ref(false);
const editingId = ref(null);
const isEditMode = ref(false);
const showDeleteConfirm = ref(false);
const itemToDelete = ref(null);
const friends = ref([]);
const friendStats = ref([]);
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
    name: '',
});

const fetchFriends = async (page = 1) => {
    try {
        const response = await axios.get('/friends', {
            params: { page: page, per_page: perPage.value }
        });
        friends.value = response.data.data;
        currentPage.value = response.data.current_page;
        totalPages.value = response.data.last_page;
        total.value = response.data.total;
    } catch (error) {
        console.error('Error fetching friends:', error);
    }
};

const goToPage = (page: number) => {
    if (page >= 1 && page <= totalPages.value) {
        fetchFriends(page);
    }
};

const nextPage = () => {
    if (currentPage.value < totalPages.value) {
        fetchFriends(currentPage.value + 1);
    }
};

const previousPage = () => {
    if (currentPage.value > 1) {
        fetchFriends(currentPage.value - 1);
    }
};

const editItem = (item: any) => {
    editingId.value = item.id;
    isEditMode.value = true;
    formData.value = {
        name: item.name,
    };
    showAddForm.value = true;
};

const resetForm = () => {
    formData.value = {
        name: '',
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
            await axios.put(`/friends/${editingId.value}`, formData.value);
        } else {
            await axios.post('/friends', formData.value);
        }
        await fetchFriends(currentPage.value);
        resetForm();
    } catch (error: any) {
        console.error('Error saving friend:', error);
        if (error.response?.status === 409) {
            errorMessage.value = error.response.data.message || 'This friend already exists.';
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
        await axios.delete(`/friends/${itemToDelete.value.id}`);
        await fetchFriends(currentPage.value);
        showDeleteConfirm.value = false;
        itemToDelete.value = null;
    } catch (error) {
        console.error('Error deleting friend:', error);
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
    } catch (error) {
        console.error('Error fetching available years:', error);
    }
};

// Fetch friend statistics
const fetchFriendStats = async () => {
    try {
        const response = await axios.get('/friends/stats/all', {
            params: { year: selectedYearFilter.value }
        });
        friendStats.value = response.data;
    } catch (error) {
        console.error('Error fetching friend statistics:', error);
    }
};

// Watch for year filter changes
watch(selectedYearFilter, () => {
    fetchFriendStats();
});

// Display year label
const yearLabel = computed(() => {
    return selectedYearFilter.value === 'lifetime' ? 'Lifetime' : selectedYearFilter.value;
});

onMounted(() => {
    fetchFriends();
    fetchAvailableYears();
    fetchFriendStats();
});
</script>

<template>
    <Head title="Friends" />
    <AppLayout title="Friends" :breadcrumbs="breadcrumbs">
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
                                                <Users class="h-5 w-5 text-teal-600 dark:text-teal-400" />
                                            </div>
                                            Your Fishing Friends
                                        </CardTitle>
                                        <CardDescription>
                                            View and manage your fishing friends
                                        </CardDescription>
                                    </div>
                                    <Button @click="resetForm(); showAddForm = true;" class="flex items-center gap-2">
                                        <Plus class="h-4 w-4" />
                                        Add New Friend
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
                                                <TableHead class="text-right">Actions</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            <TableRow v-if="friends.length === 0">
                                                <TableCell colspan="2" class="text-center text-muted-foreground py-8">
                                                    No friends yet. Click "Add New" to create your first friend!
                                                </TableCell>
                                            </TableRow>
                                            <TableRow v-for="item in friends" :key="item.id">
                                                <TableCell class="font-medium">{{ item.name }}</TableCell>
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
                                    <div v-if="friends.length === 0" class="text-center text-muted-foreground py-8 border rounded-md">
                                        No friends yet. Click "Add New" to create your first friend!
                                    </div>
                                    <Card v-for="item in friends" :key="item.id" class="overflow-hidden">
                                        <CardContent class="p-4">
                                            <div class="flex items-center justify-between gap-3">
                                                <div class="font-semibold text-base">{{ item.name }}</div>
                                                <div class="flex gap-1">
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
                                        <Users class="h-6 w-6" />
                                        Fishing Buddy Stats
                                    </h3>
                                    <p class="text-muted-foreground">Statistics {{ yearLabel === 'Lifetime' ? 'across all time' : 'for ' + yearLabel }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <label for="friend-year-filter" class="text-sm font-medium">Filter by:</label>
                                    <NativeSelect v-model="selectedYearFilter" id="friend-year-filter" class="w-[140px]">
                                        <NativeSelectOption value="lifetime">Lifetime</NativeSelectOption>
                                        <NativeSelectOption v-for="year in availableYears" :key="year" :value="year">
                                            {{ year }}
                                        </NativeSelectOption>
                                    </NativeSelect>
                                </div>
                            </div>

                            <!-- Friend Stats Grid -->
                            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                                <Card v-for="friend in friendStats" :key="friend.id" class="bg-gradient-to-br from-teal-50/30 to-transparent dark:from-teal-950/10">
                                    <CardHeader>
                                        <CardTitle class="text-lg flex items-center gap-2">
                                            <div class="rounded-full bg-teal-100 p-1.5 dark:bg-teal-900/30">
                                                <Users class="h-4 w-4 text-teal-600 dark:text-teal-400" />
                                            </div>
                                            {{ friend.name }}
                                        </CardTitle>
                                        <CardDescription>
                                            Fishing companion
                                        </CardDescription>
                                    </CardHeader>
                                    <CardContent class="space-y-3">
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-muted-foreground flex items-center gap-2">
                                                <CalendarIcon class="h-4 w-4 text-blue-500 dark:text-blue-400" />
                                                Trips Together
                                            </span>
                                            <span class="font-bold text-blue-700 dark:text-blue-300">{{ friend.totalTrips }}</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-muted-foreground flex items-center gap-2">
                                                <Fish class="h-4 w-4 text-emerald-500 dark:text-emerald-400" />
                                                Total Fish
                                            </span>
                                            <span class="font-bold text-emerald-700 dark:text-emerald-300">{{ friend.totalFish }}</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-muted-foreground flex items-center gap-2">
                                                <Award class="h-4 w-4 text-amber-500 dark:text-amber-400" />
                                                Biggest Fish
                                            </span>
                                            <span class="font-bold text-amber-700 dark:text-amber-300">{{ formatSize(friend.biggestFish) }}"</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-muted-foreground flex items-center gap-2">
                                                <TrendingUp class="h-4 w-4 text-red-500 dark:text-red-400" />
                                                Success Rate
                                            </span>
                                            <span class="font-bold text-red-700 dark:text-red-300">{{ friend.successRate }}%</span>
                                        </div>
                                    </CardContent>
                                </Card>
                            </div>

                            <!-- Empty State -->
                            <Card v-if="friendStats.length === 0" class="bg-gradient-to-br from-gray-50/30 to-transparent dark:from-gray-950/10">
                                <CardContent class="py-8">
                                    <div class="text-center text-muted-foreground">
                                        No friend statistics available yet. Start logging your fishing trips!
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
                        <Users class="h-6 w-6" />
                        {{ isEditMode ? 'Edit Friend' : 'Add New Friend' }}
                    </DialogTitle>
                    <DialogDescription>
                        {{ isEditMode ? 'Update the friend details below.' : 'Enter the friend details below.' }}
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="handleSubmit">
                    <div class="grid gap-4 py-4">
                        <div class="grid gap-2">
                            <Label for="name">Name *</Label>
                            <Input id="name" v-model="formData.name" required placeholder="e.g., John Doe" />
                        </div>
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="resetForm">Cancel</Button>
                        <Button type="submit">{{ isEditMode ? 'Update Friend' : 'Add Friend' }}</Button>
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
                        Delete Friend
                    </DialogTitle>
                    <DialogDescription>
                        Are you sure you want to delete this friend? This action cannot be undone.
                    </DialogDescription>
                </DialogHeader>
                <div v-if="itemToDelete" class="py-4">
                    <div class="rounded-lg bg-muted p-4 space-y-2">
                        <p class="text-sm font-medium">Friend Details:</p>
                        <p class="text-sm text-muted-foreground">
                            <strong>Name:</strong> {{ itemToDelete.name }}
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


