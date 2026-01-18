<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { dashboard, fishingLog } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Fish, MapPin, Users, TrendingUp, Award, Calendar } from 'lucide-vue-next';

interface Stats {
    totalCatches: number;
    totalTrips: number;
    totalLocations: number;
    totalFriends: number;
    favoriteLocation: string | null;
    topFish: string | null;
    topFishCount: number;
    biggestCatch: {
        size: number;
        species: string;
        location: string;
        date: string;
    } | null;
}

interface FishingLogItem {
    id: number;
    date: string;
    quantity: number;
    max_size: number | null;
    location: { name: string } | null;
    fish: { species: string } | null;
    fly: { name: string } | null;
    equipment: { rod_name: string } | null;
    friends: { name: string }[];
}

interface ChartData {
    month: string;
    total: number;
}

interface LocationData {
    name: string;
    total: number;
}

const props = defineProps<{
    stats: Stats;
    recentLogs: FishingLogItem[];
    catchesByMonth: ChartData[];
    topLocations: LocationData[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Catches</CardTitle>
                        <Fish class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.totalCatches }}</div>
                        <p class="text-xs text-muted-foreground">Across {{ stats.totalTrips }} trips</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Favorite Location</CardTitle>
                        <MapPin class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.favoriteLocation || 'N/A' }}</div>
                        <p class="text-xs text-muted-foreground">Most visited spot</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Top Species</CardTitle>
                        <TrendingUp class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.topFish || 'N/A' }}</div>
                        <p class="text-xs text-muted-foreground">{{ stats.topFishCount }} caught</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Fishing Buddies</CardTitle>
                        <Users class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.totalFriends }}</div>
                        <p class="text-xs text-muted-foreground">Friends in your network</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Biggest Catch & Charts Row -->
            <div class="grid gap-4 md:grid-cols-2">
                <!-- Biggest Catch -->
                <Card v-if="stats.biggestCatch">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Award class="h-5 w-5" />
                            Biggest Catch
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-2">
                            <div class="text-3xl font-bold">{{ stats.biggestCatch.size }}"</div>
                            <div class="text-lg font-medium">{{ stats.biggestCatch.species }}</div>
                            <div class="text-sm text-muted-foreground">
                                <div>{{ stats.biggestCatch.location }}</div>
                                <div>{{ formatDate(stats.biggestCatch.date) }}</div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card v-else>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Award class="h-5 w-5" />
                            Biggest Catch
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-muted-foreground">No catches recorded yet</p>
                    </CardContent>
                </Card>


                <!-- Catches by Month Chart -->
                <Card>
                    <CardHeader>
                        <CardTitle>Catches Last 6 Months</CardTitle>
                        <CardDescription>Your fishing activity over time</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="catchesByMonth.length > 0" class="space-y-2">
                            <div v-for="item in catchesByMonth" :key="item.month" class="flex items-center gap-2">
                                <div class="w-20 text-sm text-muted-foreground">{{ item.month }}</div>
                                <div class="flex-1">
                                    <div class="h-8 bg-primary/20 rounded-md relative overflow-hidden">
                                        <div
                                            class="h-full bg-primary rounded-md transition-all"
                                            :style="{ width: `${(item.total / Math.max(...catchesByMonth.map(i => i.total))) * 100}%` }"
                                        ></div>
                                    </div>
                                </div>
                                <div class="w-12 text-right text-sm font-medium">{{ item.total }}</div>
                            </div>
                        </div>
                        <p v-else class="text-muted-foreground">No data available</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Recent Activity & Top Locations -->
            <div class="grid gap-4 md:grid-cols-2">
                <!-- Recent Fishing Logs -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Calendar class="h-5 w-5" />
                            Recent Activity
                        </CardTitle>
                        <CardDescription>Your latest fishing trips</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="recentLogs.length > 0" class="space-y-4">
                            <div v-for="log in recentLogs" :key="log.id" class="flex items-start gap-3 pb-3 border-b last:border-0">
                                <div class="flex-1 space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span class="font-medium">{{ log.fish?.species || 'Unknown' }}</span>
                                        <span class="text-sm text-muted-foreground">× {{ log.quantity || 0 }}</span>
                                    </div>
                                    <div class="text-sm text-muted-foreground">
                                        {{ log.location?.name || 'Unknown location' }}
                                    </div>
                                    <div class="text-xs text-muted-foreground">
                                        {{ formatDate(log.date) }}
                                    </div>
                                </div>
                                <div v-if="log.max_size" class="text-right">
                                    <div class="text-sm font-medium">{{ log.max_size }}"</div>
                                    <div class="text-xs text-muted-foreground">max</div>
                                </div>
                            </div>
                            <Link :href="fishingLog()" class="block text-sm text-primary hover:underline text-center pt-2">
                                View all logs →
                            </Link>
                        </div>
                        <div v-else class="text-center py-8">
                            <p class="text-muted-foreground mb-4">No fishing logs yet</p>
                            <Link :href="fishingLog()" class="text-sm text-primary hover:underline">
                                Add your first catch →
                            </Link>
                        </div>
                    </CardContent>
                </Card>

                <!-- Top Locations -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <MapPin class="h-5 w-5" />
                            Top Locations
                        </CardTitle>
                        <CardDescription>Your most productive spots</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="topLocations.length > 0" class="space-y-3">
                            <div v-for="(location, index) in topLocations" :key="location.name" class="flex items-center gap-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10 text-sm font-medium">
                                    {{ index + 1 }}
                                </div>
                                <div class="flex-1">
                                    <div class="font-medium">{{ location.name }}</div>
                                    <div class="text-sm text-muted-foreground">{{ location.total }} catches</div>
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-muted-foreground">No location data available</p>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
