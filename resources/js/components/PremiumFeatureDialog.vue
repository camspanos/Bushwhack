<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Crown } from 'lucide-vue-next';

interface Props {
    open: boolean;
    title?: string;
    description?: string;
}

const props = withDefaults(defineProps<Props>(), {
    title: 'Premium Feature',
    description: 'This feature is only available to premium users. Upgrade to premium to unlock this and other advanced features.',
});

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const handleClose = () => {
    emit('update:open', false);
};
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="sm:max-w-md">
            <DialogHeader class="flex items-center justify-center">
                <div
                    class="mb-3 w-auto rounded-full border border-border bg-card p-0.5 shadow-sm"
                >
                    <div
                        class="relative overflow-hidden rounded-full border border-amber-500/20 bg-gradient-to-br from-amber-50 to-amber-100 dark:from-amber-950/30 dark:to-amber-900/20 p-2.5"
                    >
                        <Crown
                            class="relative z-20 size-6 text-amber-600 dark:text-amber-400"
                        />
                    </div>
                </div>
                <DialogTitle>{{ title }}</DialogTitle>
                <DialogDescription class="text-center">
                    {{ description }}
                </DialogDescription>
            </DialogHeader>
            <DialogFooter class="sm:justify-center">
                <Button variant="outline" @click="handleClose">
                    Maybe Later
                </Button>
                <Button class="bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700">
                    <Crown class="mr-2 h-4 w-4" />
                    Upgrade to Premium
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

