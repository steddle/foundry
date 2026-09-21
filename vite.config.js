import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

// Builds the workbench's stylesheet, which foundry:assets renders Foundry's own images with.
export default defineConfig({
    plugins: [
        laravel({
            input: ['workbench/resources/css/app.css'],
            publicDirectory: 'workbench/public',
        }),
        tailwindcss(),
    ],
});
