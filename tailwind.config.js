import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography'; // ← add this

/** @type {import('tailwindcss').Config} */
export default {

    /*
   | 'class' strategy means dark mode is driven by the presence of the
   | 'dark' class on the <html> element — which Alpine.js controls.
   | This is what makes localStorage persistence work.
   */
    darkMode: 'class',

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
        },
    },

    plugins: [
        forms,
        typography, // ← add this
    ],
};
