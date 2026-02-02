<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import PremiumFeatureDialog from '@/components/PremiumFeatureDialog.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { NativeSelect, NativeSelectOption } from '@/components/ui/native-select';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Fish, Calendar, Award, Palette, Bug, Crown } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { ref, computed, watch, nextTick } from 'vue';

interface UserInfo {
    id: number;
    name: string;
    email: string;
    member_since: string;
}

interface FlyData {
    id: number;
    name: string;
    type: string | null;
    favoriteColor: string | null;
    totalCaught: number;
    totalTrips: number;
    biggestFish: number;
    successRate: number;
}

const props = defineProps<{
    user: UserInfo;
    flies: FlyData[];
    availableYears: string[];
    selectedYear: string;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { label: 'Following', href: '/following' },
    { label: props.user.name, href: `/users/${props.user.id}/dashboard` },
    { label: 'Flies' },
];

const selectedYearFilter = ref(props.selectedYear);
const showPremiumDialog = ref(false);
const page = usePage();
const currentYear = new Date().getFullYear().toString();

const yearLabel = computed(() => {
    return selectedYearFilter.value === 'lifetime' ? 'Lifetime' : selectedYearFilter.value;
});

watch(selectedYearFilter, async (newYear, oldYear) => {
    // Check if user is trying to select a non-current year and is not premium
    // Exception: user_id 1 can be viewed by anyone
    if (!page.props.auth.isPremium && newYear !== currentYear && props.user.id !== 1) {
        // Show premium dialog
        showPremiumDialog.value = true;
        // Revert the selection after showing dialog
        await nextTick();
        selectedYearFilter.value = oldYear;
        return;
    }

    router.visit(`/users/${props.user.id}/flies`, {
        data: { year: newYear },
        preserveState: true,
        preserveScroll: true,
    });
});

// Format size for display (remove .0 decimals)
const formatSize = (size: number) => {
    const num = parseFloat(size.toString());
    return num % 1 === 0 ? Math.floor(num).toString() : num.toString();
};
</script>

