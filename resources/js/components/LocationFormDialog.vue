<template>
    <Dialog v-model:open="isOpen">
        <DialogContent class="max-w-2xl max-h-[90vh] overflow-y-auto">
            <DialogHeader>
                <DialogTitle>{{ isEditMode ? 'Edit Location' : 'Add New Location' }}</DialogTitle>
                <DialogDescription>
                    {{ isEditMode ? 'Update the location details below.' : 'Create a new fishing location.' }}
                </DialogDescription>
            </DialogHeader>

            <Alert v-if="errorMessage" variant="destructive" class="mb-4">
                <AlertCircle class="h-4 w-4" />
                <AlertDescription>{{ errorMessage }}</AlertDescription>
            </Alert>

            <form @submit.prevent="handleSubmit" class="space-y-4">
                <div class="grid gap-2">
                    <Label for="location-name">Location Name *</Label>
                    <Input
                        id="location-name"
                        v-model="formData.name"
                        placeholder="e.g., Yellowstone River"
                        required
                    />
                </div>

                <div class="grid gap-2">
                    <Label for="location-city">City/Town</Label>
                    <Input
                        id="location-city"
                        v-model="formData.city"
                        placeholder="e.g., Livingston"
                    />
                </div>

                <div class="grid gap-2">
                    <Label for="location-state">State/Province/Region</Label>
                    <Input
                        id="location-state"
                        v-model="formData.state"
                        placeholder="e.g., Montana (leave empty for countries without states)"
                    />
                </div>

                <div class="grid gap-2">
                    <Label for="location-country">Country *</Label>
                    <NativeSelect
                        id="location-country"
                        v-model="formData.country_id"
                        class="w-full"
                        required
                    >
                        <NativeSelectOption :value="null" disabled>
                            Select a country
                        </NativeSelectOption>
                        <NativeSelectOption
                            v-for="country in countries"
                            :key="country.id"
                            :value="country.id"
                        >
                            {{ country.name }}
                        </NativeSelectOption>
                    </NativeSelect>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label for="location-latitude">Latitude</Label>
                        <Input
                            id="location-latitude"
                            v-model="formData.latitude"
                            type="number"
                            step="any"
                            placeholder="e.g., 45.6587"
                            @input="userEditedCoordinates = true"
                        />
                    </div>
                    <div class="grid gap-2">
                        <Label for="location-longitude">Longitude</Label>
                        <Input
                            id="location-longitude"
                            v-model="formData.longitude"
                            type="number"
                            step="any"
                            placeholder="e.g., -110.5603"
                            @input="userEditedCoordinates = true"
                        />
                    </div>
                </div>

                <DialogFooter>
                    <div class="text-sm text-muted-foreground flex items-center gap-2 min-h-[20px]">
                        <template v-if="isGeocoding">
                            <div class="h-4 w-4 animate-spin rounded-full border-2 border-primary border-t-transparent"></div>
                            Looking up coordinates...
                        </template>
                    </div>
                    <div class="flex gap-2">
                        <Button type="button" variant="outline" @click="handleCancel">
                            Cancel
                        </Button>
                        <Button type="submit">
                            {{ isEditMode ? 'Update Location' : 'Add Location' }}
                        </Button>
                    </div>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { NativeSelect, NativeSelectOption } from '@/components/ui/native-select';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { AlertCircle } from 'lucide-vue-next';
import axios from '@/lib/axios';

interface Country {
    id: number;
    name: string;
    code: string;
}

interface LocationFormData {
    name: string;
    city: string;
    state: string;
    country_id: number | null;
    latitude: string;
    longitude: string;
}

const props = defineProps<{
    open: boolean;
    editingLocation?: LocationFormData & { id?: number } | null;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    'success': [location: any];
}>();

const isOpen = computed({
    get: () => props.open,
    set: (value) => emit('update:open', value),
});

const isEditMode = computed(() => !!props.editingLocation?.id);
const countries = ref<Country[]>([]);
const errorMessage = ref('');
const isGeocoding = ref(false);
const userEditedCoordinates = ref(false);

const formData = ref<LocationFormData>({
    name: '',
    city: '',
    state: '',
    country_id: null,
    latitude: '',
    longitude: '',
});

// Fetch countries on mount
const fetchCountries = async () => {
    try {
        const response = await axios.get('/countries');
        countries.value = response.data;

        // Set US as default country for new locations (not editing)
        if (!props.editingLocation && formData.value.country_id === null) {
            const usCountry = countries.value.find(c => c.code === 'US');
            if (usCountry) {
                formData.value.country_id = usCountry.id;
            }
        }
    } catch (error) {
        console.error('Error fetching countries:', error);
    }
};

fetchCountries();

// Watch for editing location changes
watch(() => props.editingLocation, (newLocation) => {
    if (newLocation) {
        formData.value = {
            name: newLocation.name || '',
            city: newLocation.city || '',
            state: newLocation.state || '',
            country_id: newLocation.country_id || null,
            latitude: newLocation.latitude?.toString() || '',
            longitude: newLocation.longitude?.toString() || '',
        };
        userEditedCoordinates.value = false;
    } else {
        resetForm();
    }
}, { immediate: true });

// Watch for dialog open state to reset form when opening for new location
watch(() => props.open, (isOpen) => {
    if (isOpen && !props.editingLocation) {
        // Opening for new location - reset form
        resetForm();
    }
});

// Geocoding with debounce
let geocodeTimeout: ReturnType<typeof setTimeout> | null = null;

const geocodeLocation = async () => {
    if (!formData.value.city && !formData.value.state && !formData.value.country_id) {
        return;
    }

    if (geocodeTimeout) {
        clearTimeout(geocodeTimeout);
    }

    geocodeTimeout = setTimeout(async () => {
        isGeocoding.value = true;
        try {
            const response = await axios.post('/locations/geocode', {
                city: formData.value.city,
                state: formData.value.state,
                country_id: formData.value.country_id,
            });

            // Only update if user hasn't manually edited coordinates AND geocoding returned valid results
            if (!userEditedCoordinates.value && response.data.latitude && response.data.longitude) {
                formData.value.latitude = response.data.latitude;
                formData.value.longitude = response.data.longitude;
            }
        } catch (error) {
            console.error('Error geocoding location:', error);
        } finally {
            isGeocoding.value = false;
        }
    }, 800);
};

// Watch for changes to trigger geocoding
watch([() => formData.value.city, () => formData.value.state, () => formData.value.country_id], () => {
    geocodeLocation();
});

const handleSubmit = async () => {
    errorMessage.value = '';
    try {
        const url = isEditMode.value
            ? `/locations/${props.editingLocation!.id}`
            : '/locations';
        const method = isEditMode.value ? 'put' : 'post';

        const response = await axios[method](url, formData.value);
        emit('success', response.data);
        isOpen.value = false;
        resetForm();
    } catch (error: any) {
        errorMessage.value = error.response?.data?.message || 'An error occurred while saving the location.';
    }
};

const handleCancel = () => {
    isOpen.value = false;
    resetForm();
};

const resetForm = () => {
    // Find US country to set as default
    const usCountry = countries.value.find(c => c.code === 'US');

    formData.value = {
        name: '',
        city: '',
        state: '',
        country_id: usCountry ? usCountry.id : null,
        latitude: '',
        longitude: '',
    };
    errorMessage.value = '';
    userEditedCoordinates.value = false;
};
</script>
