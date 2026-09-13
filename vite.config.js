import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/checkout.css',
                'resources/css/vendor-assets.css',
                'resources/js/app.js',
                'resources/js/address-selector.js',
                'resources/js/chart-vendor.js',
                'resources/js/checkout-address.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
