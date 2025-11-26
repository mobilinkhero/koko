import "./bootstrap";
import "./config";
import "./alpine-init";
import "@nextapps-be/livewire-sortablejs";
import "./../../vendor/power-components/livewire-powergrid/dist/powergrid";
import flatpickr from "flatpickr";
import "flatpickr/dist/flatpickr.css";
import "./tippy";
import "./lv-theme-ui";
import GLightbox from "glightbox";
// Import theme utilities
import { refreshThemeStyle } from "./theme/refreshTheme";
// Vue Import
import { createApp } from "vue";
import BotFlowBuilder from "./components/BotFlowBuilder.vue";

import ThemeStyleSettings from "../js/theme/components/ThemeStyleSettings.vue";
import ThemeStyleSwatch from "../js/theme/components/ThemeStyleSwatch.vue";
// import WhatsAppTemplateManager from "../js/dynamic-template/components/WhatsAppTemplateManager.vue";

// Import Vue Select
import vSelect from "vue-select";
import "vue-select/dist/vue-select.css";
// Create Vue app for specific components
document.addEventListener("DOMContentLoaded", function () {

    if (document.getElementById("bot-flow-builder")) {
        const app = createApp({});
        app.component("v-select", vSelect);
        app.component("bot-flow-builder", BotFlowBuilder);
        app.mount("#bot-flow-builder");
    }

    // dynamic templates
    // if (document.getElementById("dynamic-templates")) {
    //     const app = createApp({});
    //     app.component("v-select", vSelect);
    //     app.component("whatsapp-template-manager", WhatsAppTemplateManager);

    //     app.mount("#dynamic-templates");
    // }
    // Initialize theme style app if element exists
    if (document.getElementById("theme-style-app")) {
        const app = createApp({});
        app.component("theme-style-settings", ThemeStyleSettings);
        app.component("theme-style-swatch", ThemeStyleSwatch);
        app.mount("#theme-style-app");
    }

    // Initialize theme refresh handler
    refreshThemeStyle();
});

// Store the lightbox instance globally
window.GLightboxInstance = null;

// Define a global function to initialize GLightbox
window.initGLightbox = function () {
    // Destroy previous instance if it exists
    if (window.GLightboxInstance) {
        window.GLightboxInstance.destroy();
    }

    // Create new instance
    window.GLightboxInstance = GLightbox({
        selector: ".glightbox",
        touchNavigation: true,
        loop: true,
        zoomable: true,
        autoplayVideos: true,
    });
};

// Initialize once on DOM ready
document.addEventListener("DOMContentLoaded", function () {
    window.initGLightbox();
});
