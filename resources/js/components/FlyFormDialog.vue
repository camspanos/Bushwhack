<template>
    <Dialog v-model:open="isOpen">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>{{ isEditMode ? 'Edit Fly Pattern' : 'Add New Fly' }}</DialogTitle>
                <DialogDescription>
                    {{ isEditMode ? 'Update the fly pattern details.' : 'Create a new fly pattern to add to your log.' }}
                </DialogDescription>
            </DialogHeader>

            <Alert v-if="errorMessage" variant="destructive" class="mb-4">
                <AlertCircle class="h-4 w-4" />
                <AlertDescription>{{ errorMessage }}</AlertDescription>
            </Alert>

            <form @submit.prevent="handleSubmit" class="space-y-4">
                <div class="grid gap-2">
                    <Label for="fly-name">Fly Name *</Label>
                    <Input
                        id="fly-name"
                        v-model="formData.name"
                        placeholder="e.g., Woolly Bugger"
                        required
                    />
                </div>

                <div class="grid gap-2">
                    <Label for="fly-color">Color</Label>
                    <Input
                        id="fly-color"
                        v-model="formData.color"
                        placeholder="e.g., Olive"
                    />
                </div>

                <div class="grid gap-2">
                    <Label for="fly-size">Size</Label>
                    <Input
                        id="fly-size"
                        v-model="formData.size"
                        placeholder="e.g., #12"
                    />
                </div>

                <div class="grid gap-2">
                    <Label for="fly-type">Type</Label>
                    <Input
                        id="fly-type"
                        v-model="formData.type"
                        placeholder="e.g., Streamer, Dry Fly"
                    />
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="handleCancel">
                        Cancel
                    </Button>
                    <Button type="submit">
                        {{ isEditMode ? 'Update Fly' : 'Add Fly' }}
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

interface FlyFormData {
    name: string;
    color: string;
    size: string;
    type: string;
}

const props = defineProps<{
    open: boolean;
    editingFly?: FlyFormData & { id?: number } | null;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    'success': [fly: any];
}>();

const isOpen = computed({
    get: () => props.open,
    set: (value) => emit('update:open', value),
});

const isEditMode = computed(() => !!props.editingFly?.id);
const errorMessage = ref('');

const formData = ref<FlyFormData>({
    name: '',
    color: '',
    size: '',
    type: '',
});

// Reset form function
const resetForm = () => {
    formData.value = {
        name: '',
        color: '',
        size: '',
        type: '',
    };
    errorMessage.value = '';
};

// Watch for editing fly changes
watch(() => props.editingFly, (newFly) => {
    if (newFly) {
        formData.value = {
            name: newFly.name || '',
            color: newFly.color || '',
            size: newFly.size || '',
            type: newFly.type || '',
        };
    } else {
        resetForm();
    }
}, { immediate: true });

// Watch for dialog open state to reset form when opening for new fly
watch(() => props.open, (isOpen) => {
    if (isOpen && !props.editingFly) {
        resetForm();
    }
});

const handleSubmit = async () => {
    errorMessage.value = '';
    try {
        const url = isEditMode.value
            ? `/flies/${props.editingFly!.id}`
            : '/flies';
        const method = isEditMode.value ? 'put' : 'post';

        const response = await axios[method](url, formData.value);
        emit('success', response.data);
        isOpen.value = false;
        resetForm();
    } catch (error: any) {
        console.error('Error saving fly:', error);
        if (error.response?.status === 409) {
            errorMessage.value = error.response.data.message || 'This fly already exists.';
        } else if (error.response?.status === 500) {
            errorMessage.value = error.response.data.message || 'Server error. Please try again.';
        } else if (error.response?.data?.message) {
            errorMessage.value = error.response.data.message;
        } else {
            errorMessage.value = 'An error occurred while saving the fly.';
        }
    }
};

const handleCancel = () => {
    isOpen.value = false;
    resetForm();
};
</script>

