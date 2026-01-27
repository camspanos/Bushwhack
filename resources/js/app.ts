import '../css/app.css';

import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { initializeTheme } from './composables/useAppearance';

const appName = import.meta.env.VITE_APP_NAME || 'Bushwhack';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

// Handle CSRF token expiration (419 errors)
document.addEventListener('inertia:error', (event) => {
    const response = (event as any).detail?.response;
    if (response?.status === 419) {
        // If this is a logout request, just redirect to home
        // (session expired means user is already logged out)
        if (response?.config?.url?.includes('/logout')) {
            console.log('CSRF token expired during logout, redirecting to home...');
            window.location.href = '/';
        } else {
            // For other requests, reload the page to get a fresh token
            console.log('CSRF token expired, reloading page...');
            window.location.reload();
        }
    }
});

// This will set light / dark mode on page load...
initializeTheme();
