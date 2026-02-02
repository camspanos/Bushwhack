<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

import HeadingSmall from '@/components/HeadingSmall.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { type BreadcrumbItem } from '@/types';

import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { Check, Crown, Loader2 } from 'lucide-vue-next';

interface SubscriptionPlan {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    price: number;
    billing_interval: string;
    formatted_price: string;
    billing_period: string;
}

interface Subscription {
    id: number;
    status: string;
    starts_at: string;
    ends_at: string | null;
    next_billing_date: string | null;
    cancelled_at: string | null;
    plan: SubscriptionPlan;
}

interface Transaction {
    id: number;
    type: string;
    amount: number;
    formatted_amount: string;
    status: string;
    created_at: string;
}

interface Props {
    plans: SubscriptionPlan[];
    activeSubscription: Subscription | null;
    recentTransactions: Transaction[];
    isPremium: boolean;
}

const props = defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Subscription',
        href: '/settings/subscription',
    },
];

const page = usePage();
const processing = ref(false);
const cancellingSubscription = ref(false);

const formatPrice = (cents: number): string => {
    return '$' + (cents / 100).toFixed(2);
};

const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const checkout = (plan: SubscriptionPlan) => {
    processing.value = true;
    router.post(`/settings/subscription/checkout/${plan.id}`, {}, {
        onFinish: () => {
            processing.value = false;
        },
    });
};

const cancelSubscription = () => {
    if (!confirm('Are you sure you want to cancel your subscription? You will lose access to premium features at the end of your billing period.')) {
        return;
    }

    cancellingSubscription.value = true;
    router.delete('/settings/subscription', {
        onFinish: () => {
            cancellingSubscription.value = false;
        },
    });
};

const getPlanFeatures = (slug: string): string[] => {
    const features = [
        'Follow any angler',
        'Filter dashboard by year',
        'Advanced analytics',
        'Priority support',
    ];

    if (slug === 'annual') {
        features.push('2 months free');
    } else if (slug === 'lifetime') {
        features.push('Never pay again');
        features.push('All future features included');
    }

    return features;
};

const isPopular = (slug: string): boolean => {
    return slug === 'annual';
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Subscription" />

        <h1 class="sr-only">Subscription Settings</h1>

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall
                    title="Subscription"
                    description="Manage your subscription and billing"
                />

                <!-- Active Subscription Section -->
                <div v-if="activeSubscription" class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <Crown class="h-5 w-5 text-yellow-500" />
                                <h3 class="text-lg font-semibold">Premium Member</h3>
                                <Badge variant="default">Active</Badge>
                            </div>
                            <p class="mt-1 text-sm text-muted-foreground">
                                {{ activeSubscription.plan.name }} Plan
                            </p>
                        </div>
                    </div>

                    <div class="mt-4 grid gap-4 sm:grid-cols-3">
                        <div>
                            <p class="text-sm text-muted-foreground">Plan</p>
                            <p class="font-medium">{{ activeSubscription.plan.name }}</p>
                        </div>
                        <div v-if="activeSubscription.next_billing_date">
                            <p class="text-sm text-muted-foreground">Next billing date</p>
                            <p class="font-medium">{{ formatDate(activeSubscription.next_billing_date) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Amount</p>
                            <p class="font-medium">{{ activeSubscription.plan.formatted_price }} {{ activeSubscription.plan.billing_period }}</p>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <Button
                            variant="destructive"
                            @click="cancelSubscription"
                            :disabled="cancellingSubscription"
                        >
                            <Loader2 v-if="cancellingSubscription" class="mr-2 h-4 w-4 animate-spin" />
                            Cancel Subscription
                        </Button>
                    </div>
                </div>

                <!-- Subscription Plans for Free Users -->
                <div v-else class="space-y-6">
                    <div class="rounded-lg border bg-muted/50 p-4">
                        <p class="text-sm text-muted-foreground">
                            Upgrade to Premium to unlock all features including following any angler, year filtering, and advanced analytics.
                        </p>
                    </div>

                    <div class="grid gap-6 md:grid-cols-3">
                        <Card
                            v-for="plan in plans"
                            :key="plan.id"
                            :class="{ 'border-primary ring-2 ring-primary': isPopular(plan.slug) }"
                        >
                            <CardHeader>
                                <div class="flex items-center justify-between">
                                    <CardTitle>{{ plan.name }}</CardTitle>
                                    <Badge v-if="isPopular(plan.slug)" variant="default">Popular</Badge>
                                </div>
                                <CardDescription>{{ plan.description }}</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div class="mb-4">
                                    <span class="text-3xl font-bold">{{ plan.formatted_price }}</span>
                                    <span class="text-muted-foreground"> {{ plan.billing_period }}</span>
                                </div>
                                <ul class="space-y-2">
                                    <li
                                        v-for="feature in getPlanFeatures(plan.slug)"
                                        :key="feature"
                                        class="flex items-center gap-2 text-sm"
                                    >
                                        <Check class="h-4 w-4 text-green-500" />
                                        {{ feature }}
                                    </li>
                                </ul>
                            </CardContent>
                            <CardFooter>
                                <Button
                                    class="w-full"
                                    :variant="isPopular(plan.slug) ? 'default' : 'outline'"
                                    @click="checkout(plan)"
                                    :disabled="processing"
                                >
                                    <Loader2 v-if="processing" class="mr-2 h-4 w-4 animate-spin" />
                                    Get Started
                                </Button>
                            </CardFooter>
                        </Card>
                    </div>
                </div>

                <!-- Recent Transactions -->
                <div v-if="recentTransactions.length > 0" class="space-y-4">
                    <h3 class="text-lg font-semibold">Recent Transactions</h3>
                    <div class="rounded-lg border">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b bg-muted/50">
                                    <th class="px-4 py-3 text-left text-sm font-medium">Date</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium">Type</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium">Amount</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="transaction in recentTransactions"
                                    :key="transaction.id"
                                    class="border-b last:border-0"
                                >
                                    <td class="px-4 py-3 text-sm">{{ formatDate(transaction.created_at) }}</td>
                                    <td class="px-4 py-3 text-sm capitalize">{{ transaction.type }}</td>
                                    <td class="px-4 py-3 text-sm">{{ transaction.formatted_amount }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        <Badge
                                            :variant="transaction.status === 'succeeded' ? 'default' : 'destructive'"
                                        >
                                            {{ transaction.status }}
                                        </Badge>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>

