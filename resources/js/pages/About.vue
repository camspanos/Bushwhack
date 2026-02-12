<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { about, dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Heart, Users, Target, Sparkles, MapPin, BarChart3, Waves } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'About Us',
        href: about().url,
    },
];

const page = usePage();
</script>

<template>
    <Head title="About Us" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-8 p-6">
            <!-- Hero Section with Gradient Background -->
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-600 via-teal-500 to-emerald-500 p-8 md:p-12 text-white">
                <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNmZmZmZmYiIGZpbGwtb3BhY2l0eT0iMC4xIj48cGF0aCBkPSJNMzYgMzRjMC0yLjIgMS44LTQgNC00czQgMS44IDQgNC0xLjggNC00IDQtNC0xLjgtNC00eiIvPjwvZz48L2c+PC9zdmc+')] opacity-30"></div>
                <div class="relative z-10 text-center space-y-4">
                    <div class="inline-flex items-center justify-center p-3 bg-white/20 rounded-full backdrop-blur-sm mb-4">
                        <span class="text-4xl">🐟</span>
                    </div>
                    <h1 class="text-4xl md:text-5xl font-bold tracking-tight">About Bushwhack</h1>
                    <p class="text-xl text-white/90 max-w-2xl mx-auto">
                        Your ultimate companion for tracking and celebrating every fishing adventure
                    </p>
                </div>
                <!-- Decorative waves -->
                <Waves class="absolute bottom-4 right-4 h-24 w-24 text-white/10" />
            </div>

            <!-- Mission Statement -->
            <Card class="border-l-4 border-l-blue-500 bg-gradient-to-r from-blue-50/50 to-transparent dark:from-blue-950/20">
                <CardHeader>
                    <CardTitle class="flex items-center gap-3 text-2xl">
                        <div class="p-2 rounded-lg bg-blue-100 dark:bg-blue-900/50">
                            <Target class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                        </div>
                        Our Mission
                    </CardTitle>
                </CardHeader>
                <CardContent class="text-lg space-y-4">
                    <p>
                        Bushwhack was created by anglers, for anglers. We understand the thrill of the catch,
                        the beauty of nature, and the importance of preserving those memories.
                    </p>
                    <p class="text-muted-foreground">
                        Our mission is to provide a simple, elegant way to log your fishing experiences,
                        track your favorite spots, and share your passion with fellow fishing enthusiasts.
                    </p>
                </CardContent>
            </Card>

            <!-- Features Grid -->
            <div class="grid gap-6 md:grid-cols-3">
                <Card class="group hover:shadow-lg transition-all duration-300 hover:-translate-y-1 bg-gradient-to-br from-teal-50/50 to-transparent dark:from-teal-950/20 border-teal-200/50 dark:border-teal-800/30">
                    <CardHeader>
                        <div class="p-3 rounded-xl bg-teal-100 dark:bg-teal-900/50 w-fit mb-2 group-hover:scale-110 transition-transform">
                            <span class="text-2xl">🐠</span>
                        </div>
                        <CardTitle class="text-teal-900 dark:text-teal-100">Track Your Catches</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-muted-foreground">
                            Log every catch with detailed information including species, size, location,
                            equipment used, and more. Build a comprehensive history of your fishing journey.
                        </p>
                    </CardContent>
                </Card>

                <Card class="group hover:shadow-lg transition-all duration-300 hover:-translate-y-1 bg-gradient-to-br from-purple-50/50 to-transparent dark:from-purple-950/20 border-purple-200/50 dark:border-purple-800/30">
                    <CardHeader>
                        <div class="p-3 rounded-xl bg-purple-100 dark:bg-purple-900/50 w-fit mb-2 group-hover:scale-110 transition-transform">
                            <Users class="h-6 w-6 text-purple-600 dark:text-purple-400" />
                        </div>
                        <CardTitle class="text-purple-900 dark:text-purple-100">Connect with Friends</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-muted-foreground">
                            Keep track of your fishing buddies and the memories you've made together.
                            Remember who was there for your biggest catches and best adventures.
                        </p>
                    </CardContent>
                </Card>

                <Card class="group hover:shadow-lg transition-all duration-300 hover:-translate-y-1 bg-gradient-to-br from-rose-50/50 to-transparent dark:from-rose-950/20 border-rose-200/50 dark:border-rose-800/30">
                    <CardHeader>
                        <div class="p-3 rounded-xl bg-rose-100 dark:bg-rose-900/50 w-fit mb-2 group-hover:scale-110 transition-transform">
                            <BarChart3 class="h-6 w-6 text-rose-600 dark:text-rose-400" />
                        </div>
                        <CardTitle class="text-rose-900 dark:text-rose-100">Discover Patterns</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-muted-foreground">
                            Analyze your fishing data to discover what works best. Find your most productive
                            locations, successful flies, and optimal conditions for each species.
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Story Section -->
            <Card class="overflow-hidden">
                <div class="md:flex">
                    <div class="md:w-1/3 bg-gradient-to-br from-emerald-500 to-teal-600 p-8 flex items-center justify-center">
                        <div class="text-center text-white">
                            <Sparkles class="h-16 w-16 mx-auto mb-4 opacity-90" />
                            <h3 class="text-2xl font-bold">Our Story</h3>
                        </div>
                    </div>
                    <div class="md:w-2/3 p-8">
                        <CardContent class="space-y-4 text-muted-foreground p-0">
                            <p>
                                Bushwhack started as a simple idea: what if there was an easy way to remember
                                all those amazing fishing moments? The spot where you caught your first trout,
                                the fly that worked magic on a summer evening, the friend who was there when
                                you landed your personal best.
                            </p>
                            <p>
                                We built Bushwhack to be the fishing log we always wished we had - intuitive,
                                beautiful, and focused on what matters most: your fishing experiences.
                            </p>
                            <p>
                                Whether you're a weekend warrior or a seasoned angler, Bushwhack helps you
                                preserve your fishing legacy and improve your skills with every cast.
                            </p>
                        </CardContent>
                    </div>
                </div>
            </Card>

            <!-- Call to Action -->
            <Card class="bg-gradient-to-r from-amber-50 via-orange-50 to-rose-50 dark:from-amber-950/30 dark:via-orange-950/30 dark:to-rose-950/30 border-amber-200/50 dark:border-amber-800/30">
                <CardContent class="py-10">
                    <div class="text-center space-y-6">
                        <div class="inline-flex items-center justify-center p-3 bg-gradient-to-br from-amber-400 to-orange-500 rounded-full shadow-lg">
                            <Heart class="h-8 w-8 text-white" />
                        </div>
                        <h3 class="text-3xl font-bold bg-gradient-to-r from-amber-600 via-orange-600 to-rose-600 bg-clip-text text-transparent">
                            Ready to Start Logging?
                        </h3>
                        <p class="text-muted-foreground max-w-xl mx-auto text-lg">
                            Every great fishing story deserves to be remembered. Start building your
                            fishing legacy today.
                        </p>
                        <Link :href="dashboard().url">
                            <Button size="lg" class="bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white shadow-md">
                                <MapPin class="mr-2 h-5 w-5" />
                                Go to Dashboard
                            </Button>
                        </Link>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

