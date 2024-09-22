import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import plugin from "tailwindcss";

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
        './resources/js/**/*.js',
        './index.html',
    ],
    theme: {
        asideScrollbars: {
            light: "light",
            dark: "dark",
        },
        extend: {
            zIndex: {
                "-1": "-1",
            },
            flexGrow: {
                5: "5",
            },
            maxHeight: {
                "screen-menu": "calc(100vh - 3.5rem)",
                modal: "calc(100vh - 160px)",
            },
            transitionProperty: {
                position: "right, left, top, bottom, margin, padding",
                textColor: "color",
            },
            keyframes: {
                "fade-out": {
                    from: {opacity: 1},
                    to: {opacity: 0},
                },
                "fade-in": {
                    from: {opacity: 0},
                    to: {opacity: 1},
                },
            },
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            animation: {
                "fade-out": "fade-out 250ms ease-in-out",
                "fade-in": "fade-in 250ms ease-in-out",
            },
            colors: {
                'light': {
                    'background': '#ffffff',
                    'text': '#000000',
                },
                'dark': {
                    'background': '#1a202c',
                    'text': '#ffffff',
                },
            },
        },
    },

    plugins: [
        forms,
        plugin(function ({matchUtilities, theme}) {
            matchUtilities(
                {
                    "aside-scrollbars": (value) => {
                        const track = value === "light" ? "100" : "800";
                        const thumb = value === "light" ? "300" : "600";
                        const color = value === "light" ? "gray" : "dark";

                        return {
                            scrollbarWidth: "thin",
                            scrollbarColor: `${theme(`colors.${color}.${thumb}`)} ${theme(
                                `colors.${color}.${track}`
                            )}`,
                            "&::-webkit-scrollbar": {
                                width: "8px",
                                height: "8px",
                            },
                            "&::-webkit-scrollbar-track": {
                                backgroundColor: theme(`colors.${color}.${track}`),
                            },
                            "&::-webkit-scrollbar-thumb": {
                                borderRadius: "0.25rem",
                                backgroundColor: theme(`colors.${color}.${thumb}`),
                            },
                        };
                    },
                },
                {values: theme("asideScrollbars")}
            );
        }),
    ],
    
    darkMode: 'class', // Enable dark mode
};
