<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import FishingLogForm from '@/components/FishingLogForm.vue';
import LocationFormDialog from '@/components/LocationFormDialog.vue';
import FishFormDialog from '@/components/FishFormDialog.vue';
import FlyFormDialog from '@/components/FlyFormDialog.vue';
import RodFormDialog from '@/components/RodFormDialog.vue';
import FriendFormDialog from '@/components/FriendFormDialog.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import { Fish } from 'lucide-vue-next';
import axios from '@/lib/axios';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Fishing Log',
        href: '/fishing-log',
    },
    {
        title: 'Add New Log',
        href: '/fishing-log/create',
    },
];

// Dynamic data from API
const locations = ref([]);
const fishSpecies = ref([]);
const flies = ref([]);
const equipment = ref([]);
const friends = ref([]);

// Modal states
const showLocationModal = ref(false);
const showFishModal = ref(false);
const showFlyModal = ref(false);
const showEquipmentModal = ref(false);
const showFriendModal = ref(false);

// Fetch data from API
const fetchLocations = async () => {
    try {
        const response = await axios.get('/locations', {
            params: { per_page: 1000 }
        });
        // Handle paginated response
        const data = response.data.data || response.data;
        const locationsArray = Array.isArray(data) ? data : [];
        locations.value = locationsArray
            .filter((loc: any) => loc !== null && loc !== undefined)
            .sort((a: any, b: any) => a.name.localeCompare(b.name));
    } catch (error) {
        console.error('Error fetching locations:', error);
    }
};

const fetchEquipment = async () => {
    try {
        const response = await axios.get('/rods', {
            params: { per_page: 1000 }
        });
        // Handle paginated response
        const data = response.data.data || response.data;
        const equipmentArray = Array.isArray(data) ? data : [];
        equipment.value = equipmentArray
            .filter((eq: any) => eq !== null && eq !== undefined)
            .sort((a: any, b: any) => a.rod_name.localeCompare(b.rod_name));
    } catch (error) {
        console.error('Error fetching equipment:', error);
    }
};

const fetchFish = async () => {
    try {
        const response = await axios.get('/fish', {
            params: { per_page: 1000 }
        });
        // Handle paginated response
        const data = response.data.data || response.data;
        const fishArray = Array.isArray(data) ? data : [];
        fishSpecies.value = fishArray
            .filter((fish: any) => fish !== null && fish !== undefined)
            .sort((a: any, b: any) => a.species.localeCompare(b.species));
    } catch (error) {
        console.error('Error fetching fish:', error);
    }
};

const fetchFlies = async () => {
    try {
        const response = await axios.get('/flies', {
            params: { per_page: 1000 }
        });
        // Handle paginated response
        const data = response.data.data || response.data;
        const fliesArray = Array.isArray(data) ? data : [];
        flies.value = fliesArray
            .filter((fly: any) => fly !== null && fly !== undefined)
            .sort((a: any, b: any) => a.name.localeCompare(b.name));
    } catch (error) {
        console.error('Error fetching flies:', error);
    }
};

const fetchFriends = async () => {
    try {
        const response = await axios.get('/friends', {
            params: { per_page: 1000 }
        });
        // Handle paginated response
        const data = response.data.data || response.data;
        const friendsArray = Array.isArray(data) ? data : [];
        friends.value = friendsArray
            .filter((friend: any) => friend !== null && friend !== undefined)
            .sort((a: any, b: any) => a.name.localeCompare(b.name));
    } catch (error) {
        console.error('Error fetching friends:', error);
    }
};

// Load all data on mount
onMounted(() => {
    fetchLocations();
    fetchEquipment();
    fetchFish();
    fetchFlies();
    fetchFriends();
});

// Handle location form success
const handleLocationSuccess = (location: any) => {
    locations.value.push(location);
};

// Success handlers for form dialogs
const handleFishSuccess = (fish: any) => {
    fishSpecies.value.push(fish);
};

const handleFlySuccess = (fly: any) => {
    flies.value.push(fly);
};

const handleRodSuccess = (rod: any) => {
    equipment.value.push(rod);
};

const handleFriendSuccess = (friend: any) => {
    friends.value.push(friend);
};

// Handle fishing log form success
const handleFishingLogSuccess = async (data: any) => {
    // Check if it's a new species
    if (data.is_new_species && data.fishing_log?.fish) {
        // Store in sessionStorage to show on fishing log page
        sessionStorage.setItem('newSpecies', data.fishing_log.fish.species);
        router.visit('/fishing-log');
    }
    // Check if it's a personal best (only show if not a new species)
    else if (data.is_personal_best && data.fishing_log?.fish) {
        // Store in sessionStorage to show on fishing log page
        sessionStorage.setItem('personalBest', JSON.stringify({
            species: data.fishing_log.fish.species,
            size: data.fishing_log.max_size,
            previousBest: data.previous_best_size
        }));
        router.visit('/fishing-log');
    }
    // If no special celebration, navigate immediately
    else {
        router.visit('/fishing-log');
    }
};
</script>

<template>
    <Head title="Add Fishing Log" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="mx-auto w-full max-w-6xl">
                <!-- Fishing Log Form in Card -->
                <Card class="bg-gradient-to-br from-teal-50/30 to-transparent dark:from-teal-950/10">
            <CardHeader>
                <CardTitle class="flex items-center gap-2">
                    <div class="rounded-full bg-teal-100 p-1.5 dark:bg-teal-900/30">
                        <Fish class="h-5 w-5 text-teal-600 dark:text-teal-400" />
                    </div>
                    Log Your Fishing Trip
                </CardTitle>
                <CardDescription>
                    Record details about your fishing adventure
                </CardDescription>
            </CardHeader>
            <CardContent class="p-6">
                <FishingLogForm
                        :locations="locations"
                        :fish="fishSpecies"
                        :flies="flies"
                        :equipment="equipment"
                        :friends="friends"
                        :show-cancel-button="true"
                        @success="handleFishingLogSuccess"
                        @cancel="router.visit('/fishing-log')"
                        @open-location-modal="showLocationModal = true"
                        @open-fish-modal="showFishModal = true"
                        @open-fly-modal="showFlyModal = true"
                        @open-equipment-modal="showEquipmentModal = true"
                        @open-friend-modal="showFriendModal = true"
                    />
            </CardContent>
        </Card>

                <!-- Location Form Dialog -->
                <LocationFormDialog
                    v-model:open="showLocationModal"
                    @success="handleLocationSuccess"
                />

                <!-- Fish Form Dialog -->
                <FishFormDialog
                    v-model:open="showFishModal"
                    @success="handleFishSuccess"
                />

                <!-- Fly Form Dialog -->
                <FlyFormDialog
                    v-model:open="showFlyModal"
                    @success="handleFlySuccess"
                />

                <!-- Rod Form Dialog -->
                <RodFormDialog
                    v-model:open="showEquipmentModal"
                    @success="handleRodSuccess"
                />

                <!-- Friend Form Dialog -->
                <FriendFormDialog
                    v-model:open="showFriendModal"
                    @success="handleFriendSuccess"
                />
            </div>
        </div>
    </AppLayout>
</template>




