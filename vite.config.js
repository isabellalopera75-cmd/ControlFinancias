import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import { globSync } from 'tinyglobby';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                // JS
                'resources/js/app.js',
                // Cada CSS genera su propio archivo en public/build/assets
                ...globSync('resources/css/*.css'),
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
