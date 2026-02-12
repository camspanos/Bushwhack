<script setup lang="ts">
import { CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { computed, type Component } from 'vue';

interface Props {
    title: string;
    subtitle?: string;
    icon?: Component;
    emoji?: string;
    color?: string;
    gradientTo?: string;
}

const props = withDefaults(defineProps<Props>(), {
    color: 'blue',
    gradientTo: '',
});

// Color mappings for Tailwind (must be static strings for purging)
const colorMap: Record<string, { bg: string; icon: string; gradientFrom: string; gradientTo: string }> = {
    blue: { bg: 'bg-blue-100 dark:bg-blue-900/30', icon: 'text-blue-600 dark:text-blue-400', gradientFrom: 'from-blue-100 dark:from-blue-900/30', gradientTo: 'to-blue-100 dark:to-blue-900/30' },
    sky: { bg: 'bg-sky-100 dark:bg-sky-900/30', icon: 'text-sky-600 dark:text-sky-400', gradientFrom: 'from-sky-100 dark:from-sky-900/30', gradientTo: 'to-sky-100 dark:to-sky-900/30' },
    cyan: { bg: 'bg-cyan-100 dark:bg-cyan-900/30', icon: 'text-cyan-600 dark:text-cyan-400', gradientFrom: 'from-cyan-100 dark:from-cyan-900/30', gradientTo: 'to-cyan-100 dark:to-cyan-900/30' },
    teal: { bg: 'bg-teal-100 dark:bg-teal-900/30', icon: 'text-teal-600 dark:text-teal-400', gradientFrom: 'from-teal-100 dark:from-teal-900/30', gradientTo: 'to-teal-100 dark:to-teal-900/30' },
    emerald: { bg: 'bg-emerald-100 dark:bg-emerald-900/30', icon: 'text-emerald-600 dark:text-emerald-400', gradientFrom: 'from-emerald-100 dark:from-emerald-900/30', gradientTo: 'to-emerald-100 dark:to-emerald-900/30' },
    green: { bg: 'bg-green-100 dark:bg-green-900/30', icon: 'text-green-600 dark:text-green-400', gradientFrom: 'from-green-100 dark:from-green-900/30', gradientTo: 'to-green-100 dark:to-green-900/30' },
    lime: { bg: 'bg-lime-100 dark:bg-lime-900/30', icon: 'text-lime-600 dark:text-lime-400', gradientFrom: 'from-lime-100 dark:from-lime-900/30', gradientTo: 'to-lime-100 dark:to-lime-900/30' },
    yellow: { bg: 'bg-yellow-100 dark:bg-yellow-900/30', icon: 'text-yellow-600 dark:text-yellow-400', gradientFrom: 'from-yellow-100 dark:from-yellow-900/30', gradientTo: 'to-yellow-100 dark:to-yellow-900/30' },
    amber: { bg: 'bg-amber-100 dark:bg-amber-900/30', icon: 'text-amber-600 dark:text-amber-400', gradientFrom: 'from-amber-100 dark:from-amber-900/30', gradientTo: 'to-amber-100 dark:to-amber-900/30' },
    orange: { bg: 'bg-orange-100 dark:bg-orange-900/30', icon: 'text-orange-600 dark:text-orange-400', gradientFrom: 'from-orange-100 dark:from-orange-900/30', gradientTo: 'to-orange-100 dark:to-orange-900/30' },
    red: { bg: 'bg-red-100 dark:bg-red-900/30', icon: 'text-red-600 dark:text-red-400', gradientFrom: 'from-red-100 dark:from-red-900/30', gradientTo: 'to-red-100 dark:to-red-900/30' },
    rose: { bg: 'bg-rose-100 dark:bg-rose-900/30', icon: 'text-rose-600 dark:text-rose-400', gradientFrom: 'from-rose-100 dark:from-rose-900/30', gradientTo: 'to-rose-100 dark:to-rose-900/30' },
    pink: { bg: 'bg-pink-100 dark:bg-pink-900/30', icon: 'text-pink-600 dark:text-pink-400', gradientFrom: 'from-pink-100 dark:from-pink-900/30', gradientTo: 'to-pink-100 dark:to-pink-900/30' },
    fuchsia: { bg: 'bg-fuchsia-100 dark:bg-fuchsia-900/30', icon: 'text-fuchsia-600 dark:text-fuchsia-400', gradientFrom: 'from-fuchsia-100 dark:from-fuchsia-900/30', gradientTo: 'to-fuchsia-100 dark:to-fuchsia-900/30' },
    purple: { bg: 'bg-purple-100 dark:bg-purple-900/30', icon: 'text-purple-600 dark:text-purple-400', gradientFrom: 'from-purple-100 dark:from-purple-900/30', gradientTo: 'to-purple-100 dark:to-purple-900/30' },
    violet: { bg: 'bg-violet-100 dark:bg-violet-900/30', icon: 'text-violet-600 dark:text-violet-400', gradientFrom: 'from-violet-100 dark:from-violet-900/30', gradientTo: 'to-violet-100 dark:to-violet-900/30' },
    indigo: { bg: 'bg-indigo-100 dark:bg-indigo-900/30', icon: 'text-indigo-600 dark:text-indigo-400', gradientFrom: 'from-indigo-100 dark:from-indigo-900/30', gradientTo: 'to-indigo-100 dark:to-indigo-900/30' },
    slate: { bg: 'bg-slate-100 dark:bg-slate-900/30', icon: 'text-slate-600 dark:text-slate-400', gradientFrom: 'from-slate-100 dark:from-slate-900/30', gradientTo: 'to-slate-100 dark:to-slate-900/30' },
    gray: { bg: 'bg-gray-100 dark:bg-gray-900/30', icon: 'text-gray-600 dark:text-gray-400', gradientFrom: 'from-gray-100 dark:from-gray-900/30', gradientTo: 'to-gray-100 dark:to-gray-900/30' },
    stone: { bg: 'bg-stone-100 dark:bg-stone-900/30', icon: 'text-stone-600 dark:text-stone-400', gradientFrom: 'from-stone-100 dark:from-stone-900/30', gradientTo: 'to-stone-100 dark:to-stone-900/30' },
};

const iconContainerClass = computed(() => {
    const base = 'rounded-full p-2';
    const primary = colorMap[props.color] || colorMap.blue;

    if (props.gradientTo && colorMap[props.gradientTo]) {
        const secondary = colorMap[props.gradientTo];
        return `${base} bg-gradient-to-br ${primary.gradientFrom} ${secondary.gradientTo}`;
    }

    return `${base} ${primary.bg}`;
});

const iconClass = computed(() => {
    const primary = colorMap[props.color] || colorMap.blue;
    return `h-4 w-4 ${primary.icon}`;
});
</script>

<template>
    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-3">
        <div class="space-y-0.5">
            <CardTitle class="text-base font-medium">{{ title }}</CardTitle>
            <CardDescription v-if="subtitle || $slots.subtitle" class="text-xs">
                <slot name="subtitle">{{ subtitle }}</slot>
            </CardDescription>
        </div>
        <div :class="iconContainerClass">
            <span v-if="emoji" class="text-base leading-none flex items-center justify-center gap-0.5">{{ emoji }}</span>
            <component v-else-if="icon" :is="icon" :class="iconClass" />
        </div>
    </CardHeader>
</template>

