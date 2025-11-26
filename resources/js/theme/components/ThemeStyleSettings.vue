<template>
    <div class="theme-style-settings">
        <!-- Card Header -->
        <div
            class="bg-white ring-1 ring-slate-300 dark:bg-transparent dark:ring-slate-600 lg:mx-0 rounded-lg mb-24"
        >
            <!-- Form Content -->
            <form
                @submit.prevent="saveThemeStyle(colors)"
                class=""
            >
                <div
                    v-for="(options, color) in colors"
                    :key="color"
                    :class="!canCustomize(color) ? 'hidden' : 'space-y-4 p-6 border-b border-gray-200 dark:border-gray-600'"
                >
                    <!-- Color Controls Row -->
                    <div class="mb-2 items-center md:flex">
                        <div class="mb-4 grow text-left sm:mb-0">
                            <h4
                                class="text-lg font-medium text-gray-900 dark:text-gray-100"
                            >
                                {{
                                    color.charAt(0).toUpperCase() +
                                    color.slice(1)
                                }}
                            </h4>
                        </div>

                        <div class="flex items-center justify-center space-x-3">
                            <!-- Reset Button -->
                            <div class="mr-3 flex flex-col justify-end">
                                <button
                                    v-if="
                                        !isEqual(
                                            defaultColors[color],
                                            colors[color]
                                        )
                                    "
                                    type="button"
                                    :class="[
                                        'mt-5 text-sm text-danger-600 hover:text-danger-800 dark:text-danger-400 dark:hover:text-danger-300',
                                        resetting ? 'pointer-events-none' : '',
                                    ]"
                                    @click="reset(color)"
                                >
                                    Reset
                                </button>
                                <div v-else class="mt-5 h-5"></div>
                            </div>

                            <!-- Lightness Maximum -->
                            <div class="mr-2 flex flex-col">
                                <label
                                    for="lMax"
                                    class="mb-1 text-xs font-medium text-gray-900 dark:text-gray-100"
                                >
                                    Lightness Maximum
                                </label>
                                <input
                                    id="lMax"
                                    v-model="options.lMax"
                                    type="number"
                                    class="block w-24 rounded-md border-0 bg-white px-2 py-1.5 text-base text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 dark:bg-gray-800 dark:text-gray-100 dark:ring-gray-600 dark:placeholder:text-gray-500 dark:focus:ring-primary-500 sm:text-sm"
                                    @input="generatePalette(color)"
                                />
                            </div>

                            <!-- Lightness Minimum -->
                            <div class="mr-3 flex flex-col">
                                <label
                                    for="lMin"
                                    class="mb-1 text-xs font-medium text-gray-900 dark:text-gray-100"
                                >
                                    Lightness Minimum
                                </label>
                                <input
                                    id="lMin"
                                    v-model="options.lMin"
                                    type="number"
                                    class="block w-24 rounded-md border-0 bg-white px-2 py-1.5 text-base text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 dark:bg-gray-800 dark:text-gray-100 dark:ring-gray-600 dark:placeholder:text-gray-500 dark:focus:ring-primary-500 sm:text-sm"
                                    @input="generatePalette(color)"
                                />
                            </div>

                            <!-- Value Stop Dropdown -->
                            <div class="mr-3 flex flex-col">
                                <label
                                    for="valueStop"
                                    class="mb-1 text-xs font-medium text-gray-900 dark:text-gray-100"
                                >
                                    Shade
                                </label>
                                <select
                                    id="valueStop"
                                    v-model="options.valueStop"
                                    @change="generatePalette(color)"
                                    class="block w-24 rounded-md border-0 bg-white px-2 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary-600 dark:bg-gray-800 dark:text-gray-100 dark:ring-gray-600 dark:focus:ring-primary-500 sm:text-sm"
                                >
                                    <option
                                        v-for="shade in shades"
                                        :key="shade"
                                        :value="shade"
                                    >
                                        {{ shade }}
                                    </option>
                                </select>
                            </div>

                            <!-- Color Picker -->
                            <div class="ml-1 flex flex-col justify-end">
                                <input
                                    type="color"
                                    class="mt-5 h-8 w-8 shrink-0 cursor-pointer appearance-none border-0 bg-white p-0 outline-none dark:bg-gray-800 [&::-webkit-color-swatch]:rounded"
                                    :value="options.hex"
                                    @input="
                                        generatePalette(
                                            color,
                                            $event.target.value
                                        )
                                    "
                                />
                            </div>
                        </div>
                    </div>
                    <!-- Swatches Grid -->
                    <div
                        class="flex flex-col justify-between space-y-1 overflow-hidden md:flex-row md:space-x-1 md:space-y-0"
                    >
                        <div
                            v-for="(swatch, index) in options.swatches"
                            :key="index"
                            class="relative"
                        >
                            <!-- Active Stop Indicator -->
                            <div
                                v-if="swatch.stop === options.valueStop"
                                class="absolute left-1/2 top-10 hidden h-2 -translate-x-1/2 transform items-center justify-center md:flex"
                            >
                                <div
                                    class="-mt-2 h-2 w-2 rounded-full shadow"
                                    :style="{
                                        backgroundColor: getContrast(
                                            swatch.hex
                                        ),
                                    }"
                                />
                            </div>

                            <!-- Swatch Component -->
                            <ThemeStyleSwatch
                                v-model:hex="swatch.hex"
                                :swatch="swatch"
                                :color="color"
                                @update:hex="updateUI()"
                            />
                        </div>
                    </div>
                </div>
                <!-- Footer Actions Bar -->
                <div
                    class="fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-700 border-t border-gray-200 dark:border-gray-600 z-10"
                >
                    <div class="flex justify-end px-6 py-3">
                        <button
                            type="submit"
                            class="inline-flex justify-center rounded-md bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600"
                            :disabled="saving"
                        >
                            {{ saving ? "Saving..." : "Save Changes" }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
import { reactive, ref, toRaw, onMounted } from "vue";
import each from "lodash/each";
import isEqual from "lodash/isEqual";

// Import theme utilities
import { getContrast, hexToTailwindColor, debounce } from "../../utils";
import {
    DEFAULT_PALETTE_CONFIG,
    DEFAULT_STOP,
    SHADES as shades,
} from "../constants";
import { THEME_COLOR_MAPPING } from "../tailwindColors";
import { createSwatches } from "../createSwatches";
import { rgbToHex } from "../helpers";

import ThemeStyleSwatch from "./ThemeStyleSwatch.vue";

export default {
    name: "ThemeStyleSettings",

    components: {
        ThemeStyleSwatch,
    },

    props: {
        initialTheme: {
            type: String,
            default: null,
        },
        saveUrl: {
            type: String,
            required: true,
        },
    },

    emits: ["theme-saved"],

    setup(props, { emit }) {
        // State management
        const colorTypes = [];
        const defaultVars = {};
        const excludedShades = [0, 950, 1000];

        const defaultColors = {};
        const colors = reactive({});
        const resetting = ref(false);
        const saving = ref(false);
        const componentReady = ref(false);

        // Parse default variables
        function parseDefaultVars() {
            // For now, set up basic color types
            const basicColors = [
                "primary",
                "danger",
                "warning",
                "success",
                "info",
                "neutral",
                "secondary",
            ];

            basicColors.forEach((color) => {
                colorTypes.push(color);
                defaultVars[color] = {};

                // Set default values for each shade
                shades.forEach((shade) => {
                    defaultVars[color][shade] = {
                        rgb: `var(--color-${color}-${shade})`,
                        hex: getDefaultHexForColorShade(color, shade),
                    };
                });
            });
        }

        function getDefaultHexForColorShade(color, shade) {
            // Use the official Tailwind CSS colors from the imported THEME_COLOR_MAPPING
            return THEME_COLOR_MAPPING[color]?.[shade] || "#000000";
        }

        function getDefaultConfig(color) {
            return {
                valueStop: DEFAULT_STOP,
                lMax: DEFAULT_PALETTE_CONFIG.lMax,
                lMin: DEFAULT_PALETTE_CONFIG.lMin,
                hex: defaultVars[color][DEFAULT_STOP].hex,
                swatches: shades
                    .filter((shade) => !excludedShades.includes(shade))
                    .map((shade) => ({
                        stop: shade,
                        hex: defaultVars[color][shade].hex,
                    })),
            };
        }

        // Initialize colors
        function initializeColors() {
            parseDefaultVars();

            colorTypes.forEach((color) => {
                colors[color] = getDefaultConfig(color);
                defaultColors[color] = getDefaultConfig(color);
            });

            if (props.initialTheme) {
                setColors(props.initialTheme);
            }

            componentReady.value = true;
        }

        function setColors(colorsJsonString) {
            if (!colorsJsonString) return;

            try {
                let themeStyle = JSON.parse(colorsJsonString);
                each(themeStyle, (options, color) => {
                    if (colors[color]) {
                        colors[color] = options;
                    }
                });
            } catch (error) {
                console.error("Failed to parse theme style:", error);
            }
        }

        // Reset function
        function reset(color) {
            resetting.value = true;
            let nonReactiveColors = structuredClone(toRaw(colors));
            delete nonReactiveColors[color];

            saveThemeStyle(nonReactiveColors, () => {
                colors[color] = getDefaultConfig(color);
                updateUI();
                resetting.value = false;
            });
        }

        // Save function
        function saveThemeStyle(newColors, callback = null) {
            saving.value = true;
            let nonReactiveColors = structuredClone(toRaw(newColors));

            each(nonReactiveColors, (options, color) => {
                if (isEqual(defaultColors[color], options)) {
                    delete nonReactiveColors[color];
                }
            });

            let now = new Date();

            const themeData = {
                theme_style: JSON.stringify(nonReactiveColors),
                theme_style_modified_at:
                    Date.UTC(
                        now.getUTCFullYear(),
                        now.getUTCMonth(),
                        now.getUTCDate(),
                        now.getUTCHours(),
                        now.getUTCMinutes(),
                        now.getUTCSeconds()
                    ) / 1000,
            };

            fetch(props.saveUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content"),
                },
                body: JSON.stringify(themeData),
            })
                .then((response) => response.json())
                .then((data) => {
                    saving.value = false;
                    if (data.success) {
                        // Emit Vue event
                        emit("theme-saved");

                        // Dispatch custom DOM events
                        document.dispatchEvent(new CustomEvent("theme-saved"));

                        // Show success notification using the app's notification system
                        if (window.showNotification) {
                            window.showNotification(
                                "Theme style saved successfully!",
                                "success"
                            );
                        }

                        if (callback) callback();
                    }
                })
                .catch((error) => {
                    console.error("Error saving theme style:", error);
                    saving.value = false;

                    // Show error notification
                    if (window.showNotification) {
                        window.showNotification(
                            "Failed to save theme style. Please try again.",
                            "danger"
                        );
                    }
                });
        }

        // Generate palette for a color
        function generatePalette(color, hexValue = null) {
            if (!componentReady.value) return;

            const options = colors[color];

            if (hexValue) {
                options.hex = hexValue;
            }

            // Remove the # if it exists
            const valueWithoutHash = options.hex.replace("#", "");

            options.swatches = createSwatches({
                value: valueWithoutHash,
                valueStop: options.valueStop,
                h: options.h || 0,
                s: options.s || 0,
                lMin: options.lMin,
                lMax: options.lMax,
            });
        }

        // Update UI after changes
        function updateUI() {
            if (!componentReady.value) return;

            colorTypes.forEach((color) => {
                generatePalette(color);
            });
        }

        // Check if a color can be customized
        function canCustomize(color) {
            return colorTypes.includes(color);
        }

        // Initialize on mount
        onMounted(() => {
            initializeColors();
        });

        return {
            colors,
            defaultColors,
            shades,
            saving,
            resetting,
            getContrast,
            generatePalette,
            saveThemeStyle,
            reset,
            updateUI,
            canCustomize,
            isEqual,
        };
    },
};
</script>
