<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Fish, Calendar, Award, MapPin, Target } from 'lucide-vue-next';

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
    total_catches: number;
    total_trips: number;
    biggest_catch: {
        size: number;
        date: string;
        location: string;
    } | null;
    top_location: {
        name: string;
        city: string | null;
        state: string | null;
        total: number;
    } | null;
    top_fly: {
        name: string;
        total: number;
    } | null;
}

const props = defineProps<{
    user: UserInfo;
    fishSpecies: FishData[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { label: 'Following', href: '/following' },
    { label: props.user.name, href: `/users/${props.user.id}/dashboard` },
    { label: 'Fish' },
];

const formatSize = (size: number | null | undefined): string => {
    if (!size) return '0';
    return size.toFixed(1);
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

            <!-- User Info Header -->
            <Card class="bg-gradient-to-br from-teal-50/50 to-transparent dark:from-teal-950/20">
                <CardHeader>
                    <CardTitle class="text-2xl">{{ user.name }}'s Fish Species</CardTitle>
                    <CardDescription>
                        {{ user.email }} • Member since {{ user.member_since }}
                    </CardDescription>
                </CardHeader>
            </Card>

            <!-- Fish Species Grid -->
            <div v-if="fishSpecies.length > 0" class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <Card v-for="fish in fishSpecies" :key="fish.id" class="bg-gradient-to-br from-green-50/30 to-transparent dark:from-green-950/10">
                    <CardHeader class="pb-3">
                        <CardTitle class="text-lg">{{ fish.species }}</CardTitle>
                        <CardDescription v-if="fish.water_type">
                            {{ fish.water_type }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <!-- Stats -->
                        <div class="grid grid-cols-2 gap-3">
                            <div class="flex items-center gap-2 rounded-lg bg-green-100 dark:bg-green-900/30 p-2">
                                <Fish class="h-4 w-4 text-green-600 dark:text-green-400" />
                                <div>
                                    <div class="text-lg font-bold text-green-700 dark:text-green-300">{{ fish.total_catches }}</div>
                                    <div class="text-xs text-muted-foreground">Catches</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 p-2">
                                <Calendar class="h-4 w-4 text-emerald-600 dark:text-emerald-400" />
                                <div>
                                    <div class="text-lg font-bold text-emerald-700 dark:text-emerald-300">{{ fish.total_trips }}</div>
                                    <div class="text-xs text-muted-foreground">Trips</div>
                                </div>
                            </div>
                        </div>



                        <!-- Biggest Catch -->
                        <div v-if="fish.biggest_catch" class="rounded-lg border bg-card p-3">
                            <div class="flex items-start gap-2">
                                <Award class="h-4 w-4 text-amber-600 dark:text-amber-400 mt-0.5" />
                                <div class="flex-1">
                                    <div class="text-xs font-medium text-muted-foreground">Biggest Catch</div>
                                    <div class="font-semibold">{{ formatSize(fish.biggest_catch.size) }}"</div>
                                    <div class="text-xs text-muted-foreground">{{ fish.biggest_catch.date }}</div>
                                    <div v-if="fish.biggest_catch.location" class="text-xs text-muted-foreground">{{ fish.biggest_catch.location }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Top Location -->
                        <div v-if="fish.top_location" class="rounded-lg border bg-card p-3">
                            <div class="flex items-start gap-2">
                                <MapPin class="h-4 w-4 text-blue-600 dark:text-blue-400 mt-0.5" />
                                <div class="flex-1">
                                    <div class="text-xs font-medium text-muted-foreground">Top Location</div>
                                    <div class="font-semibold">{{ fish.top_location.name }}</div>
                                    <div class="text-xs text-muted-foreground">
                                        {{ fish.top_location.total }} caught
                                        <span v-if="fish.top_location.city || fish.top_location.state">
                                            • {{ fish.top_location.city }}{{ fish.top_location.city && fish.top_location.state ? ', ' : '' }}{{ fish.top_location.state }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Top Fly -->
                        <div v-if="fish.top_fly" class="rounded-lg border bg-card p-3">
                            <div class="flex items-start gap-2">
                                <Target class="h-4 w-4 text-purple-600 dark:text-purple-400 mt-0.5" />
                                <div class="flex-1">
                                    <div class="text-xs font-medium text-muted-foreground">Top Fly</div>
                                    <div class="font-semibold">{{ fish.top_fly.name }}</div>
                                    <div class="text-xs text-muted-foreground">{{ fish.top_fly.total }} caught</div>
                                </div>
                            </div>
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