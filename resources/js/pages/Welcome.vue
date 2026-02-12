<script setup lang="ts">
import { dashboard, login, register } from '@/routes';
import { Head, Link } from '@inertiajs/vue3';
import { TrendingUp, Calendar, Users } from 'lucide-vue-next';

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    },
);
</script>

<template>
    <Head title="Welcome" />
    <div class="min-h-screen bg-gradient-to-b from-blue-50 to-white dark:from-gray-900 dark:to-gray-800">
        <!-- Navigation -->
        <nav class="border-b border-gray-200 bg-white/80 backdrop-blur-sm dark:border-gray-700 dark:bg-gray-900/80">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-600">
                            <span class="text-xl">🐟</span>
                        </div>
                        <span class="text-xl font-bold text-gray-900 dark:text-white">Bushwhack</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="dashboard()"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700"
                        >
                            Go to Dashboard
                        </Link>
                        <template v-else>
                            <Link
                                :href="login()"
                                class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
                            >
                                Log in
                            </Link>
                            <Link
                                v-if="canRegister"
                                :href="register()"
                                class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700"
                            >
                                Get Started
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="mb-6 text-5xl font-bold tracking-tight text-gray-900 sm:text-6xl dark:text-white">
                    Track Your Fly Fishing
                    <span class="text-blue-600">Adventures</span>
                </h1>
                <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-600 dark:text-gray-300">
                    The ultimate companion for fly fishing enthusiasts. Log your catches, track your favorite spots,
                    manage your gear, and analyze your fishing patterns all in one place.
                </p>
                <div class="flex flex-col items-center justify-center gap-4 sm:flex-row">
                    <Link
                        v-if="!$page.props.auth.user"
                        :href="register()"
                        class="rounded-lg bg-blue-600 px-8 py-3 text-base font-medium text-white transition hover:bg-blue-700"
                    >
                        Start Tracking Free
                    </Link>
                    <Link
                        v-else
                        :href="dashboard()"
                        class="rounded-lg bg-blue-600 px-8 py-3 text-base font-medium text-white transition hover:bg-blue-700"
                    >
                        Go to Dashboard
                    </Link>
                </div>
            </div>

            <!-- Features Grid -->
            <div class="mt-24 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                <!-- Feature 1 -->
                <div class="rounded-xl border border-gray-200 bg-white p-8 shadow-sm transition hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
                    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900">
                        <span class="text-2xl">🐟</span>
                    </div>
                    <h3 class="mb-2 text-xl font-semibold text-gray-900 dark:text-white">
                        Log Your Catches
                    </h3>
                    <p class="text-gray-600 dark:text-gray-300">
                        Record every catch with details like species, size, location, and the fly you used.
                        Build a comprehensive fishing journal.
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="rounded-xl border border-gray-200 bg-white p-8 shadow-sm transition hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
                    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900">
                        <span class="text-2xl">🌎</span>
                    </div>
                    <h3 class="mb-2 text-xl font-semibold text-gray-900 dark:text-white">
                        Track Locations
                    </h3>
                    <p class="text-gray-600 dark:text-gray-300">
                        Save your favorite fishing spots and see which locations are most productive.
                        Never forget where you had that epic day.
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="rounded-xl border border-gray-200 bg-white p-8 shadow-sm transition hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
                    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-900">
                        <TrendingUp class="h-6 w-6 text-purple-600 dark:text-purple-400" />
                    </div>
                    <h3 class="mb-2 text-xl font-semibold text-gray-900 dark:text-white">
                        Analyze Patterns
                    </h3>
                    <p class="text-gray-600 dark:text-gray-300">
                        Discover trends in your fishing data. See which flies work best,
                        when fish are most active, and track your success rate.
                    </p>
                </div>

                <!-- Feature 4 -->
                <div class="rounded-xl border border-gray-200 bg-white p-8 shadow-sm transition hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
                    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-orange-100 dark:bg-orange-900">
                        <span class="text-2xl">🎣</span>
                    </div>
                    <h3 class="mb-2 text-xl font-semibold text-gray-900 dark:text-white">
                        Manage Your Gear
                    </h3>
                    <p class="text-gray-600 dark:text-gray-300">
                        Keep track of your rods, reels, flies, and equipment.
                        See which gear performs best and plan your tackle box.
                    </p>
                </div>

                <!-- Feature 5 -->
                <div class="rounded-xl border border-gray-200 bg-white p-8 shadow-sm transition hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
                    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-red-100 dark:bg-red-900">
                        <Calendar class="h-6 w-6 text-red-600 dark:text-red-400" />
                    </div>
                    <h3 class="mb-2 text-xl font-semibold text-gray-900 dark:text-white">
                        Track Your Streaks
                    </h3>
                    <p class="text-gray-600 dark:text-gray-300">
                        Monitor your fishing frequency and success streaks.
                        Stay motivated and see your progress over time.
                    </p>
                </div>

                <!-- Feature 6 -->
                <div class="rounded-xl border border-gray-200 bg-white p-8 shadow-sm transition hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
                    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-teal-100 dark:bg-teal-900">
                        <Users class="h-6 w-6 text-teal-600 dark:text-teal-400" />
                    </div>
                    <h3 class="mb-2 text-xl font-semibold text-gray-900 dark:text-white">
                        Fish with Friends
                    </h3>
                    <p class="text-gray-600 dark:text-gray-300">
                        Keep track of your fishing buddies and see who you've had
                        the most successful trips with.
                    </p>
                </div>
            </div>

            <!-- CTA Section -->
            <div class="mt-24 rounded-2xl bg-gradient-to-r from-blue-600 to-blue-700 px-8 py-16 text-center shadow-xl">
                <h2 class="mb-4 text-3xl font-bold text-white sm:text-4xl">
                    Ready to Level Up Your Fly Fishing?
                </h2>
                <p class="mb-8 text-lg text-blue-100">
                    Join Bushwhack today and start tracking your fishing adventures like never before.
                </p>
                <Link
                    v-if="!$page.props.auth.user"
                    :href="register()"
                    class="inline-block rounded-lg bg-white px-8 py-3 text-base font-medium text-blue-600 transition hover:bg-gray-100"
                >
                    Get Started for Free
                </Link>
                <Link
                    v-else
                    :href="dashboard()"
                    class="inline-block rounded-lg bg-white px-8 py-3 text-base font-medium text-blue-600 transition hover:bg-gray-100"
                >
                    Go to Your Dashboard
                </Link>
            </div>
        </div>

        <!-- Footer -->
        <footer class="border-t border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                    <div class="flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-600">
                            <span class="text-lg">🐟</span>
                        </div>
                        <span class="text-lg font-bold text-gray-900 dark:text-white">Bushwhack</span>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        © {{ new Date().getFullYear() }} Bushwhack. Track your fly fishing adventures.
                    </p>
                </div>
            </div>
        </footer>
    </div>
</template>
