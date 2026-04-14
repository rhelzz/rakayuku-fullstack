import { createInertiaApp } from '@inertiajs/vue3';
import { createApp, h } from 'vue';
import type { DefineComponent } from 'vue';

const pages = import.meta.glob<{ default: DefineComponent }>('./pages/**/*.vue');

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: async (name) => {
        const page = pages[`./pages/${name}.vue`];

        if (!page) {
            throw new Error(`Unknown Inertia page: ${name}`);
        }

        const module = await page();

        return module.default;
    },
    setup({ el, App, props, plugin }) {
        createApp({
            render: () => h(App, props),
        })
            .use(plugin)
            .mount(el);
    },
    progress: {
        color: '#059669',
    },
});
