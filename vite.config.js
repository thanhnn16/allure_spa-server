import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { compression } from 'vite-plugin-compression2'

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            ssr: 'resources/js/ssr.js',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
                compilerOptions: {
                    isCustomElement: (tag) => tag === 'emoji-picker'
                }
            },
        }),
        compression({
            algorithm: 'brotliCompress',
            quality: 11,
        }),
    ],
    build: {
        chunkSizeWarningLimit: 1000,
    },
});
