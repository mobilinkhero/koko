import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";

function generateColorVariant(colorName) {
    return {
        50: `rgb(var(--color-${colorName}-50))`,
        100: `rgb(var(--color-${colorName}-100))`,
        200: `rgb(var(--color-${colorName}-200))`,
        300: `rgb(var(--color-${colorName}-300))`,
        400: `rgb(var(--color-${colorName}-400))`,
        500: `rgb(var(--color-${colorName}-500))`,
        600: `rgb(var(--color-${colorName}-600))`,
        700: `rgb(var(--color-${colorName}-700))`,
        800: `rgb(var(--color-${colorName}-800))`,
        900: `rgb(var(--color-${colorName}-900))`,
    };
}
/** @type {import('tailwindcss').Config} */
export default {
    darkMode: "class",
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
        "./app/Livewire/**/*Table.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
        "./vendor/power-components/livewire-powergrid/resources/views/**/*.php",
        "./vendor/power-components/livewire-powergrid/src/Themes/Tailwind.php",
    ],
    presets: [
        require("./vendor/power-components/livewire-powergrid/tailwind.config.js"),
    ],
    theme: {
        extend: {
            screens: {
                xs: { max: "360px" },
                xss: { min: "400px" }, // Custom media query for 360px
            },
            fontFamily: {
                sans: ["Inter", ...defaultTheme.fontFamily.sans],
                display: ["Lexend", ...defaultTheme.fontFamily.sans],
            },
            animation: {
                "bounce-slow-subtle": "bounce-subtle 7s infinite ease-in-out",
                "infinite-scroll": "infinite-scroll 25s linear infinite",
                "spin-slow": "spin 15s linear infinite",
                "pulse-slow": "pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite",
                "clock-hour-hand": "clockHourHand 12s linear infinite",
                "clock-minute-hand": "clockMinuteHand 60s linear infinite",
            },
            bounce: {
                "0%, 100%": {
                    transform: "translateY(-10%)",
                    animationTimingFunction: "ease-in-out",
                },
                "50%": {
                    transform: "translateY(0)",
                    animationTimingFunction: "ease-in-out",
                },
            },
            keyframes: {
                "infinite-scroll": {
                    from: { transform: "translateX(0)" },
                    to: { transform: "translateX(-100%)" },
                },
                clockHourHand: {
                    "0%": { transform: "rotate(0deg)" },
                    "100%": { transform: "rotate(360deg)" },
                },
                clockMinuteHand: {
                    "0%": { transform: "rotate(0deg)" },
                    "100%": { transform: "rotate(360deg)" },
                },
                "bounce-subtle": {
                    "0%, 100%": { transform: "translateY(0)" },
                    "50%": { transform: "translateY(-20px)" }, // only move up a little
                },
            },
            colors: {
                // Standard Tailwind color palettes
                slate: generateColorVariant("slate"),
                gray: generateColorVariant("gray"),
                zinc: generateColorVariant("zinc"),
                neutral: generateColorVariant("neutral"),
                stone: generateColorVariant("stone"),
                red: generateColorVariant("red"),
                orange: generateColorVariant("orange"),
                amber: generateColorVariant("amber"),
                yellow: generateColorVariant("yellow"),
                lime: generateColorVariant("lime"),
                green: generateColorVariant("green"),
                emerald: generateColorVariant("emerald"),
                teal: generateColorVariant("teal"),
                cyan: generateColorVariant("cyan"),
                sky: generateColorVariant("sky"),
                blue: generateColorVariant("blue"),
                indigo: generateColorVariant("indigo"),
                violet: generateColorVariant("violet"),
                purple: generateColorVariant("purple"),
                fuchsia: generateColorVariant("fuchsia"),
                pink: generateColorVariant("pink"),
                rose: generateColorVariant("rose"),

                // Semantic color mappings (these use the CSS variables that reference the standard colors)
                primary: generateColorVariant("primary"),
                secondary: generateColorVariant("secondary"),
                danger: generateColorVariant("danger"),
                warning: generateColorVariant("warning"),
                success: generateColorVariant("success"),
                info: generateColorVariant("info"),
            },
        },
    },
    plugins: [
        require("@tailwindcss/forms"),
        require("@tailwindcss/typography"),
        require("@tailwindcss/aspect-ratio"),
    ],

    plugins: [forms],
};
