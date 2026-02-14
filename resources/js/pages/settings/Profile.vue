<script setup lang="ts">
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import { edit } from '@/routes/profile';
import { send } from '@/routes/verification';
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

import DeleteUser from '@/components/DeleteUser.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import NativeSelect from '@/components/ui/native-select/NativeSelect.vue';
import NativeSelectOption from '@/components/ui/native-select/NativeSelectOption.vue';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem } from '@/types';
import { CalendarDate, today, getLocalTimeZone } from '@internationalized/date';
import { CheckCircle2, Calendar as CalendarIcon } from 'lucide-vue-next';

interface Country {
    id: number;
    name: string;
    code: string;
}

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
    countries: Country[];
}

defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Profile settings',
        href: edit().url,
    },
];

const page = usePage();
const user = page.props.auth.user;

// Create refs for form fields that need v-model
const selectedCountryId = ref(user.country_id?.toString() || '');
const isMetric = ref(Boolean(user.metric)); // Explicitly convert to boolean

// Birthday date picker state
const initBirthdayFromUser = () => {
    if (user.birthday) {
        const datePart = user.birthday.split('T')[0];
        const [year, month, day] = datePart.split('-').map(Number);
        return {
            input: datePart,
            date: new CalendarDate(year, month, day)
        };
    }
    return { input: '', date: undefined };
};

const initialBirthday = initBirthdayFromUser();
const birthdayInput = ref(initialBirthday.input);
const selectedBirthday = ref<CalendarDate | undefined>(initialBirthday.date);
const birthdayPickerOpen = ref(false);
const maxBirthdayDate = today(getLocalTimeZone()); // Prevent future date selection

// Handle calendar date selection for birthday
const handleBirthdaySelect = (date: CalendarDate | undefined) => {
    if (!date) return;
    selectedBirthday.value = date;
    const year = date.year;
    const month = String(date.month).padStart(2, '0');
    const day = String(date.day).padStart(2, '0');
    birthdayInput.value = `${year}-${month}-${day}`;
    birthdayPickerOpen.value = false;
};

// Watch for user changes (in case they update from another tab)
watch(() => user.country_id, (newValue) => {
    selectedCountryId.value = newValue?.toString() || '';
});

watch(() => user.metric, (newValue) => {
    isMetric.value = Boolean(newValue); // Explicitly convert to boolean
});

watch(() => user.birthday, (newValue) => {
    if (newValue) {
        const datePart = newValue.split('T')[0];
        const [year, month, day] = datePart.split('-').map(Number);
        birthdayInput.value = datePart;
        selectedBirthday.value = new CalendarDate(year, month, day);
    } else {
        birthdayInput.value = '';
        selectedBirthday.value = undefined;
    }
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Profile settings" />

        <h1 class="sr-only">Profile Settings</h1>

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall
                    title="Profile information"
                    description="Update your name, email address, location, and preferences"
                />

                <Form
                    v-bind="ProfileController.update.form()"
                    class="space-y-6"
                    v-slot="{ errors, processing, recentlySuccessful }"
                >
                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input
                            id="name"
                            class="mt-1 block w-full"
                            name="name"
                            :default-value="user.name"
                            required
                            autocomplete="name"
                            placeholder="Full name"
                        />
                        <InputError class="mt-2" :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Email address</Label>
                        <Input
                            id="email"
                            type="email"
                            class="mt-1 block w-full"
                            name="email"
                            :default-value="user.email"
                            required
                            autocomplete="username"
                            placeholder="Email address"
                        />
                        <InputError class="mt-2" :message="errors.email" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="city">City/Town</Label>
                        <Input
                            id="city"
                            class="mt-1 block w-full"
                            name="city"
                            :default-value="user.city"
                            autocomplete="address-level2"
                            placeholder="e.g., San Francisco"
                        />
                        <InputError class="mt-2" :message="errors.city" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="state">State/Province/Region</Label>
                        <Input
                            id="state"
                            class="mt-1 block w-full"
                            name="state"
                            :default-value="user.state"
                            autocomplete="address-level1"
                            placeholder="e.g., California"
                        />
                        <InputError class="mt-2" :message="errors.state" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="country_id">Country</Label>
                        <NativeSelect
                            id="country_id"
                            name="country_id"
                            v-model="selectedCountryId"
                            class="w-full"
                        >
                            <NativeSelectOption value="" disabled>
                                Select a country
                            </NativeSelectOption>
                            <NativeSelectOption
                                v-for="country in countries"
                                :key="country.id"
                                :value="country.id.toString()"
                            >
                                {{ country.name }}
                            </NativeSelectOption>
                        </NativeSelect>
                        <InputError class="mt-2" :message="errors.country_id" />
                    </div>

                    <div class="flex items-center space-x-2">
                        <Checkbox
                            id="metric"
                            :model-value="isMetric"
                            @update:model-value="(value) => isMetric = value"
                        />
                        <input type="hidden" name="metric" :value="isMetric ? '1' : '0'" />
                        <Label for="metric" class="cursor-pointer">
                            Use metric system (cm, kg, m) instead of imperial (in, lb, ft)
                        </Label>
                        <InputError class="mt-2" :message="errors.metric" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="birthday">Birthday</Label>
                        <input type="hidden" name="birthday" :value="birthdayInput" />
                        <Popover v-model:open="birthdayPickerOpen">
                            <PopoverTrigger as-child>
                                <Button
                                    id="birthday"
                                    type="button"
                                    variant="outline"
                                    class="mt-1 w-full justify-between text-left font-normal"
                                    :class="{ 'text-muted-foreground': !birthdayInput }"
                                >
                                    <span>{{ birthdayInput ? new Date(birthdayInput + 'T00:00:00').toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : 'Select birthday' }}</span>
                                    <CalendarIcon class="h-4 w-4 opacity-50" />
                                </Button>
                            </PopoverTrigger>
                            <PopoverContent class="w-auto p-0" align="start">
                                <Calendar
                                    class="min-w-[280px]"
                                    :model-value="selectedBirthday"
                                    @update:model-value="handleBirthdaySelect"
                                    :max-value="maxBirthdayDate"
                                    layout="month-and-year"
                                />
                            </PopoverContent>
                        </Popover>
                        <InputError class="mt-2" :message="errors.birthday" />
                    </div>

                    <div v-if="mustVerifyEmail && !user.email_verified_at">
                        <p class="-mt-4 text-sm text-muted-foreground">
                            Your email address is unverified.
                            <Link
                                :href="send()"
                                as="button"
                                class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                            >
                                Click here to resend the verification email.
                            </Link>
                        </p>

                        <div
                            v-if="status === 'verification-link-sent'"
                            class="mt-2 text-sm font-medium text-green-600"
                        >
                            A new verification link has been sent to your email
                            address.
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button
                            :disabled="processing"
                            data-test="update-profile-button"
                            >Save</Button
                        >

                        <Transition
                            enter-active-class="transition ease-in-out duration-300"
                            enter-from-class="opacity-0 scale-95"
                            leave-active-class="transition ease-in-out duration-300"
                            leave-to-class="opacity-0 scale-95"
                        >
                            <div
                                v-show="recentlySuccessful"
                                class="flex items-center gap-2 text-sm font-medium text-green-600 dark:text-green-500"
                            >
                                <CheckCircle2 class="h-4 w-4" />
                                <span>Profile updated successfully!</span>
                            </div>
                        </Transition>
                    </div>
                </Form>
            </div>

            <DeleteUser />
        </SettingsLayout>
    </AppLayout>
</template>
