<template>
    <Dialog v-model:open="isOpen">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>{{ isEditMode ? 'Edit Rod Setup' : 'Add New Rod' }}</DialogTitle>
                <DialogDescription>
                    {{ isEditMode ? 'Update the rod setup details.' : 'Create a new rod setup to add to your log.' }}
                </DialogDescription>
            </DialogHeader>

            <Alert v-if="errorMessage" variant="destructive" class="mb-4">
                <AlertCircle class="h-4 w-4" />
                <AlertDescription>{{ errorMessage }}</AlertDescription>
            </Alert>

            <form @submit.prevent="handleSubmit" class="space-y-4">
                <div class="grid gap-2">
                    <Label for="rod-name">Rod Name *</Label>
                    <Input
                        id="rod-name"
                        v-model="formData.rod_name"
                        placeholder="e.g., Orvis Clearwater"
                        required
                    />
                </div>

                <div class="grid gap-2">
                    <Label for="rod-weight">Rod Weight</Label>
                    <Input
                        id="rod-weight"
                        v-model="formData.rod_weight"
                        placeholder="e.g., 5wt"
                    />
                </div>

                <div class="grid gap-2">
                    <Label for="rod-length">Rod Length</Label>
                    <Input
                        id="rod-length"
                        v-model="formData.rod_length"
                        placeholder="e.g., 9ft"
                    />
                </div>

                <div class="grid gap-2">
                    <Label for="rod-reel">Reel</Label>
                    <Input
                        id="rod-reel"
                        v-model="formData.reel"
                        placeholder="e.g., Orvis Battenkill"
                    />
                </div>

                <div class="grid gap-2">
                    <Label for="rod-line">Line</Label>
                    <Input
                        id="rod-line"
                        v-model="formData.line"
                        placeholder="e.g., WF5F"
                    />
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="handleCancel">
                        Cancel
                    </Button>
                    <Button type="submit">
                        {{ isEditMode ? 'Update Rod' : 'Add Rod' }}
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

interface RodFormData {
    rod_name: string;
    rod_weight: string;
    rod_length: string;
    reel: string;
    line: string;
}

const props = defineProps<{
    open: boolean;
    editingRod?: RodFormData & { id?: number } | null;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    'success': [rod: any];
}>();

const isOpen = computed({
    get: () => props.open,
    set: (value) => emit('update:open', value),
});

const isEditMode = computed(() => !!props.editingRod?.id);
const errorMessage = ref('');

const formData = ref<RodFormData>({
    rod_name: '',
    rod_weight: '',
    rod_length: '',
    reel: '',
    line: '',
});

// Reset form function
const resetForm = () => {
    formData.value = {
        rod_name: '',
        rod_weight: '',
        rod_length: '',
        reel: '',
        line: '',
    };
    errorMessage.value = '';
};

// Watch for editing rod changes
watch(() => props.editingRod, (newRod) => {
    if (newRod) {
        formData.value = {
            rod_name: newRod.rod_name || '',
            rod_weight: newRod.rod_weight || '',
            rod_length: newRod.rod_length || '',
            reel: newRod.reel || '',
            line: newRod.line || '',
        };
    } else {
        resetForm();
    }
}, { immediate: true });

// Watch for dialog open state to reset form when opening for new rod
watch(() => props.open, (isOpen) => {
    if (isOpen && !props.editingRod) {
        resetForm();
    }
});

const handleSubmit = async () => {
    errorMessage.value = '';
    try {
        const url = isEditMode.value
            ? `/rods/${props.editingRod!.id}`
            : '/rods';
        const method = isEditMode.value ? 'put' : 'post';

        const response = await axios[method](url, formData.value);
        emit('success', response.data);
        isOpen.value = false;
        resetForm();
    } catch (error: any) {
        console.error('Error saving rod:', error);
        if (error.response?.status === 409) {
            errorMessage.value = error.response.data.message || 'This rod already exists.';
        } else if (error.response?.status === 500) {
            errorMessage.value = error.response.data.message || 'Server error. Please try again.';
        } else if (error.response?.data?.message) {
            errorMessage.value = error.response.data.message;
        } else {
            errorMessage.value = 'An error occurred while saving the rod.';
        }
    }
};

const handleCancel = () => {
    isOpen.value = false;
    resetForm();
};
</script>

