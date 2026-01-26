<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { edit as editFollowingSettings, update as updateFollowingSettings } from '@/routes/following-settings';
import { type BreadcrumbItem } from '@/types';
import { Form, Head } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

interface Props {
    allowFollowers: boolean;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Following Settings',
        href: editFollowingSettings(),
    },
];

const allowFollowers = ref(props.allowFollowers);

// Watch for changes from the server
watch(() => props.allowFollowers, (newValue) => {
    allowFollowers.value = newValue;
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Following Settings" />

        <h1 class="sr-only">Following Settings</h1>

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall
                    title="Following Settings"
                    description="Control who can follow you and see your public dashboard"
                />

                <Form
                    v-bind="updateFollowingSettings.form()"
                    #default="{ processing }"
                >
                    <div class="space-y-6">
                        <div class="flex items-start space-x-4 rounded-lg border p-4">
                            <Checkbox
                                id="allow-followers"
                                :model-value="allowFollowers"
                                @update:model-value="(value) => allowFollowers = value"
                            />
                            <input type="hidden" name="allow_followers" :value="allowFollowers ? '1' : '0'" />
                            <div class="flex-1 space-y-1">
                                <Label for="allow-followers" class="text-base font-medium cursor-pointer">
                                    Allow others to follow you
                                </Label>
                                <p class="text-sm text-muted-foreground">
                                    When enabled, other users can follow you and view your public dashboard.
                                    When disabled, users cannot follow you and existing followers will be removed.
                                </p>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <Button type="submit" :disabled="processing">
                                Save Changes
                            </Button>
                        </div>
                    </div>
                </Form>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>

