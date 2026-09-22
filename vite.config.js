import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            fonts: [
                // Only 3 weights: body (400), bold (700), display (800).
                // 500/600/900 are synthesized by the browser — saves ~2 font files (~24KB woff2).
                bunny('Plus Jakarta Sans', {
                    weights: [400, 700, 800],
                }),
            ],
        }),
        tailwindcss(),
    ],
    build: {
        cssMinify: true,
        reportCompressedSize: false,
        assetsInlineLimit: 4096,
        // Note: dynamic `import('lenis')` already code-splits into its own
        // chunk automatically — no manualChunks needed (rolldown-vite).
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
