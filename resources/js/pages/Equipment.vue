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
import { Wrench, Plus, Pencil, Trash2 } from 'lucide-vue-next';
import axios from '@/lib/axios';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Equipment', href: '/equipment' },
];

const showAddForm = ref(false);
const editingId = ref(null);
const isEditMode = ref(false);
const showDeleteConfirm = ref(false);
const itemToDelete = ref(null);
const equipment = ref([]);

const currentPage = ref(1);
const totalPages = ref(1);
const perPage = ref(15);
const total = ref(0);

const formData = ref({
    rod_name: '',
    rod_weight: '',
    reel: '',
    line: '',
    tippet: '',
});

const fetchEquipment = async (page = 1) => {
    try {
        const response = await axios.get('/equipment', {
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
        reel: item.reel || '',
        line: item.line || '',
        tippet: item.tippet || '',
    };
    showAddForm.value = true;
};

const resetForm = () => {
    formData.value = {
        rod_name: '',
        rod_weight: '',
        reel: '',
        line: '',
        tippet: '',
    };
    editingId.value = null;
    isEditMode.value = false;
    showAddForm.value = false;
};

const handleSubmit = async () => {
    try {
        if (isEditMode.value && editingId.value) {
            await axios.put(`/equipment/${editingId.value}`, formData.value);
        } else {
            await axios.post('/equipment', formData.value);
        }
        await fetchEquipment(currentPage.value);
        resetForm();
    } catch (error) {
        console.error('Error saving equipment:', error);
    }
};

const confirmDelete = (item: any) => {
    itemToDelete.value = item;
    showDeleteConfirm.value = true;
};

const handleDelete = async () => {
    if (!itemToDelete.value) return;
    try {
        await axios.delete(`/equipment/${itemToDelete.value.id}`);
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

onMounted(() => {
    fetchEquipment();
});
</script>

<template>
    <Head title="Equipment" />
    <AppLayout title="Equipment" :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="mx-auto w-full max-w-6xl">
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div>
                                <CardTitle class="flex items-center gap-2">
                                    <Wrench class="h-6 w-6" />
                                    Your Equipment
                                </CardTitle>
                                <CardDescription>
                                    View and manage your fishing equipment
                                </CardDescription>
                            </div>
                            <Button @click="resetForm(); showAddForm = true;" class="flex items-center gap-2">
                                <Plus class="h-4 w-4" />
                                Add New Equipment
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent>
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Rod Name</TableHead>
                                    <TableHead>Rod Weight</TableHead>
                                    <TableHead>Reel</TableHead>
                                    <TableHead>Line</TableHead>
                                    <TableHead>Tippet</TableHead>
                                    <TableHead class="text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-if="equipment.length === 0">
                                    <TableCell colspan="6" class="text-center text-muted-foreground py-8">
                                        No equipment yet. Click "Add New" to create your first equipment!
                                    </TableCell>
                                </TableRow>
                                <TableRow v-for="item in equipment" :key="item.id">
                                    <TableCell class="font-medium">{{ item.rod_name }}</TableCell>
                                    <TableCell>{{ item.rod_weight || '-' }}</TableCell>
                                    <TableCell>{{ item.reel || '-' }}</TableCell>
                                    <TableCell>{{ item.line || '-' }}</TableCell>
                                    <TableCell>{{ item.tippet || '-' }}</TableCell>
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
        </div>
        </div>

        <!-- Add/Edit Dialog -->
        <Dialog v-model:open="showAddForm">
            <DialogContent class="max-w-2xl">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2">
                        <Wrench class="h-6 w-6" />
                        {{ isEditMode ? 'Edit Equipment' : 'Add New Equipment' }}
                    </DialogTitle>
                    <DialogDescription>
                        {{ isEditMode ? 'Update the equipment details below.' : 'Enter the equipment details below.' }}
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
                            <Label for="reel">Reel</Label>
                            <Input id="reel" v-model="formData.reel" placeholder="e.g., Orvis Battenkill" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="line">Line</Label>
                            <Input id="line" v-model="formData.line" placeholder="e.g., WF5F" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="tippet">Tippet</Label>
                            <Input id="tippet" v-model="formData.tippet" placeholder="e.g., 4X" />
                        </div>
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="resetForm">Cancel</Button>
                        <Button type="submit">{{ isEditMode ? 'Update Equipment' : 'Add Equipment' }}</Button>
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
                        Delete Equipment
                    </DialogTitle>
                    <DialogDescription>
                        Are you sure you want to delete this equipment? This action cannot be undone.
                    </DialogDescription>
                </DialogHeader>
                <div v-if="itemToDelete" class="py-4">
                    <div class="rounded-lg bg-muted p-4 space-y-2">
                        <p class="text-sm font-medium">Equipment Details:</p>
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


