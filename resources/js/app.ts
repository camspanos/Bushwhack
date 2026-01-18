import '../css/app.css';

import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { initializeTheme } from './composables/useAppearance';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

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
router.on('error', (event) => {
    if (event.detail.errors && typeof event.detail.errors === 'object') {
        const response = event.detail.errors as { response?: { status?: number } };
        if (response.response?.status === 419) {
            // CSRF token expired - reload the page to get a fresh token
            window.location.reload();
        }
    }
});

// This will set light / dark mode on page load...
initializeTheme();