<template>
    <Head :title="`${user.name}'s Flies`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Navigation Tabs -->
            <Tabs :model-value="`/users/${user.id}/flies`" class="w-full">
                <TabsList class="grid w-full grid-cols-4">
                    <TabsTrigger
                        :value="`/users/${user.id}/dashboard`"
                        as-child
                    >
                        <Link :href="`/users/${user.id}/dashboard`">
                            Dashboard
                        </Link>
                    </TabsTrigger>
                    <TabsTrigger
                        :value="`/users/${user.id}/rods`"
                        as-child
                    >
                        <Link :href="`/users/${user.id}/rods`">
                            Rods
                        </Link>
                    </TabsTrigger>
                    <TabsTrigger
                        :value="`/users/${user.id}/fish`"
                        as-child
                    >
                        <Link :href="`/users/${user.id}/fish`">
                            Fish
                        </Link>
                    </TabsTrigger>
                    <TabsTrigger
                        :value="`/users/${user.id}/flies`"
                        as-child
                    >
                        <Link :href="`/users/${user.id}/flies`">
                            Flies
                        </Link>
                    </TabsTrigger>
                </TabsList>
            </Tabs>

            <!-- Header with Year Filter -->
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-2xl font-bold tracking-tight flex items-center gap-2">
                        <Bug class="h-6 w-6" />
                        {{ user.name }}'s Flies
                    </h3>
                    <p class="text-muted-foreground">
                        {{ user.email }} • Member since {{ user.member_since }} •
                        Statistics {{ yearLabel === 'Lifetime' ? 'across all time' : 'for ' + yearLabel }}
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <label for="fly-year-filter" class="text-sm font-medium">Filter by:</label>
                    <NativeSelect v-model="selectedYearFilter" id="fly-year-filter" class="w-[140px]">
                        <NativeSelectOption value="lifetime">Lifetime</NativeSelectOption>
                        <NativeSelectOption v-for="year in availableYears" :key="year" :value="year">
                            {{ year }}
                        </NativeSelectOption>
                    </NativeSelect>
                </div>
            </div>

            <!-- Premium Feature Dialog -->
            <PremiumFeatureDialog
                v-model:open="showPremiumDialog"
                title="Year Filtering is a Premium Feature"
                description="Access to historical data and year filtering is only available to premium users. Upgrade to premium to view your fishing statistics from previous years and lifetime totals."
            />

            <!-- AdSense & Premium Upgrade Row (shown for non-premium users) -->
            <div v-if="!page.props.auth.isPremium" class="grid gap-4 md:grid-cols-2 mb-4">
                <!-- Google AdSense Card -->
                <Card class="bg-gradient-to-br from-slate-50/50 to-transparent dark:from-slate-950/20">
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">Advertisement</CardTitle>
                    </CardHeader>
                    <CardContent class="pt-0 pb-4">
                        <div class="flex items-center justify-center min-h-[200px] bg-muted/30 rounded-lg border-2 border-dashed border-muted-foreground/20">
                            <p class="text-sm text-muted-foreground">Google AdSense Placeholder</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Premium Upgrade Card -->
                <Card class="bg-gradient-to-br from-amber-50/50 to-amber-100/30 dark:from-amber-950/20 dark:to-amber-900/10 border-amber-200/50 dark:border-amber-800/30">
                    <CardHeader class="pb-2">
                        <CardTitle class="flex items-center gap-2 text-lg">
                            <div class="rounded-full bg-amber-100 p-2 dark:bg-amber-900/30">
                                <Crown class="h-5 w-5 text-amber-600 dark:text-amber-400" />
                            </div>
                            <span class="text-amber-900 dark:text-amber-100">Upgrade to Premium</span>
                        </CardTitle>
                        <CardDescription class="text-amber-700/80 dark:text-amber-300/80">
                            Unlock the full Bushwhack experience
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="pt-0 pb-4 space-y-4">
                        <div class="space-y-2">
                            <p class="text-sm text-amber-900/90 dark:text-amber-100/90 font-medium">
                                Remove all advertisements and enjoy:
                            </p>
                            <ul class="text-sm text-amber-800/80 dark:text-amber-200/80 space-y-1 ml-4">
                                <li class="flex items-start gap-2">
                                    <span class="text-amber-600 dark:text-amber-400 mt-0.5">✓</span>
                                    <span>Ad-free experience across all pages</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-amber-600 dark:text-amber-400 mt-0.5">✓</span>
                                    <span>Historical data & year filtering</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-amber-600 dark:text-amber-400 mt-0.5">✓</span>
                                    <span>Dashboard customization</span>
                                </li>
                            </ul>
                        </div>
                        <Button
                            class="w-full bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white shadow-md"
                            @click="router.visit('/settings/subscription')"
                        >
                            <Crown class="mr-2 h-4 w-4" />
                            Upgrade Now
                        </Button>
                    </CardContent>
                </Card>
            </div>

            <!-- Flies Grid -->
            <div v-if="flies.length > 0" class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card v-for="fly in flies" :key="fly.id" class="bg-gradient-to-br from-purple-50/30 to-transparent dark:from-purple-950/10">
                    <CardHeader>
                        <CardTitle class="text-lg flex items-center gap-2">
                            <div class="rounded-full bg-purple-100 p-1.5 dark:bg-purple-900/30">
                                <Bug class="h-4 w-4 text-purple-600 dark:text-purple-400" />
                            </div>
                            {{ fly.name }}
                        </CardTitle>
                        <CardDescription>
                            {{ fly.type || 'No type specified' }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-muted-foreground flex items-center gap-2">
                                <Fish class="h-4 w-4 text-emerald-500 dark:text-emerald-400" />
                                Total Caught
                            </span>
                            <span class="font-bold text-emerald-700 dark:text-emerald-300">{{ fly.totalCaught }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-muted-foreground flex items-center gap-2">
                                <Calendar class="h-4 w-4 text-blue-500 dark:text-blue-400" />
                                Total Trips
                            </span>
                            <span class="font-bold text-blue-700 dark:text-blue-300">{{ fly.totalTrips }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-muted-foreground flex items-center gap-2">
                                <Award class="h-4 w-4 text-amber-500 dark:text-amber-400" />
                                Biggest Fish
                            </span>
                            <span class="font-bold text-amber-700 dark:text-amber-300">{{ formatSize(fly.biggestFish) }}"</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-muted-foreground flex items-center gap-2">
                                <Palette class="h-4 w-4 text-purple-500 dark:text-purple-400" />
                                Favorite Color
                            </span>
                            <span class="font-bold text-purple-700 dark:text-purple-300">{{ fly.favoriteColor }}</span>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Empty State -->
            <Card v-else class="bg-gradient-to-br from-slate-50/30 to-transparent dark:from-slate-950/10">
                <CardContent class="flex flex-col items-center justify-center py-12">
                    <Fish class="h-12 w-12 text-muted-foreground mb-4" />
                    <p class="text-lg font-medium text-muted-foreground">No flies with catches yet</p>
                    <p class="text-sm text-muted-foreground">Flies will appear here once they've been used to catch fish</p>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>