// tailwind.config.js
module.exports = {
    content: [
        './index.html',
        './src/**/*.{vue,js,ts,jsx,tsx}',
    ],
    theme: {
        extend: {
            colors: {
                background: {
                    DEFAULT: "#121212",
                    light: "#1f1f1f",
                    lighter: "#2d2d2d",
                },
                text: {
                    DEFAULT: "#ffffff",
                    muted: "#b3b3b3",
                },
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'), // Улучшенная стилизация форм
    ],
};
