<template>
    <Dialog v-model:open="isOpen">
        <DialogContent class="max-w-5xl max-h-[90vh] overflow-y-auto">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2">
                    <Fish class="h-6 w-6" />
                    {{ editingLog ? 'Edit Fishing Trip' : 'Log Your Fishing Trip' }}
                </DialogTitle>
                <DialogDescription>
                    {{ editingLog ? 'Update details about your fishing adventure' : 'Record details about your fishing adventure' }}
                </DialogDescription>
            </DialogHeader>

            <FishingLogForm
                :editing-log="editingLog"
                :locations="locations"
                :fish="fish"
                :flies="flies"
                :equipment="equipment"
                :friends="friends"
                :show-cancel-button="true"
                @success="handleSuccess"
                @cancel="handleCancel"
                @open-location-modal="$emit('open-location-modal')"
                @open-fish-modal="$emit('open-fish-modal')"
                @open-fly-modal="$emit('open-fly-modal')"
                @open-equipment-modal="$emit('open-equipment-modal')"
                @open-friend-modal="$emit('open-friend-modal')"
            />
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import FishingLogForm from '@/components/FishingLogForm.vue';
import { Fish } from 'lucide-vue-next';

const props = defineProps<{
    open: boolean;
    editingLog?: any;
    locations: any[];
    fish: any[];
    flies: any[];
    equipment: any[];
    friends: any[];
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    'success': [log: any];
    'open-location-modal': [];
    'open-fish-modal': [];
    'open-fly-modal': [];
    'open-equipment-modal': [];
    'open-friend-modal': [];
}>();

const isOpen = computed({
    get: () => props.open,
    set: (value) => emit('update:open', value),
});

const handleSuccess = (data: any) => {
    emit('success', data);
    isOpen.value = false;
};

const handleCancel = () => {
    isOpen.value = false;
};
</script>


