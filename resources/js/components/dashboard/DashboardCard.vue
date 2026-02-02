<script setup lang="ts">
import { Card } from '@/components/ui/card';
import { ChevronUp, ChevronDown, EyeOff, Eye, Minus, Plus } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

// Size values: 3 = 1/4, 4 = 1/3, 6 = 1/2, 8 = 2/3, 9 = 3/4, 12 = Full (columns out of 12)
const SIZE_OPTIONS = [3, 4, 6, 8, 9, 12] as const;
type SizeOption = typeof SIZE_OPTIONS[number];

interface Props {
    cardId: string;
    isEditMode: boolean;
    isHidden?: boolean;
    class?: string;
    order?: number;
    size?: number; // 3, 4, 6, 8, 9, or 12 (columns out of 12)
    isFirst?: boolean;
    isLast?: boolean;
    displayPosition?: number; // 1-based position among visible cards
    totalVisible?: number; // Total number of visible cards
}

const props = withDefaults(defineProps<Props>(), {
    size: 3, // Default to 1/4 (3 columns)
    isHidden: false,
    order: 0,
    isFirst: false,
    isLast: false,
    displayPosition: 1,
    totalVisible: 1,
});

const emit = defineEmits<{
    hide: [cardId: string];
    show: [cardId: string];
    resize: [cardId: string, size: number];
    moveUp: [cardId: string];
    moveDown: [cardId: string];
    jumpToPosition: [cardId: string, newPosition: number];
}>();

// Local state for the position input
const localPosition = ref(props.displayPosition);

// Keep local position in sync with prop
watch(() => props.displayPosition, (newVal) => {
    localPosition.value = newVal;
});

const handleToggleVisibility = () => {
    if (props.isHidden) {
        emit('show', props.cardId);
    } else {
        emit('hide', props.cardId);
    }
};

const handleResize = (delta: number) => {
    const currentIndex = SIZE_OPTIONS.indexOf(props.size as SizeOption);
    const newIndex = Math.max(0, Math.min(SIZE_OPTIONS.length - 1, currentIndex + delta));
    const newSize = SIZE_OPTIONS[newIndex];
    if (newSize !== props.size) {
        emit('resize', props.cardId, newSize);
    }
};

const handleMoveUp = () => {
    emit('moveUp', props.cardId);
};

const handleMoveDown = () => {
    emit('moveDown', props.cardId);
};

const handlePositionChange = (event: Event) => {
    const input = event.target as HTMLInputElement;
    let newPosition = parseInt(input.value, 10);

    // Clamp to valid range
    if (isNaN(newPosition) || newPosition < 1) {
        newPosition = 1;
    } else if (newPosition > props.totalVisible) {
        newPosition = props.totalVisible;
    }

    // Update local value to clamped value
    localPosition.value = newPosition;

    // Only emit if position actually changed
    if (newPosition !== props.displayPosition) {
        emit('jumpToPosition', props.cardId, newPosition);
    }
};

const handlePositionBlur = () => {
    // Reset to current position if invalid
    localPosition.value = props.displayPosition;
};

// Compute column span class based on size (using 12-column grid)
// Grid is: grid-cols-1 md:grid-cols-6 lg:grid-cols-12
const colSpanClass = computed(() => {
    switch (props.size) {
        case 3: return 'md:col-span-3 lg:col-span-3'; // 1/4
        case 4: return 'md:col-span-3 lg:col-span-4'; // 1/3
        case 6: return 'md:col-span-6 lg:col-span-6'; // 1/2
        case 8: return 'md:col-span-6 lg:col-span-8'; // 2/3
        case 9: return 'md:col-span-6 lg:col-span-9'; // 3/4
        case 12: return 'col-span-full'; // Full
        default: return 'md:col-span-3 lg:col-span-3'; // Default to 1/4
    }
});

