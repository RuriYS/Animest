/* eslint-disable no-undef */
/* eslint-disable @typescript-eslint/no-require-imports */

const BrowserSyncPlugin = require('browser-sync-webpack-plugin');
const mix = require('laravel-mix');
const glob = require('glob');

const tsFiles = glob.sync('resources/src/**/*.{ts,tsx}');

tsFiles.forEach((file) => {
    mix.ts(file, 'public/js');
});

mix.react()
    .postCss('resources/css/app.css', 'public/css', [require('tailwindcss')])
    .webpackConfig({
        plugins: [
            new BrowserSyncPlugin({
                proxy: '0.0.0.1:8001',
                files: [
                    'resources/views/**/*.blade.php',
                    'resources/src/**/*',
                    'resources/src/**/*.ts',
                    'resources/js/**/*.js',
                    'resources/css/**/*.css',
                    'public/js/**/*.js',
                    'public/css/**/*.css',
                ],
                notify: false,
                open: true,
            }),
        ],
    });

mix.options({
    hmrOptions: {
        host: 'localhost',
        port: 8081,
    },
    processCssUrls: false,
});
