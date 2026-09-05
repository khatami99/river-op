import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            // peta.js dipisah agar Leaflet hanya dimuat pada halaman yang memakainya
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/peta.js'],
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
