import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './node_modules/flowbite/**/*.js'
    ],
    darkMode: 'class',
    theme: {
        extend: {
            colors: {
                primary: 'hsl(var(--color-primary) / <alpha-value>)',
                secondary: 'hsl(var(--color-secondary) / <alpha-value>)',
                success: 'hsl(var(--color-success) / <alpha-value>)',
                danger: 'hsl(var(--color-danger) / <alpha-value>)',
                'text-base': 'hsl(var(--color-text-base) / <alpha-value>)',
                'bg-base': 'hsl(var(--color-bg-base) / <alpha-value>)',
                'bg-muted': 'hsl(var(--color-bg-muted) / <alpha-value>)',

                // Colores del Sidebar
                'sidebar-bg': 'hsl(var(--color-sidebar-bg) / <alpha-value>)',
                'sidebar-text': 'hsl(var(--color-sidebar-text) / <alpha-value>)',
                'sidebar-text-hover': 'hsl(var(--color-sidebar-text-hover) / <alpha-value>)',
                'sidebar-text-active': 'hsl(var(--color-sidebar-text-active) / <alpha-value>)',
                'sidebar-active-bg': 'hsl(var(--color-sidebar-active-bg) / <alpha-value>)',
            },
            backgroundColor: {
                // Soporte para bg-primary/10 y bg-primary/20 en modo claro/oscuro
                'primary': 'hsl(var(--color-primary) / 1)',
                'primary-10': 'hsl(var(--color-primary) / 0.10)',
                'primary-20': 'hsl(var(--color-primary) / 0.20)',
            },
            textColor: {
                'primary': 'hsl(var(--color-primary) / 1)',
                'primary-700': 'hsl(var(--color-primary) / 0.85)',
                'primary-200': 'hsl(var(--color-primary) / 0.60)',
            },
            borderColor: {
                'primary': 'hsl(var(--color-primary) / 1)',
            },
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [
        forms,
        require('flowbite/plugin')
    ],
};
