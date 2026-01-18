<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { about } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Fish, Heart, Users, Target } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'About Us',
        href: about().url,
    },
];
</script>

<template>
    <Head title="About Us" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Hero Section -->
            <div class="text-center space-y-4">
                <h1 class="text-4xl font-bold tracking-tight">About Bushwhack</h1>
                <p class="text-xl text-muted-foreground max-w-2xl mx-auto">
                    Your ultimate companion for tracking and celebrating every fishing adventure
                </p>
            </div>

            <!-- Mission Statement -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2 text-2xl">
                        <Target class="h-6 w-6" />
                        Our Mission
                    </CardTitle>
                </CardHeader>
                <CardContent class="text-lg space-y-4">
                    <p>
                        Bushwhack was created by anglers, for anglers. We understand the thrill of the catch, 
                        the beauty of nature, and the importance of preserving those memories.
                    </p>
                    <p>
                        Our mission is to provide a simple, elegant way to log your fishing experiences, 
                        track your favorite spots, and share your passion with fellow fishing enthusiasts.
                    </p>
                </CardContent>
            </Card>

            <!-- Features Grid -->
            <div class="grid gap-6 md:grid-cols-3">
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Fish class="h-5 w-5 text-primary" />
                            Track Your Catches
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-muted-foreground">
                            Log every catch with detailed information including species, size, location, 
                            equipment used, and more. Build a comprehensive history of your fishing journey.
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Users class="h-5 w-5 text-primary" />
                            Connect with Friends
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-muted-foreground">
                            Keep track of your fishing buddies and the memories you've made together. 
                            Remember who was there for your biggest catches and best adventures.
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Heart class="h-5 w-5 text-primary" />
                            Discover Patterns
                        </CardTitle>
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
            <Card>
                <CardHeader>
                    <CardTitle class="text-2xl">Our Story</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4 text-muted-foreground">
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
            </Card>

            <!-- Call to Action -->
            <Card class="bg-primary/5 border-primary/20">
                <CardContent class="pt-6">
                    <div class="text-center space-y-4">
                        <h3 class="text-2xl font-bold">Ready to Start Logging?</h3>
                        <p class="text-muted-foreground">
                            Every great fishing story deserves to be remembered. Start building your 
                            fishing legacy today.
                        </p>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

