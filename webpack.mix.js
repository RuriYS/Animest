const BrowserSyncPlugin = require('browser-sync-webpack-plugin');
const mix = require('laravel-mix');
const glob = require('glob');
const path = require('path');

const tsFiles = glob.sync('resources/src/**/*.{ts,tsx}');

tsFiles.forEach((file) => {
    mix.ts(file, 'public/js');
});

mix.webpackConfig({
    module: {
        rules: [
            {
                test: /\.tsx?$/,
                loader: 'babel-loader',
                exclude: /node_modules/,
                options: {
                    presets: [
                        '@babel/preset-env',
                        '@babel/preset-react',
                        '@babel/preset-typescript',
                    ],
                    plugins: ['macros', '@emotion'],
                },
            },
        ],
    },
});

mix.react()
    .postCss('resources/css/app.css', 'public/css', [
        require('tailwindcss'),
        require('autoprefixer'),
    ])
    .webpackConfig({
        plugins: [
            new BrowserSyncPlugin({
                proxy: '0.0.0.1:8001',
                files: [
                    'resources/views/**/*.blade.php',
                    'resources/src/**/*.ts',
                    'resources/js/**/*.js',
                    'resources/css/**/*.css',
                ],
                notify: false,
                open: true,
            }),
        ],
        resolve: {
            alias: {
                'twin.macro': path.resolve(
                    __dirname,
                    'node_modules/twin.macro',
                ),
            },
        },
    });

mix.options({
    hmrOptions: {
        host: 'localhost',
        port: 8081,
    },
    processCssUrls: false,
});
