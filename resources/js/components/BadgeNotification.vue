<script setup lang="ts">
import { onMounted } from 'vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Trophy, X } from 'lucide-vue-next';
import { useBadgeNotifications } from '@/composables/useBadgeNotifications';

const { showBadgeNotification, newBadges, checkForUnnotifiedBadges, dismissNotification } = useBadgeNotifications();

// Check for unnotified badges on mount
onMounted(() => {
    checkForUnnotifiedBadges();
});
</script>

<template>
    <!-- Badge Notification - Fixed position at top of screen -->
    <Teleport to="body">
        <Transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="opacity-0 transform -translate-y-4"
            enter-to-class="opacity-100 transform translate-y-0"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 transform translate-y-0"
            leave-to-class="opacity-0 transform -translate-y-4"
        >
            <div 
                v-if="showBadgeNotification" 
                class="fixed top-4 left-1/2 -translate-x-1/2 z-[9999] w-full max-w-md px-4"
            >
                <Alert variant="success" class="relative !gap-x-6 shadow-lg">
                    <Trophy class="h-5 w-5" />
                    <button
                        @click="dismissNotification"
                        class="absolute right-2 top-2 rounded-sm opacity-70 ring-offset-background transition-opacity hover:opacity-100 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                    >
                        <X class="h-4 w-4" />
                        <span class="sr-only">Close</span>
                    </button>
                    <AlertTitle class="text-base font-semibold pr-6">
                        🏅&nbsp;&nbsp;{{ newBadges.length === 1 ? 'Badge Unlocked!' : `${newBadges.length} Badges Unlocked!` }}
                    </AlertTitle>
                    <AlertDescription class="text-sm pr-6">
                        <div class="flex flex-wrap gap-2 mt-1">
                            <span
                                v-for="badge in newBadges"
                                :key="badge.id"
                                :class="[
                                    'inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium',
                                    badge.rarity_colors.bg,
                                    badge.rarity_colors.text,
                                ]"
                            >
                                {{ badge.icon }} {{ badge.name }}
                            </span>
                        </div>
                    </AlertDescription>
                </Alert>
            </div>
        </Transition>
    </Teleport>
</template>

