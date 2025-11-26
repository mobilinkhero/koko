import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import path from "path";
import fs from "fs";
import vue from "@vitejs/plugin-vue";
import collectModuleAssetsPaths from './vite-module-loader.js';

const themeFilePath = path.resolve(__dirname, "theme.json");
const activeTheme = fs.existsSync(themeFilePath)
    ? JSON.parse(fs.readFileSync(themeFilePath, "utf8")).name
    : "anchor";
console.log(`Active theme: ${activeTheme}`);

async function getConfig() {
    // Base application assets
    const paths = [
        "resources/css/app.css",
        "resources/css/admin-app.css",
        "resources/css/tenant-app.css",
        "resources/js/app.js",
        "resources/js/admin-app.js",
        "resources/js/tenant-app.js",
    ];

    // Collect module assets (only non-empty files will be added)
    const allPaths = await collectModuleAssetsPaths(paths, 'Modules');

    return defineConfig({
        plugins: [
            laravel({
                input: allPaths,
                refresh: true,
            }),
            vue({
                template: {
                    transformAssetUrls: {
                        base: null,
                        includeAbsolute: false,
                    },
                },
            }),
        ],
        server: {
            cors: true,
            host: "0.0.0.0",
            port: 5173,
            strictPort: false,
        },
        resolve: {
            alias: {
                "@": "/resources/js",
                vue: "vue/dist/vue.esm-bundler.js",
            },
        },
        build: {
            chunkSizeWarningLimit: 1000,
            sourcemap: false,
            rollupOptions: {
                output: {
                    manualChunks: {},
                },
            },
        },
        optimizeDeps: {
            include: [
                "alpinejs",
                "@nextapps-be/livewire-sortablejs",
                "tippy.js",
                "tom-select",
            ],
        },
    });
}

export default getConfig();
