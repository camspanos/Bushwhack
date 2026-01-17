<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Fish, MapPin, Calendar } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Fishing Log',
        href: '/fishing-log',
    },
];

// Form state
const formData = ref({
    date: '',
    location: '',
    species: '',
    quantity: '',
    maxSize: '',
    fly: '',
    rod: '',
    fishingStyle: '',
    fishedWith: '',
    notes: '',
});

const fishSpecies = [
    'Trout',
    'Bass',
    'Salmon',
    'Pike',
    'Walleye',
    'Catfish',
    'Perch',
    'Bluegill',
    'Crappie',
    'Other',
];

const fishingStyles = [
    'Fly Fishing',
    'Spin Fishing',
    'Bait Fishing',
    'Trolling',
    'Ice Fishing',
    'Shore Fishing',
    'Boat Fishing',
];

const handleSubmit = () => {
    console.log('Form submitted:', formData.value);
    // TODO: Implement actual form submission
};
</script>

<template>
    <Head title="Fishing Log" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="mx-auto w-full max-w-4xl">
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Fish class="h-6 w-6" />
                            Log Your Fishing Trip
                        </CardTitle>
                        <CardDescription>
                            Record details about your fishing adventure
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="handleSubmit" class="space-y-6">
                            <!-- Date -->
                            <div class="grid gap-2">
                                <Label for="date" class="flex items-center gap-2">
                                    <Calendar class="h-4 w-4" />
                                    Date
                                </Label>
                                <Input
                                    id="date"
                                    type="date"
                                    v-model="formData.date"
                                    required
                                />
                            </div>

                            <!-- Location -->
                            <div class="grid gap-2">
                                <Label for="location" class="flex items-center gap-2">
                                    <MapPin class="h-4 w-4" />
                                    Location
                                </Label>
                                <Input
                                    id="location"
                                    type="text"
                                    v-model="formData.location"
                                    placeholder="Lake, river, or fishing spot name"
                                    required
                                />
                            </div>

                            <!-- Fish Species and Quantity -->
                            <div class="grid gap-4 md:grid-cols-3">
                                <div class="grid gap-2">
                                    <Label for="species" class="flex items-center gap-2">
                                        <Fish class="h-4 w-4" />
                                        Fish Species
                                    </Label>
                                    <Select v-model="formData.species">
                                        <SelectTrigger id="species">
                                            <SelectValue placeholder="Select species" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem
                                                v-for="species in fishSpecies"
                                                :key="species"
                                                :value="species"
                                            >
                                                {{ species }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div class="grid gap-2">
                                    <Label for="quantity">Quantity Caught</Label>
                                    <Input
                                        id="quantity"
                                        type="number"
                                        v-model="formData.quantity"
                                        placeholder="Number of fish"
                                        min="0"
                                    />
                                </div>
                                <div class="grid gap-2">
                                    <Label for="maxSize">Max Size (inches)</Label>
                                    <Input
                                        id="maxSize"
                                        type="number"
                                        v-model="formData.maxSize"
                                        placeholder="Largest fish"
                                        min="0"
                                        step="0.1"
                                    />
                                </div>
                            </div>

                            <!-- Fly and Rod -->
                            <div class="grid gap-4 md:grid-cols-2">
                                <div class="grid gap-2">
                                    <Label for="fly">Fly</Label>
                                    <Input
                                        id="fly"
                                        type="text"
                                        v-model="formData.fly"
                                        placeholder="e.g., Woolly Bugger, Elk Hair Caddis"
                                    />
                                </div>
                                <div class="grid gap-2">
                                    <Label for="rod">Rod</Label>
                                    <Input
                                        id="rod"
                                        type="text"
                                        v-model="formData.rod"
                                        placeholder="e.g., 5wt 9ft"
                                    />
                                </div>
                            </div>

                            <!-- Style of Fishing -->
                            <div class="grid gap-2">
                                <Label for="fishingStyle">Style of Fishing</Label>
                                <Select v-model="formData.fishingStyle">
                                    <SelectTrigger id="fishingStyle">
                                        <SelectValue placeholder="Select fishing style" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="style in fishingStyles"
                                            :key="style"
                                            :value="style"
                                        >
                                            {{ style }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <!-- Fished With -->
                            <div class="grid gap-2">
                                <Label for="fishedWith">Fished With</Label>
                                <Input
                                    id="fishedWith"
                                    type="text"
                                    v-model="formData.fishedWith"
                                    placeholder="Names of people you fished with"
                                />
                            </div>

                            <!-- Notes -->
                            <div class="grid gap-2">
                                <Label for="notes">Notes</Label>
                                <Textarea
                                    id="notes"
                                    v-model="formData.notes"
                                    placeholder="Additional details about your trip..."
                                    class="min-h-[120px]"
                                />
                            </div>

                            <!-- Submit Button -->
                            <div class="flex justify-end gap-4">
                                <Button type="button" variant="outline">
                                    Cancel
                                </Button>
                                <Button type="submit">
                                    Save Fishing Log
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
