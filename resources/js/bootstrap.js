import axios from "axios";
window.axios = axios;

window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

// Load configuration before initializing Echo
window.addEventListener("DOMContentLoaded", () => {
    // Initialize pusherConfig from server-side if not already set
    if (!window.pusherConfig) {
        window.pusherConfig = {
            key: document
                .querySelector('meta[name="pusher-key"]')
                ?.getAttribute("content"),
            cluster: document
                .querySelector('meta[name="pusher-cluster"]')
                ?.getAttribute("content"),
            auto_dismiss_notification: parseInt(
                document
                    .querySelector('meta[name="pusher-auto-dismiss"]')
                    ?.getAttribute("content") || "5000"
            ),
        };
    }

    // Set up error handling for unhandled promise rejections
    window.addEventListener("unhandledrejection", (event) => {
        if (event.reason && event.reason.message) {
            const errorMessage = event.reason.message.toLowerCase();

            // Handle specific Pusher errors
            if (
                errorMessage.includes("pusher") ||
                errorMessage.includes("connection")
            ) {
                console.error(
                    "Pusher error caught in global handler:",
                    event.reason
                );

                // Attempt to reconnect Echo if appropriate
                const echoManager = window.Alpine?.store("echoManager");
                if (
                    echoManager &&
                    typeof echoManager.reconnect === "function"
                ) {
                    // Wait a bit before reconnecting
                    setTimeout(() => {
                        echoManager.reconnect();
                    }, 5000);
                }
            }
        }
    });
});
import "./pusher/echoManager";
import "./pusher/pusherManager";
