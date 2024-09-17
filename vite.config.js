import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import path from 'path';
import react from '@vitejs/plugin-react';
import tsconfigPaths from 'vite-tsconfig-paths';

export default defineConfig({
    optimizeDeps: {
        esbuildOptions: {
            target: 'es2020',
        },
    },
    esbuild: {
        logOverride: { 'this-is-undefined-in-esm': 'silent' },
    },
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/src/App.tsx'],
            refresh: true,
        }),
        react({
            babel: {
                plugins: [
                    'babel-plugin-macros',
                    [
                        '@emotion/babel-plugin-jsx-pragmatic',
                        {
                            export: 'jsx',
                            import: '__cssprop',
                            module: '@emotion/react',
                        },
                    ],
                    [
                        '@babel/plugin-transform-react-jsx',
                        { pragma: '__cssprop' },
                        'twin.macro',
                    ],
                ],
            },
        }),
        tsconfigPaths(),
    ],
    resolve: {
        alias: {
            'twin.macro': 'twin.macro',
            '@': path.resolve(__dirname, './resources/src'),
        },
    },
    server: {
        proxy: {
            '/api': 'http://0.0.0.1:9000',
        },
        hmr: false,
    },
    build: {
        chunkSizeWarningLimit: 1000,
    },
});
