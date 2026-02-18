import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    primary: '#1C3519',
                    'primary-hover': '#152A13',
                    secondary: '#AD7E07',
                    'secondary-hover': '#8A6506',
                    accent: '#2E522A',
                    'light-gold': '#DDAD26',
                    text: '#282828',
                    border: '#F1F1F1',
                    sidebar: '#f0e8d3',
                },
            },
        },
    },

    plugins: [forms],
};
