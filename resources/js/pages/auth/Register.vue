<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import NativeSelect from '@/components/ui/native-select/NativeSelect.vue';
import NativeSelectOption from '@/components/ui/native-select/NativeSelectOption.vue';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { store } from '@/routes/register';
import { Form, Head } from '@inertiajs/vue3';

interface Country {
    id: number;
    name: string;
    code: string;
}

interface Props {
    countries: Country[];
}

defineProps<Props>();
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
                    <Label for="password">Password</Label>
                    <Input
                        id="password"
                        type="password"
                        required
                        :tabindex="7"
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
                        :tabindex="8"
                        autocomplete="new-password"
                        name="password_confirmation"
                        placeholder="Confirm password"
                    />
                    <InputError :message="errors.password_confirmation" />
                </div>

                <Button
                    type="submit"
                    class="mt-2 w-full"
                    tabindex="9"
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
                    :tabindex="10"
                    >Log in</TextLink
                >
            </div>
        </Form>
    </AuthBase>
</template>
