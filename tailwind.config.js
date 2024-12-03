const plugin = require("tailwindcss/plugin");

module.exports = {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
        './resources/js/**/*.js'
    ],
    darkMode: "class",
    theme: {
        asideScrollbars: {
            light: "light",
            gray: "gray",
        },
        extend: {
            spacing: {
                '78': '19.5rem',
            },
            colors: {
                'dark-bg': {
                    DEFAULT: '#0f172a',
                    secondary: '#1e293b'
                },
                'dark-surface': {
                    DEFAULT: '#1e293b',
                    hover: '#2d3748',
                    active: '#4a5568'
                },
                'dark-border': {
                    DEFAULT: '#334155',
                    hover: '#475569'
                },
                'dark-text': {
                    DEFAULT: '#f1f5f9',
                    secondary: '#e2e8f0',
                    muted: '#94a3b8'
                },
                'dark-muted': '#6b7280',
                'dark-modal': '#374151',
                'dark-hover': '#2d3748',
                'dark-active': '#4a5568',
                'primary': {
                    50: '#eff6ff',
                    100: '#dbeafe',
                    200: '#bfdbfe',
                    300: '#93c5fd',
                    400: '#60a5fa',
                    500: '#3b82f6',
                    600: '#2563eb',
                    700: '#1d4ed8',
                    800: '#1e40af',
                    900: '#1e3a8a',
                },
                'input-disabled': {
                    light: '#f9fafb',
                    dark: '#1e293b'
                }
            },
            zIndex: {
                "-1": "-1",
                30: '30',
                40: '40',
                50: '50'
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
                    from: { opacity: 1 },
                    to: { opacity: 0 },
                },
                "fade-in": {
                    from: { opacity: 0 },
                    to: { opacity: 1 },
                },
            },
            animation: {
                "fade-out": "fade-out 250ms ease-in-out",
                "fade-in": "fade-in 250ms ease-in-out",
            },
            width: {
                64: '16rem'
            },
            boxShadow: {
                'aside': '0 2px 8px 0 rgb(0 0 0 / 0.1)',
                'menu': '0 4px 6px -1px rgb(0 0 0 / 0.1)',
            },
            '.no-scrollbar::-webkit-scrollbar': {
                display: 'none',
            },
            '.no-scrollbar': {
                '-ms-overflow-style': 'none',
                'scrollbar-width': 'none',
            },
            backgroundColor: {
                'disabled': {
                    light: '#f3f4f6',
                    dark: '#1e293b'
                }
            }
        },
    },
    plugins: [
        require("@tailwindcss/forms"),
        plugin(function ({ matchUtilities, theme }) {
            matchUtilities(
                {
                    "aside-scrollbars": (value) => {
                        const track = value === "light" ? "100" : "900";
                        const thumb = value === "light" ? "300" : "600";
                        const color = value === "light" ? "gray" : value;

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
                { values: theme("asideScrollbars") }
            );
        }),
    ],
};
