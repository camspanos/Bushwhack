<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import PremiumFeatureDialog from '@/components/PremiumFeatureDialog.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { following, usersDashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { UserPlus, UserMinus, Search, Eye, AlertCircle } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import axios from 'axios';

interface FollowingUser {
    id: number;
    name: string;
    email: string;
    member_since: string;
    following_since: string;
}

interface SearchUser {
    id: number;
    name: string;
    email: string;
    member_since: string;
    is_following: boolean;
    can_follow: boolean;
}

const props = defineProps<{
    following: FollowingUser[] | Record<string, FollowingUser>;
}>();

// Convert to array if it's an object
const followingList = computed(() => {
    if (Array.isArray(props.following)) {
        return props.following;
    }
    // If it's an object, convert to array
    return Object.values(props.following);
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Following',
        href: following().url,
    },
];

const page = usePage();
const searchQuery = ref('');
const searchResults = ref<SearchUser[]>([]);
const isSearching = ref(false);
const showUnfollowConfirm = ref(false);
const userToUnfollow = ref<FollowingUser | null>(null);
const showPremiumDialog = ref(false);

async function searchUsers() {
    if (!searchQuery.value.trim()) {
        searchResults.value = [];
        return;
    }

    isSearching.value = true;
    try {
        const response = await axios.get('/users/search', {
            params: { query: searchQuery.value }
        });
        searchResults.value = response.data;
    } catch (error) {
        console.error('Error searching users:', error);
    } finally {
        isSearching.value = false;
    }
}

async function followUser(user: SearchUser) {
    // Check if user can follow (premium check)
    if (!user.can_follow) {
        showPremiumDialog.value = true;
        return;
    }

    try {
        await axios.post(`/users/${user.id}/follow`);
        router.reload({ only: ['following'] });
        searchUsers(); // Refresh search results
    } catch (error) {
        console.error('Error following user:', error);
    }
}

function confirmUnfollow(user: FollowingUser) {
    userToUnfollow.value = user;
    showUnfollowConfirm.value = true;
}

function cancelUnfollow() {
    showUnfollowConfirm.value = false;
    userToUnfollow.value = null;
}

async function unfollowUser() {
    if (!userToUnfollow.value) return;

    try {
        await axios.delete(`/users/${userToUnfollow.value.id}/unfollow`);
        showUnfollowConfirm.value = false;
        userToUnfollow.value = null;
        router.reload({ only: ['following'] });
    } catch (error) {
        console.error('Error unfollowing user:', error);
    }
}

function viewDashboard(userId: number) {
    router.visit(`/users/${userId}/dashboard`);
}
</script>

<template>
    <Head title="Following" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Following</h1>
                    <p class="text-muted-foreground">
                        View and manage users you're following
                    </p>
                </div>
            </div>

            <!-- Search Section -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Search class="h-5 w-5" />
                        Find Users to Follow
                    </CardTitle>
                    <CardDescription>
                        Search for other anglers to follow and view their fishing stats
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="flex gap-2">
                        <Input
                            v-model="searchQuery"
                            placeholder="Search by name or email..."
                            @input="searchUsers"
                            class="flex-1"
                        />
                    </div>

                    <!-- Search Results -->
                    <div v-if="searchResults.length > 0" class="space-y-2">
                        <div
                            v-for="user in searchResults"
                            :key="user.id"
                            class="flex items-center justify-between p-3 border rounded-lg hover:bg-accent/50 transition-colors"
                        >
                            <div>
                                <p class="font-medium">{{ user.name }}</p>
                                <p class="text-sm text-muted-foreground">{{ user.email }}</p>
                                <p class="text-xs text-muted-foreground">Member since {{ user.member_since }}</p>
                            </div>
                            <Button
                                v-if="!user.is_following"
                                @click="followUser(user)"
                                size="sm"
                                variant="default"
                            >
                                <UserPlus class="h-4 w-4 mr-2" />
                                Follow
                            </Button>
                            <Button
                                v-else
                                size="sm"
                                variant="secondary"
                                disabled
                            >
                                <UserPlus class="h-4 w-4 mr-2" />
                                Following
                            </Button>
                        </div>
                    </div>



                    <p v-else-if="searchQuery && !isSearching" class="text-sm text-muted-foreground text-center py-4">
                        No users found
                    </p>
                </CardContent>
            </Card>

            <!-- Following List -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <UserPlus class="h-5 w-5" />
                        Users You're Following ({{ followingList.length }})
                    </CardTitle>
                    <CardDescription>
                        Click on a user to view their public dashboard
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="followingList.length > 0" class="space-y-3">
                        <div
                            v-for="user in followingList"
                            :key="user.id"
                            class="flex items-center justify-between p-4 border rounded-lg hover:bg-accent/50 transition-colors"
                        >
                            <div class="flex-1">
                                <p class="font-medium">{{ user.name }}</p>
                                <p class="text-sm text-muted-foreground">{{ user.email }}</p>
                                <div class="flex gap-4 mt-1">
                                    <p class="text-xs text-muted-foreground">Member since {{ user.member_since }}</p>
                                    <p class="text-xs text-muted-foreground">Following since {{ user.following_since }}</p>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <Button
                                    @click="viewDashboard(user.id)"
                                    size="sm"
                                    variant="outline"
                                >
                                    <Eye class="h-4 w-4 mr-2" />
                                    View Dashboard
                                </Button>
                                <Button
                                    @click="confirmUnfollow(user)"
                                    size="sm"
                                    variant="destructive"
                                >
                                    <UserMinus class="h-4 w-4 mr-2" />
                                    Unfollow
                                </Button>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-12">
                        <UserPlus class="h-12 w-12 mx-auto text-muted-foreground mb-4" />
                        <h3 class="text-lg font-semibold mb-2">Not following anyone yet</h3>
                        <p class="text-muted-foreground">
                            Search for users above to start following other anglers
                        </p>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Unfollow Confirmation Dialog -->
        <Dialog v-model:open="showUnfollowConfirm">
            <DialogContent class="max-w-md">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2 text-destructive">
                        <AlertCircle class="h-5 w-5" />
                        Unfollow User
                    </DialogTitle>
                    <DialogDescription>
                        Are you sure you want to unfollow this user? You will no longer see their fishing activity.
                    </DialogDescription>
                </DialogHeader>
                <div v-if="userToUnfollow" class="py-4">
                    <div class="rounded-lg bg-muted p-4 space-y-2">
                        <p class="text-sm font-medium">User Details:</p>
                        <p class="text-sm text-muted-foreground">
                            <strong>Name:</strong> {{ userToUnfollow.name }}
                        </p>
                        <p class="text-sm text-muted-foreground">
                            <strong>Email:</strong> {{ userToUnfollow.email }}
                        </p>
                    </div>
                </div>
                <DialogFooter>
                    <Button type="button" variant="outline" @click="cancelUnfollow">Cancel</Button>
                    <Button type="button" variant="destructive" @click="unfollowUser">
                        <UserMinus class="h-4 w-4 mr-2" />
                        Unfollow
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Premium Feature Dialog -->
        <PremiumFeatureDialog
            v-model:open="showPremiumDialog"
            title="Following Users is a Premium Feature"
            description="Free users can only follow the featured angler. Upgrade to premium to follow other users and see their fishing statistics, catches, and more."
        />
    </AppLayout>
</template>
