import { ref } from 'vue';
import axios from '@/lib/axios';
import confetti from 'canvas-confetti';

export interface BadgeNotification {
    id: number;
    name: string;
    icon: string;
    description: string;
    rarity: string;
    rarity_colors: {
        bg: string;
        text: string;
        border: string;
    };
}

const NOTIFICATION_TIMEOUT_MS = 20000;

// Shared state across all components using this composable
const showBadgeNotification = ref(false);
const newBadges = ref<BadgeNotification[]>([]);
const isChecking = ref(false);

// Confetti celebration
const triggerConfetti = () => {
    const duration = 3000;
    const animationEnd = Date.now() + duration;
    const defaults = { startVelocity: 30, spread: 360, ticks: 60, zIndex: 9999 };

    const randomInRange = (min: number, max: number) => {
        return Math.random() * (max - min) + min;
    };

    const interval = setInterval(() => {
        const timeLeft = animationEnd - Date.now();

        if (timeLeft <= 0) {
            return clearInterval(interval);
        }

        const particleCount = 50 * (timeLeft / duration);

        confetti({
            ...defaults,
            particleCount,
            origin: { x: randomInRange(0.1, 0.3), y: Math.random() - 0.2 }
        });
        confetti({
            ...defaults,
            particleCount,
            origin: { x: randomInRange(0.7, 0.9), y: Math.random() - 0.2 }
        });
    }, 250);
};

export function useBadgeNotifications() {
    const checkForUnnotifiedBadges = async () => {
        // Prevent multiple simultaneous checks
        if (isChecking.value) return;
        
        isChecking.value = true;
        
        try {
            const response = await axios.get('/badges/unnotified');
            if (response.data && response.data.length > 0) {
                newBadges.value = response.data;
                showBadgeNotification.value = true;
                triggerConfetti();

                // Mark badges as notified
                const badgeIds = response.data.map((b: BadgeNotification) => b.id);
                await axios.post('/badges/mark-notified', { badge_ids: badgeIds });

                // Auto-hide notification after timeout
                setTimeout(() => {
                    showBadgeNotification.value = false;
                }, NOTIFICATION_TIMEOUT_MS);
            }
        } catch (error) {
            console.error('Error checking for unnotified badges:', error);
        } finally {
            isChecking.value = false;
        }
    };

    const dismissNotification = () => {
        showBadgeNotification.value = false;
    };

    return {
        showBadgeNotification,
        newBadges,
        checkForUnnotifiedBadges,
        dismissNotification,
        triggerConfetti,
    };
}

