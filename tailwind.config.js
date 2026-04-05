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
            colors: {
                /** Government / NPO institutional palette (UAE greens + restrained gold accent) */
                institution: {
                    banner: '#0f172a',
                    gold: '#b8860b',
                    'gold-light': '#d4a84b',
                },
                /** Semantic surfaces — use for page + card hierarchy */
                surface: {
                    page: '#f4f7f6',
                    card: '#ffffff',
                    raised: '#fafcfc',
                    subtle: '#e8eeec',
                },
            },
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                display: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            maxWidth: {
                content: '72rem',
                prose: '42rem',
            },
            boxShadow: {
                /** Soft emerald-tinted lift for cards */
                card: '0 1px 2px rgb(15 23 42 / 0.04), 0 4px 16px -4px rgb(6 78 59 / 0.08)',
                'card-hover': '0 4px 6px rgb(15 23 42 / 0.04), 0 12px 28px -8px rgb(6 78 59 / 0.12)',
                header: '0 1px 0 0 rgb(15 23 42 / 0.06), 0 4px 20px -6px rgb(15 23 42 / 0.08)',
                nav: '0 1px 0 0 rgb(15 23 42 / 0.05)',
            },
            transitionDuration: {
                theme: '220ms',
            },
            transitionTimingFunction: {
                'out-soft': 'cubic-bezier(0.22, 1, 0.36, 1)',
            },
            keyframes: {
                'fade-up': {
                    '0%': { opacity: '0', transform: 'translateY(10px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
            },
            animation: {
                'fade-up': 'fade-up 0.5s ease-out both',
            },
        },
    },

    plugins: [forms],
};
