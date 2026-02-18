import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";

import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";
import typography from "@tailwindcss/typography"; // Ajouté si vous l'avez installé

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
        "./resources/js/**/*.vue", // Ajoutez ceci si vous utilisez Vue
    ],

    theme: {
        extend: {
            fontFamily: {
                // Ici, on dit : "Utilise Figtree, sinon les polices sans-serif par défaut"
                sans: ["Figtree", ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [
        forms,
        typography, // On l'active ici
    ],
};
