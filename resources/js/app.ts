import "./bootstrap";
import "../css/app.css";

import { createApp, h, DefineComponent } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { ZiggyVue } from "../../vendor/tightenco/ziggy";
import GlobalLayout from "./Layouts/GlobalLayout.vue"; // <-- 1. ADD THIS IMPORT

const appName = import.meta.env.VITE_APP_NAME || "Laravel";

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    
    // ▼▼▼ 2. REPLACE THE ORIGINAL 'resolve' FUNCTION WITH THIS 'async' ONE ▼▼▼
    resolve: async (name) => {
        const page = await resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob<DefineComponent>("./Pages/**/*.vue"),
        );

        // This line tells Inertia to use GlobalLayout.vue as the default
        // layout for any page that doesn't define its own.
        page.default.layout = page.default.layout || GlobalLayout;
        
        return page;
    },
    // ▲▲▲ END OF REPLACEMENT ▲▲▲

    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: "hsl(163 96.9% 12.7%)",
    },
});