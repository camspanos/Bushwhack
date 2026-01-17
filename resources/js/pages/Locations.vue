<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Pagination, PaginationContent, PaginationItem, PaginationNext, PaginationPrevious } from '@/components/ui/pagination';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import { MapPin, Plus, Pencil, Trash2 } from 'lucide-vue-next';
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

// Pagination
const currentPage = ref(1);
const totalPages = ref(1);
const perPage = ref(15);
const total = ref(0);

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
};

// Submit form
const handleSubmit = async () => {
    try {
        let response;
        if (isEditMode.value && editingId.value) {
            response = await axios.put(`/locations/${editingId.value}`, formData.value);
        } else {
            response = await axios.post('/locations', formData.value);
        }
        await fetchLocations(currentPage.value);
        resetForm();
    } catch (error) {
        console.error('Error saving location:', error);
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

onMounted(() => {
    fetchLocations();
});
</script>

<template>
    <Head title="Locations" />
    <AppLayout title="Locations" :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="mx-auto w-full max-w-6xl">
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
                    <CardContent>
                    <div class="rounded-md border">
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