// Size labels for display
const sizeLabel = computed(() => {
    switch (props.size) {
        case 3: return '1/4';
        case 4: return '1/3';
        case 6: return '1/2';
        case 8: return '2/3';
        case 9: return '3/4';
        case 12: return 'Full';
        default: return '1/4';
    }
});

// Check if at min/max size
const isMinSize = computed(() => props.size === SIZE_OPTIONS[0]);
const isMaxSize = computed(() => props.size === SIZE_OPTIONS[SIZE_OPTIONS.length - 1]);
</script>

<template>
    <Card
        :data-card-id="cardId"
        :class="[
            props.class,
            colSpanClass,
            isEditMode ? 'relative ring-2 ring-primary/20' : '',
            isHidden && isEditMode ? 'opacity-50 grayscale' : ''
        ]"
        :style="{ order: props.order }"
    >
        <!-- Edit mode overlay controls -->
        <div
            v-if="isEditMode"
            class="absolute top-0 left-0 right-0 z-10 flex items-center justify-between bg-primary/10 px-2 py-1 rounded-t-lg"
        >
            <!-- Left: Position controls with editable number -->
            <div class="flex items-center gap-0.5">
                <button
                    @click="handleMoveUp"
                    :disabled="isFirst || isHidden"
                    class="p-0.5 rounded hover:bg-primary/20 disabled:opacity-30 disabled:cursor-not-allowed transition-colors"
                    title="Move up"
                >
                    <ChevronUp class="h-4 w-4" />
                </button>
                <input
                    v-if="!isHidden"
                    type="number"
                    :value="localPosition"
                    @change="handlePositionChange"
                    @blur="handlePositionBlur"
                    :min="1"
                    :max="totalVisible"
                    class="w-8 h-5 text-xs text-center bg-background/50 border border-border rounded px-0.5 focus:outline-none focus:ring-1 focus:ring-primary [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                    :title="`Position ${displayPosition} of ${totalVisible}`"
                />
                <span v-else class="w-8 h-5 text-xs text-center text-muted-foreground">—</span>
                <button
                    @click="handleMoveDown"
                    :disabled="isLast || isHidden"
                    class="p-0.5 rounded hover:bg-primary/20 disabled:opacity-30 disabled:cursor-not-allowed transition-colors"
                    title="Move down"
                >
                    <ChevronDown class="h-4 w-4" />
                </button>
            </div>

            <!-- Center: Size controls -->
            <div class="flex items-center gap-1">
                <button
                    @click="handleResize(-1)"
                    :disabled="isMinSize"
                    class="p-0.5 rounded hover:bg-primary/20 disabled:opacity-30 disabled:cursor-not-allowed transition-colors"
                    title="Decrease size"
                >
                    <Minus class="h-3 w-3" />
                </button>
                <span class="text-xs font-medium min-w-[2rem] text-center">{{ sizeLabel }}</span>
                <button
                    @click="handleResize(1)"
                    :disabled="isMaxSize"
                    class="p-0.5 rounded hover:bg-primary/20 disabled:opacity-30 disabled:cursor-not-allowed transition-colors"
                    title="Increase size"
                >
                    <Plus class="h-3 w-3" />
                </button>
            </div>

            <!-- Right: Visibility toggle -->
            <button
                @click="handleToggleVisibility"
                :class="[
                    'flex items-center gap-1 text-xs transition-colors p-0.5 rounded',
                    isHidden
                        ? 'text-green-600 hover:text-green-700 dark:text-green-400 dark:hover:text-green-300'
                        : 'text-muted-foreground hover:text-destructive'
                ]"
                :title="isHidden ? 'Show card' : 'Hide card'"
            >
                <Eye v-if="isHidden" class="h-4 w-4" />
                <EyeOff v-else class="h-4 w-4" />
            </button>
        </div>

        <!-- Card content with padding when in edit mode -->
        <div :class="isEditMode ? 'pt-8' : ''">
            <slot />
        </div>
    </Card>
</template>

