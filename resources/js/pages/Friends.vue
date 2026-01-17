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
import { Users, Plus, Pencil, Trash2 } from 'lucide-vue-next';
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

const currentPage = ref(1);
const totalPages = ref(1);
const perPage = ref(15);
const total = ref(0);

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
};

const handleSubmit = async () => {
    try {
        if (isEditMode.value && editingId.value) {
            await axios.put(`/friends/${editingId.value}`, formData.value);
        } else {
            await axios.post('/friends', formData.value);
        }
        await fetchFriends(currentPage.value);
        resetForm();
    } catch (error) {
        console.error('Error saving friend:', error);
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

onMounted(() => {
    fetchFriends();
});
</script>

<template>
    <Head title="Friends" />
    <AppLayout title="Friends" :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="mx-auto w-full max-w-6xl">
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div>
                                <CardTitle class="flex items-center gap-2">
                                    <Users class="h-6 w-6" />
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
                    <CardContent>
                    <div class="rounded-md border">
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


