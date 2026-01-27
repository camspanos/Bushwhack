<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import PremiumFeatureDialog from '@/components/PremiumFeatureDialog.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { NativeSelect, NativeSelectOption } from '@/components/ui/native-select';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Fish, Calendar, Award, MapPin, Target, TrendingUp } from 'lucide-vue-next';
import { ref, computed, watch, nextTick } from 'vue';

interface UserInfo {
    id: number;
    name: string;
    email: string;
    member_since: string;
}

interface FishData {
    id: number;
    species: string;
    water_type: string | null;
    totalCaught: number;
    totalTrips: number;
    biggestFish: number;
    avgSize: number;
}

const props = defineProps<{
    user: UserInfo;
    fishSpecies: FishData[];
    availableYears: string[];
    selectedYear: string;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { label: 'Following', href: '/following' },
    { label: props.user.name, href: `/users/${props.user.id}/dashboard` },
    { label: 'Fish' },
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

    router.visit(`/users/${props.user.id}/fish`, {
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
    <Head :title="`${user.name}'s Fish Species`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Navigation Tabs -->
            <Tabs :model-value="`/users/${user.id}/fish`" class="w-full">
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
                        <Fish class="h-6 w-6" />
                        {{ user.name }}'s Fish Species
                    </h3>
                    <p class="text-muted-foreground">
                        {{ user.email }} • Member since {{ user.member_since }} •
                        Statistics {{ yearLabel === 'Lifetime' ? 'across all time' : 'for ' + yearLabel }}
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <label for="fish-year-filter" class="text-sm font-medium">Filter by:</label>
                    <NativeSelect v-model="selectedYearFilter" id="fish-year-filter" class="w-[140px]">
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

            <!-- Fish Species Grid -->
            <div v-if="fishSpecies.length > 0" class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <Card v-for="fish in fishSpecies" :key="fish.id" class="bg-gradient-to-br from-teal-50/30 to-transparent dark:from-teal-950/10">
                    <CardHeader>
                        <CardTitle class="text-lg flex items-center gap-2">
                            <div class="rounded-full bg-teal-100 p-1.5 dark:bg-teal-900/30">
                                <Fish class="h-4 w-4 text-teal-600 dark:text-teal-400" />
                            </div>
                            {{ fish.species }}
                        </CardTitle>
                        <CardDescription>
                            {{ fish.water_type || 'No water type specified' }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-muted-foreground flex items-center gap-2">
                                <Fish class="h-4 w-4 text-emerald-500 dark:text-emerald-400" />
                                Total Caught
                            </span>
                            <span class="font-bold text-emerald-700 dark:text-emerald-300">{{ fish.totalCaught }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-muted-foreground flex items-center gap-2">
                                <Calendar class="h-4 w-4 text-amber-500 dark:text-amber-400" />
                                Total Trips
                            </span>
                            <span class="font-bold text-amber-700 dark:text-amber-300">{{ fish.totalTrips }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-muted-foreground flex items-center gap-2">
                                <Award class="h-4 w-4 text-blue-500 dark:text-blue-400" />
                                Biggest Catch
                            </span>
                            <span class="font-bold text-blue-700 dark:text-blue-300">{{ formatSize(fish.biggestFish) }}"</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-muted-foreground flex items-center gap-2">
                                <TrendingUp class="h-4 w-4 text-red-500 dark:text-red-400" />
                                Avg Size
                            </span>
                            <span class="font-bold text-red-700 dark:text-red-300">{{ formatSize(fish.avgSize) }}"</span>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Empty State -->
            <Card v-else class="bg-gradient-to-br from-slate-50/30 to-transparent dark:from-slate-950/10">
                <CardContent class="flex flex-col items-center justify-center py-12">
                    <Fish class="h-12 w-12 text-muted-foreground mb-4" />
                    <p class="text-lg font-medium text-muted-foreground">No fish species caught yet</p>
                    <p class="text-sm text-muted-foreground">Fish species will appear here once catches are logged</p>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>