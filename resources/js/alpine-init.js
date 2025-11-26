// Import Alpine.js
import Alpine from "alpinejs";

// Check if Alpine is already defined and initialized
if (!window.Alpine) {
    // Make Alpine.js globally available
    window.Alpine = Alpine;

    // Add a flag to check if Alpine has been initialized
    window.alpineInitialized = false;

    // Only start Alpine once
    document.addEventListener("DOMContentLoaded", () => {
        if (!window.alpineInitialized) {
            Alpine.start();
            window.alpineInitialized = true;
            console.log("Alpine.js initialized");
        }
    });
} else {
    console.log("Alpine.js already initialized, skipping");
}
