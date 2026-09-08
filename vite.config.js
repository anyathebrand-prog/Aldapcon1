import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    server: {
        host: '0.0.0.0',
        // Assets are served from the container; the browser reaches them
        // through the host port nginx is bound to.
        hmr: { host: 'localhost' },
    },
});
