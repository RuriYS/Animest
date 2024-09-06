/* eslint-disable @typescript-eslint/no-require-imports */
/* eslint-disable no-undef */

module.exports = {
    darkMode: 'media',
    theme: {
        extend: {
            colors: {
                light: {
                    background: '#f0f0f0', // Light gray, not too light
                    primary: '#333333', // Darker gray for text
                    secondary: '#4f4f4f', // Medium gray
                    accent: '#7d7d7d', // Lighter gray accent color
                },
                // Dark mode palette
                dark: {
                    background: '#1e1e1e', // Dark gray, not fully black
                    primary: '#e0e0e0', // Light gray for text
                    secondary: '#b3b3b3', // Medium-light gray
                    accent: '#8d8d8d', // Medium gray accent color
                },
            },
        },
    },
    variants: {
        extend: {},
    },
    content: [
        './resources/views/**/*.blade.php',
        './resources/src/**/*.{ts,tsx}',
    ],
    plugins: [require('@vidstack/react/tailwind.cjs')],
};
