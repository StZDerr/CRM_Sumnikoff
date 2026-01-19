import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/app.css",
                "resources/js/sortable.js",
                "resources/js/app.js",
                "resources/js/passport-masks.js",
                "resources/js/projects/comments.js",
                "resources/js/password-generator.js",
            ],
            refresh: true,
        }),
    ],
});
