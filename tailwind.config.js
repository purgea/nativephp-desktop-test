import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                wolf: {
                    gold: '#c9a96e',
                    'gold-light': '#e0c992',
                    'gold-dark': '#a8893f',
                    cream: '#f5f0e8',
                    'cream-dark': '#e8ddd0',
                    burgundy: '#8b1a1a',
                    'burgundy-dark': '#5c1010',
                    'burgundy-light': '#a82828',
                    bg: '#0f0b07',
                    'bg-light': '#1a1410',
                    'bg-card': '#211a12',
                    'bg-elevated': '#2a2118',
                    border: '#3d3228',
                    'border-light': '#5a4d3e',
                    'text-muted': '#8a7d6d',
                    'text-secondary': '#b5a898',
                },
            },
        },
    },

    plugins: [forms],
};
