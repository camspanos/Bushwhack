<template>
    <Dialog v-model:open="isOpen">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>{{ isEditMode ? 'Edit Fish Species' : 'Add New Fish Species' }}</DialogTitle>
                <DialogDescription>
                    {{ isEditMode ? 'Update the fish species details.' : 'Create a new fish species to add to your log.' }}
                </DialogDescription>
            </DialogHeader>

            <Alert v-if="errorMessage" variant="destructive" class="mb-4">
                <AlertCircle class="h-4 w-4" />
                <AlertDescription>{{ errorMessage }}</AlertDescription>
            </Alert>

            <form @submit.prevent="handleSubmit" class="space-y-4">
                <div class="grid gap-2">
                    <Label for="fish-species">Species *</Label>
                    <Input
                        id="fish-species"
                        v-model="formData.species"
                        placeholder="e.g., Rainbow Trout"
                        required
                    />
                </div>

                <div class="grid gap-2">
                    <Label for="fish-water-type">Water Type</Label>
                    <Input
                        id="fish-water-type"
                        v-model="formData.water_type"
                        placeholder="e.g., Freshwater, Saltwater"
                    />
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="handleCancel">
                        Cancel
                    </Button>
                    <Button type="submit">
                        {{ isEditMode ? 'Update Fish Species' : 'Add Fish Species' }}
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

interface FishFormData {
    species: string;
    water_type: string;
}

const props = defineProps<{
    open: boolean;
    editingFish?: FishFormData & { id?: number } | null;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    'success': [fish: any];
}>();

const isOpen = computed({
    get: () => props.open,
    set: (value) => emit('update:open', value),
});

const isEditMode = computed(() => !!props.editingFish?.id);
const errorMessage = ref('');

const formData = ref<FishFormData>({
    species: '',
    water_type: '',
});

// Reset form function
const resetForm = () => {
    formData.value = {
        species: '',
        water_type: '',
    };
    errorMessage.value = '';
};

// Watch for editing fish changes
watch(() => props.editingFish, (newFish) => {
    if (newFish) {
        formData.value = {
            species: newFish.species || '',
            water_type: newFish.water_type || '',
        };
    } else {
        resetForm();
    }
}, { immediate: true });

// Watch for dialog open state to reset form when opening for new fish
watch(() => props.open, (isOpen) => {
    if (isOpen && !props.editingFish) {
        resetForm();
    }
});

const handleSubmit = async () => {
    errorMessage.value = '';
    try {
        const url = isEditMode.value
            ? `/fish/${props.editingFish!.id}`
            : '/fish';
        const method = isEditMode.value ? 'put' : 'post';

        const response = await axios[method](url, formData.value);
        emit('success', response.data);
        isOpen.value = false;
        resetForm();
    } catch (error: any) {
        console.error('Error saving fish:', error);
        if (error.response?.status === 409) {
            errorMessage.value = error.response.data.message || 'This fish species already exists.';
        } else if (error.response?.status === 500) {
            errorMessage.value = error.response.data.message || 'Server error. Please try again.';
        } else if (error.response?.data?.message) {
            errorMessage.value = error.response.data.message;
        } else {
            errorMessage.value = 'An error occurred while saving the fish species.';
        }
    }
};

const handleCancel = () => {
    isOpen.value = false;
    resetForm();
};
</script>

