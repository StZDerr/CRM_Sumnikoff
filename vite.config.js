import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/app.css",
                "resources/js/sortable.js",
                "resources/js/app.js",
            ],
            refresh: true,
        }),
    ],
    server: {
        host: true, // listen 0.0.0.0
        port: 5173,
        hmr: {
            host: "192.168.0.214", // ваш локальный IP
            protocol: "ws",
        },
    },
});
