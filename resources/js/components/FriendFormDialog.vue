<template>
    <Dialog v-model:open="isOpen">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>{{ isEditMode ? 'Edit Friend' : 'Add New Friend' }}</DialogTitle>
                <DialogDescription>
                    {{ isEditMode ? 'Update the friend details.' : 'Add a fishing buddy to your contacts.' }}
                </DialogDescription>
            </DialogHeader>

            <Alert v-if="errorMessage" variant="destructive" class="mb-4">
                <AlertCircle class="h-4 w-4" />
                <AlertDescription>{{ errorMessage }}</AlertDescription>
            </Alert>

            <form @submit.prevent="handleSubmit" class="space-y-4">
                <div class="grid gap-2">
                    <Label for="friend-name">Friend's Name *</Label>
                    <Input
                        id="friend-name"
                        v-model="formData.name"
                        placeholder="e.g., John Doe"
                        required
                    />
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="handleCancel">
                        Cancel
                    </Button>
                    <Button type="submit">
                        {{ isEditMode ? 'Update Friend' : 'Add Friend' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { AlertCircle } from 'lucide-vue-next';
import axios from '@/lib/axios';

interface FriendFormData {
    name: string;
}

const props = defineProps<{
    open: boolean;
    editingFriend?: FriendFormData & { id?: number } | null;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    'success': [friend: any];
}>();

const isOpen = computed({
    get: () => props.open,
    set: (value) => emit('update:open', value),
});

const isEditMode = computed(() => !!props.editingFriend?.id);
const errorMessage = ref('');

const formData = ref<FriendFormData>({
    name: '',
});

// Reset form function
const resetForm = () => {
    formData.value = {
        name: '',
    };
    errorMessage.value = '';
};

// Watch for editing friend changes
watch(() => props.editingFriend, (newFriend) => {
    if (newFriend) {
        formData.value = {
            name: newFriend.name || '',
        };
    } else {
        resetForm();
    }
}, { immediate: true });

// Watch for dialog open state to reset form when opening for new friend
watch(() => props.open, (isOpen) => {
    if (isOpen && !props.editingFriend) {
        resetForm();
    }
});

const handleSubmit = async () => {
    errorMessage.value = '';
    try {
        const url = isEditMode.value
            ? `/friends/${props.editingFriend!.id}`
            : '/friends';
        const method = isEditMode.value ? 'put' : 'post';

        const response = await axios[method](url, formData.value);
        emit('success', response.data);
        isOpen.value = false;
        resetForm();
    } catch (error: any) {
        console.error('Error saving friend:', error);
        if (error.response?.status === 409) {
            errorMessage.value = error.response.data.message || 'This friend already exists.';
        } else if (error.response?.status === 500) {
            errorMessage.value = error.response.data.message || 'Server error. Please try again.';
        } else if (error.response?.data?.message) {
            errorMessage.value = error.response.data.message;
        } else {
            errorMessage.value = 'An error occurred while saving the friend.';
        }
    }
};

const handleCancel = () => {
    isOpen.value = false;
    resetForm();
};
</script>

