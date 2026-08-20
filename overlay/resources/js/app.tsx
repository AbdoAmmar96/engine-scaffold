import "../css/app.css";
import { createInertiaApp } from "@inertiajs/react";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { createRoot } from "react-dom/client";

createInertiaApp({
    /**
     * تحميل كسول لكل صفحة على حدة.
     * قبل كده كان eager: true — يعني الزائر العادي كان بينزّل كود الأدمن كله
     * في شانك واحد قبل ما يشوف الصفحة الرئيسية.
     */
    resolve: (name) =>
        resolvePageComponent(`./Pages/${name}.tsx`, import.meta.glob("./Pages/**/*.tsx")),
    setup({ el, App, props }) {
        createRoot(el).render(<App {...props} />);
    },
    progress: {
        color: "var(--primary)",
    },
});
