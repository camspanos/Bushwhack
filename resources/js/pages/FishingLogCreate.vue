<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import FishingLogForm from '@/components/fishing-log/FishingLogForm.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Fish, ArrowLeft, X, CheckCircle2 } from 'lucide-vue-next';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
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

// Form component ref
const fishingLogFormRef = ref<InstanceType<typeof FishingLogForm> | null>(null);

// Success notification
const showSuccessNotification = ref(false);
const newSpeciesName = ref('');

// Handle form submission from FishingLogForm component
const handleFormSubmit = async (submitData: any) => {
    try {
        const response = await axios.post('/fishing-logs', submitData);
        console.log('Fishing log created:', response.data);

        // Navigate back to fishing log page with new species data if applicable
        if (response.data.is_new_species && response.data.fishing_log.fish) {
            // Store in sessionStorage so it persists through navigation
            sessionStorage.setItem('newSpecies', response.data.fishing_log.fish.species);
        }
        // Store personal best data if applicable (only if not a new species)
        else if (response.data.is_personal_best && response.data.fishing_log.fish) {
            sessionStorage.setItem('personalBest', JSON.stringify({
                species: response.data.fishing_log.fish.species,
                size: response.data.fishing_log.max_size,
                previousBest: response.data.previous_best_size
            }));
        }

        router.visit('/fishing-log');
    } catch (error: any) {
        console.error('Error saving fishing log:', error);

        // Set error state on the form component
        if (fishingLogFormRef.value) {
            if (error.response?.status === 422) {
                fishingLogFormRef.value.setErrors(
                    error.response.data.errors || {},
                    error.response.data.message || 'Validation failed. Please check the form.'
                );
            } else {
                fishingLogFormRef.value.setErrors(
                    {},
                    'An error occurred while saving the fishing log. Please try again.'
                );
            }
        }
    }
};

// Cancel and go back
const handleCancel = () => {
    router.visit('/fishing-log');
};
</script>

<template>
    <Head title="Add Fishing Log" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="mx-auto w-full max-w-4xl">
                <!-- Success Notification -->
                <Transition
                    enter-active-class="transition ease-out duration-300"
                    enter-from-class="opacity-0 transform translate-y-2"
                    enter-to-class="opacity-100 transform translate-y-0"
                    leave-active-class="transition ease-in duration-200"
                    leave-from-class="opacity-100 transform translate-y-0"
                    leave-to-class="opacity-0 transform translate-y-2"
                >
                    <Alert v-if="showSuccessNotification" variant="success" class="mb-4 relative !gap-x-6">
                        <CheckCircle2 class="h-5 w-5" />
                        <button
                            @click="showSuccessNotification = false"
                            class="absolute right-2 top-2 rounded-sm opacity-70 ring-offset-background transition-opacity hover:opacity-100 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                        >
                            <X class="h-4 w-4" />
                            <span class="sr-only">Close</span>
                        </button>
                        <AlertTitle class="text-base font-semibold pr-6">🎉&nbsp;&nbsp;Congratulations! New Species Caught!</AlertTitle>
                        <AlertDescription class="text-sm pr-6">
                            <span class="inline">You've logged your first <span class="font-bold">{{ newSpeciesName }}</span>!</span> This is a new species for you to track.
                        </AlertDescription>
                    </Alert>
                </Transition>

                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div>
                                <CardTitle class="flex items-center gap-2">
                                    <Fish class="h-6 w-6" />
                                    Log Your Fishing Trip
                                </CardTitle>
                                <CardDescription>
                                    Record details about your fishing adventure
                                </CardDescription>
                            </div>
                            <Button variant="outline" @click="handleCancel" class="flex items-center gap-2">
                                <ArrowLeft class="h-4 w-4" />
                                Back to Logs
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <FishingLogForm
                            ref="fishingLogFormRef"
                            mode="create"
                            @submit="handleFormSubmit"
                            @cancel="handleCancel"
                        />
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>