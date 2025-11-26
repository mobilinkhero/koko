document.addEventListener("alpine:init", () => {
    Alpine.store("pusherManager", {
        init() {
            this.setupDesktopNotifications();
        },

        setupDesktopNotifications() {
            // Only setup notifications if Pusher is configured and desktop notifications are enabled
            if (
                !this.isPusherConfigured() ||
                !this.isDesktopNotificationEnabled()
            ) {
                return;
            }

            if ("Notification" in window) {
                if (Notification.permission !== "granted") {
                    Notification.requestPermission().then((permission) => {
                        if (permission === "granted") {
                            console.log("Desktop notifications enabled");
                        }
                    });
                }
            }
        },

        isPusherConfigured() {
            return (
                window.pusherConfig?.key &&
                window.pusherConfig?.cluster &&
                window.pusherConfig.key.trim() !== "" &&
                window.pusherConfig.cluster.trim() !== ""
            );
        },

        isDesktopNotificationEnabled() {
            return window.pusherConfig?.desktop_notification === true;
        },

        showDesktopNotification(title, options = {}) {
            // Check if Pusher is configured and desktop notifications are enabled
            if (
                !this.isPusherConfigured() ||
                !this.isDesktopNotificationEnabled()
            ) {
                console.info(
                    "Desktop notifications are disabled or Pusher is not configured"
                );
                return;
            }

            if (
                !("Notification" in window) ||
                Notification.permission !== "granted"
            ) {
                console.warn(
                    "Desktop notifications not supported or permission denied"
                );
                return;
            }

            const defaultOptions = {
                body: options.message,
                icon: window.pusherConfig?.notification_icon || "/img/wm-notification.png",
                silent: false,
            };

            const notificationOptions = {
                ...defaultOptions,
                ...options,
            };

            const notification = new Notification(title, notificationOptions);

            notification.onclick = () => {
                window.focus();
                notification.close();
            };

            setTimeout(() => {
                notification.close();
            }, window.pusherConfig?.auto_dismiss_notification || 5000);
        },

        // Listen to a public channel using the shared Echo instance
        listenToChannel(channelName, eventName, callback) {
            const echoManager = Alpine.store("echoManager");

            if (!echoManager || !echoManager.echo) {
                console.warn(
                    "Echo is not initialized yet. Cannot listen to channel:",
                    channelName
                );
                this.scheduleChannelSubscription(
                    channelName,
                    eventName,
                    callback
                );
                return;
            }

            try {
                const channel = echoManager.echo.channel(channelName);
                channel.listen(eventName, (data) => {
                    // Show notification for the event
                    this.showDesktopNotification(
                        data.title || "New Notification",
                        {
                            message:
                                data.message || "You have a new notification",
                            autoDismiss:
                                window.pusherConfig
                                    ?.auto_dismiss_notification || 5000,
                        }
                    );

                    // Execute the callback if provided
                    if (callback && typeof callback === "function") {
                        callback(data);
                    }
                });
            } catch (error) {
                console.error(
                    `Error listening to channel ${channelName}:`,
                    error
                );
            }
        },

        // Listen to a private channel using the shared Echo instance
        listenToPrivateChannel(channelName, eventName, callback) {
            const echoManager = Alpine.store("echoManager");

            if (!echoManager || !echoManager.echo) {
                console.warn(
                    "Echo is not initialized yet. Cannot listen to private channel:",
                    channelName
                );
                this.schedulePrivateChannelSubscription(
                    channelName,
                    eventName,
                    callback
                );
                return;
            }

            try {
                const channel = echoManager.echo.private(channelName);
                channel.listen(eventName, (data) => {
                    // Show notification for the event
                    this.showDesktopNotification(
                        data.title || "New Private Notification",
                        {
                            message:
                                data.message ||
                                "You have a new private notification",
                            autoDismiss:
                                window.pusherConfig
                                    ?.auto_dismiss_notification || 5000,
                        }
                    );

                    // Execute the callback if provided
                    if (callback && typeof callback === "function") {
                        callback(data);
                    }
                });
            } catch (error) {
                console.error(
                    `Error listening to private channel ${channelName}:`,
                    error
                );
            }
        },

        // Schedule channel subscription for when Echo is ready
        scheduleChannelSubscription(channelName, eventName, callback) {
            const checkInterval = setInterval(() => {
                const echoManager = Alpine.store("echoManager");
                if (
                    echoManager &&
                    echoManager.echo &&
                    echoManager.connectionStatus === "connected"
                ) {
                    clearInterval(checkInterval);
                    this.listenToChannel(channelName, eventName, callback);
                }
            }, 1000); // Check every second

            // Stop checking after 30 seconds to prevent infinite loops
            setTimeout(() => {
                clearInterval(checkInterval);
            }, 30000);
        },

        // Schedule private channel subscription for when Echo is ready
        schedulePrivateChannelSubscription(channelName, eventName, callback) {
            const checkInterval = setInterval(() => {
                const echoManager = Alpine.store("echoManager");
                if (
                    echoManager &&
                    echoManager.echo &&
                    echoManager.connectionStatus === "connected"
                ) {
                    clearInterval(checkInterval);
                    this.listenToPrivateChannel(
                        channelName,
                        eventName,
                        callback
                    );
                }
            }, 1000); // Check every second

            // Stop checking after 30 seconds to prevent infinite loops
            setTimeout(() => {
                clearInterval(checkInterval);
            }, 30000);
        },

        // Direct access to trigger events (rarely needed, but available)
        trigger(channel, event, data) {
            const echoManager = Alpine.store("echoManager");
            const pusherInstance = echoManager?.getPusherInstance();

            if (!pusherInstance) {
                console.error(
                    "Cannot trigger event: Pusher instance not available"
                );
                return false;
            }

            try {
                pusherInstance.trigger(channel, event, data);
                return true;
            } catch (error) {
                console.error("Error triggering Pusher event:", error);
                return false;
            }
        },
    });
});
