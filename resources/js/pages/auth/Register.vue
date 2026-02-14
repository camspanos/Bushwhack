<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import NativeSelect from '@/components/ui/native-select/NativeSelect.vue';
import NativeSelectOption from '@/components/ui/native-select/NativeSelectOption.vue';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { store } from '@/routes/register';
import { CalendarDate, today, getLocalTimeZone } from '@internationalized/date';
import { Form, Head } from '@inertiajs/vue3';
import { Calendar as CalendarIcon } from 'lucide-vue-next';
import { ref } from 'vue';

interface Country {
    id: number;
    name: string;
    code: string;
}

interface Props {
    countries: Country[];
}

defineProps<Props>();

// Birthday date picker state
const birthdayInput = ref('');
const selectedBirthday = ref<CalendarDate | undefined>(undefined);
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
</script>

<template>
    <AuthBase
        title="Create an account"
        description="Enter your details below to create your account"
    >
        <Head title="Register" />

        <Form
            v-bind="store.form()"
            :reset-on-success="['password', 'password_confirmation']"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-6"
        >
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="name">Name</Label>
                    <Input
                        id="name"
                        type="text"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="name"
                        name="name"
                        placeholder="Full name"
                    />
                    <InputError :message="errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">Email address</Label>
                    <Input
                        id="email"
                        type="email"
                        required
                        :tabindex="2"
                        autocomplete="email"
                        name="email"
                        placeholder="email@example.com"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="city">City/Town</Label>
                    <Input
                        id="city"
                        type="text"
                        :tabindex="3"
                        autocomplete="address-level2"
                        name="city"
                        placeholder="e.g., San Francisco"
                    />
                    <InputError :message="errors.city" />
                </div>

                <div class="grid gap-2">
                    <Label for="state">State/Province/Region</Label>
                    <Input
                        id="state"
                        type="text"
                        :tabindex="4"
                        autocomplete="address-level1"
                        name="state"
                        placeholder="e.g., California"
                    />
                    <InputError :message="errors.state" />
                </div>

                <div class="grid gap-2">
                    <Label for="country_id">Country</Label>
                    <NativeSelect
                        id="country_id"
                        name="country_id"
                        :tabindex="5"
                        class="w-full"
                    >
                        <NativeSelectOption value="">
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
                    <InputError :message="errors.country_id" />
                </div>

                <div class="flex items-center space-x-2">
                    <Checkbox
                        id="metric"
                        name="metric"
                        value="1"
                        :tabindex="6"
                    />
                    <Label for="metric" class="cursor-pointer text-sm">
                        Use metric system (cm, kg, m)
                    </Label>
                    <InputError :message="errors.metric" />
                </div>

                <div class="grid gap-2">
                    <Label for="birthday">Birthday (optional)</Label>
                    <input type="hidden" name="birthday" :value="birthdayInput" />
                    <Popover v-model:open="birthdayPickerOpen">
                        <PopoverTrigger as-child>
                            <Button
                                id="birthday"
                                type="button"
                                variant="outline"
                                :tabindex="7"
                                class="w-full justify-between text-left font-normal"
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
                    <InputError :message="errors.birthday" />
                </div>

                <div class="grid gap-2">
                    <Label for="password">Password</Label>
                    <Input
                        id="password"
                        type="password"
                        required
                        :tabindex="8"
                        autocomplete="new-password"
                        name="password"
                        placeholder="Password"
                    />
                    <InputError :message="errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation">Confirm password</Label>
                    <Input
                        id="password_confirmation"
                        type="password"
                        required
                        :tabindex="9"
                        autocomplete="new-password"
                        name="password_confirmation"
                        placeholder="Confirm password"
                    />
                    <InputError :message="errors.password_confirmation" />
                </div>

                <Button
                    type="submit"
                    class="mt-2 w-full"
                    tabindex="10"
                    :disabled="processing"
                    data-test="register-user-button"
                >
                    <Spinner v-if="processing" />
                    Create account
                </Button>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                Already have an account?
                <TextLink
                    :href="login()"
                    class="underline underline-offset-4"
                    :tabindex="11"
                    >Log in</TextLink
                >
            </div>
        </Form>
    </AuthBase>
</template>
