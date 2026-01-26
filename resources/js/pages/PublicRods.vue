<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Fish, Calendar, Award, Target } from 'lucide-vue-next';

interface UserInfo {
    id: number;
    name: string;
    email: string;
    member_since: string;
}

interface RodData {
    id: number;
    rod_name: string;
    rod_weight: string | null;
    rod_length: string | null;
    reel: string | null;
    line: string | null;
    total_trips: number;
    total_catches: number;
    biggest_fish: {
        size: number;
        species: string;
        date: string;
    } | null;
    top_species: {
        species: string;
        total: number;
    } | null;
}

const props = defineProps<{
    user: UserInfo;
    rods: RodData[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { label: 'Following', href: '/following' },
    { label: props.user.name, href: `/users/${props.user.id}/dashboard` },
    { label: 'Rods' },
];

const formatSize = (size: number | null | undefined): string => {
    if (!size) return '0';
    return size.toFixed(1);
};
</script>

<template>
    <Head :title="`${user.name}'s Rods`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Navigation Tabs -->
            <Tabs :model-value="`/users/${user.id}/rods`" class="w-full">
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
                    <CardTitle class="text-2xl">{{ user.name }}'s Rods</CardTitle>
                    <CardDescription>
                        {{ user.email }} • Member since {{ user.member_since }}
                    </CardDescription>
                </CardHeader>
            </Card>

            <!-- Rods Grid -->
            <div v-if="rods.length > 0" class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <Card v-for="rod in rods" :key="rod.id" class="bg-gradient-to-br from-blue-50/30 to-transparent dark:from-blue-950/10">
                    <CardHeader class="pb-3">
                        <CardTitle class="text-lg">{{ rod.rod_name }}</CardTitle>
                        <CardDescription>
                            <div class="space-y-1 text-sm">
                                <div v-if="rod.rod_weight">Weight: {{ rod.rod_weight }}</div>
                                <div v-if="rod.rod_length">Length: {{ rod.rod_length }}</div>
                                <div v-if="rod.reel">Reel: {{ rod.reel }}</div>
                                <div v-if="rod.line">Line: {{ rod.line }}</div>
                            </div>
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <!-- Stats -->
                        <div class="grid grid-cols-2 gap-3">
                            <div class="flex items-center gap-2 rounded-lg bg-blue-100 dark:bg-blue-900/30 p-2">
                                <Fish class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                <div>
                                    <div class="text-lg font-bold text-blue-700 dark:text-blue-300">{{ rod.total_catches }}</div>
                                    <div class="text-xs text-muted-foreground">Catches</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 rounded-lg bg-cyan-100 dark:bg-cyan-900/30 p-2">
                                <Calendar class="h-4 w-4 text-cyan-600 dark:text-cyan-400" />
                                <div>
                                    <div class="text-lg font-bold text-cyan-700 dark:text-cyan-300">{{ rod.total_trips }}</div>
                                    <div class="text-xs text-muted-foreground">Trips</div>
                                </div>
                            </div>
                        </div>

                        <!-- Biggest Fish -->
                        <div v-if="rod.biggest_fish" class="rounded-lg border bg-card p-3">
                            <div class="flex items-start gap-2">
                                <Award class="h-4 w-4 text-amber-600 dark:text-amber-400 mt-0.5" />
                                <div class="flex-1">
                                    <div class="text-xs font-medium text-muted-foreground">Biggest Fish</div>
                                    <div class="font-semibold">{{ formatSize(rod.biggest_fish.size) }}" {{ rod.biggest_fish.species }}</div>
                                    <div class="text-xs text-muted-foreground">{{ rod.biggest_fish.date }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Top Species -->
                        <div v-if="rod.top_species" class="rounded-lg border bg-card p-3">
                            <div class="flex items-start gap-2">
                                <Target class="h-4 w-4 text-green-600 dark:text-green-400 mt-0.5" />
                                <div class="flex-1">
                                    <div class="text-xs font-medium text-muted-foreground">Top Species</div>
                                    <div class="font-semibold">{{ rod.top_species.species }}</div>
                                    <div class="text-xs text-muted-foreground">{{ rod.top_species.total }} caught</div>
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
                    <p class="text-lg font-medium text-muted-foreground">No rods with catches yet</p>
                    <p class="text-sm text-muted-foreground">Rods will appear here once they've been used to catch fish</p>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

