import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {

    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    /*
    | safelist: classes that Tailwind must ALWAYS include in the compiled CSS
    | even if it cannot find them in any blade file.
    |
    | WHY needed here:
    | Badge colors are stored as strings in the database (badges.color column).
    | Tailwind scans blade files at build time — it never reads the database.
    | Any class that only appears as a PHP/database string gets purged.
    | Adding them to safelist forces Tailwind to keep them regardless.
    |
    | These match exactly the color strings in BadgeSeeder.php.
    */
    safelist: [
        // Badge colors — light mode
        'bg-blue-100',
        'text-blue-700',
        'bg-indigo-100',
        'text-indigo-700',
        'bg-purple-100',
        'text-purple-700',
        'bg-yellow-100',
        'text-yellow-700',
        'bg-amber-100',
        'text-amber-700',
        'bg-green-100',
        'text-green-700',
        'bg-rose-100',
        'text-rose-700',
        'bg-gray-100',
        'text-gray-700',

        // Badge colors — dark mode
        'dark:bg-blue-900',
        'dark:text-blue-400',
        'dark:bg-indigo-900',
        'dark:text-indigo-400',
        'dark:bg-purple-900',
        'dark:text-purple-400',
        'dark:bg-yellow-900',
        'dark:text-yellow-400',
        'dark:bg-amber-900',
        'dark:text-amber-400',
        'dark:bg-green-900',
        'dark:text-green-400',
        'dark:bg-rose-900',
        'dark:text-rose-400',
        'dark:bg-gray-700',
        'dark:text-gray-400',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [
        forms,
        typography,
    ],

};
