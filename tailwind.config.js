import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: "class",
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ["Inter", "Figtree", ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // SIAKAD Custom Palette (UMPAR Base Theme)
                siakad: {
                    dark: "var(--siakad-dark, #06284B)",
                    primary: "var(--siakad-primary, #0055A5)",
                    secondary: "var(--siakad-secondary, #2D75B4)",
                    light: "var(--siakad-light, #E6F0F9)",
                    50: "#f0f7fb",
                    100: "#e1eff8",
                    200: "#c8e1f2",
                    300: "#a1cdeb",
                    400: "#73b0df",
                    500: "var(--siakad-primary, #0055A5)",
                    600: "var(--siakad-dark, #06284B)",
                    700: "#083766",
                    800: "#0a2f54",
                    900: "#0c2847",
                },
            },
            boxShadow: {
                saas: "0 1px 3px 0 rgb(0 0 0 / 0.05), 0 1px 2px -1px rgb(0 0 0 / 0.05)",
                "saas-md":
                    "0 4px 6px -1px rgb(0 0 0 / 0.05), 0 2px 4px -2px rgb(0 0 0 / 0.05)",
                "saas-lg":
                    "0 10px 15px -3px rgb(0 0 0 / 0.05), 0 4px 6px -4px rgb(0 0 0 / 0.05)",
                card: "0 0 0 1px rgb(0 0 0 / 0.03), 0 2px 4px rgb(0 0 0 / 0.05)",
            },
            borderRadius: {
                saas: "0.625rem",
            },
            animation: {
                "fade-in": "fadeIn 0.2s ease-out",
                "slide-in": "slideIn 0.2s ease-out",
            },
            keyframes: {
                fadeIn: {
                    "0%": { opacity: "0" },
                    "100%": { opacity: "1" },
                },
                slideIn: {
                    "0%": { opacity: "0", transform: "translateY(-4px)" },
                    "100%": { opacity: "1", transform: "translateY(0)" },
                },
            },
        },
    },

    plugins: [forms],
};
