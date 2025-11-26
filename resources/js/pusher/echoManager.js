import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Global state to track connection attempts and backoff
let connectionAttempts = 0;
const MAX_CONNECTION_ATTEMPTS = 5;
const INITIAL_BACKOFF = 2000; // 2 seconds

document.addEventListener('alpine:init', () => {
  Alpine.store('echoManager', {
    echo: null,
    connectionStatus: 'disconnected',
    error: null,
    isInitializing: false,
    connectionTimeout: null,

    init() {
      // Only initialize once
      if (!this.isInitializing && !this.echo) {
        this.isInitializing = true;
        this.initializeEcho();
      }
    },

    initializeEcho() {
      // Clear any existing initialization
      this.clearConnectionTimeout();

      if (!window.pusherConfig?.key ||
          !window.pusherConfig?.cluster ||
          window.pusherConfig.key.trim() === '' ||
          window.pusherConfig.cluster.trim() === '') {
        console.info('Pusher configuration is missing or incomplete, skipping Echo initialization');
        this.isInitializing = false;
        return;
      }

      try {
        // Make Pusher available globally
        window.Pusher = Pusher;

        // Configure Pusher client with more options
        const pusherOptions = {
          broadcaster: 'pusher',
          key: window.pusherConfig.key,
          cluster: window.pusherConfig.cluster,
          forceTLS: true,
          encrypted: true,
          enabledTransports: ['ws', 'wss'],
          disableStats: true,
          authEndpoint: '/broadcasting/auth',
          auth: {
            headers: {
              'X-CSRF-TOKEN':
                document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
              Accept: 'application/json',
            },
          },
          // Be more tolerant with timeouts
          activityTimeout: 30000, // Increased from default
          pongTimeout: 15000, // Increased from default
          unavailableTimeout: 15000, // Increased from default
        };

        this.echo = new Echo(pusherOptions);

        // Set a connection timeout
        this.setConnectionTimeout();

        // Setup connection event handlers
        this.setupConnectionHandlers();

        // Reset initialization flag
        this.isInitializing = false;
      } catch (error) {
        console.error('Failed to initialize Echo:', error);
        this.error = error.message;
        this.connectionStatus = 'error';
        this.isInitializing = false;

        // Attempt reconnection with exponential backoff
        this.scheduleReconnection();
      }
    },

    setConnectionTimeout() {
      // Set a timeout to detect connection authorization failures
      this.clearConnectionTimeout();
      this.connectionTimeout = setTimeout(() => {
        if (this.connectionStatus !== 'connected') {
          console.warn('Connection authorization timeout');
          this.error = 'Connection authorization timeout';
          this.connectionStatus = 'error';

          // Force reconnection
          this.scheduleReconnection();
        }
      }, 10000); // 10 second timeout for initial connection
    },

    clearConnectionTimeout() {
      if (this.connectionTimeout) {
        clearTimeout(this.connectionTimeout);
        this.connectionTimeout = null;
      }
    },

    setupConnectionHandlers() {
      if (!this.echo?.connector?.pusher) return;

      const pusher = this.echo.connector.pusher;

      // Connection successful
      pusher.connection.bind('connected', () => {

        this.connectionStatus = 'connected';
        this.error = null;
        connectionAttempts = 0; // Reset connection attempts on success
        this.clearConnectionTimeout();
      });

      // Connection closed/disconnected
      pusher.connection.bind('disconnected', () => {

        this.connectionStatus = 'disconnected';
      });

      // Connection error
      pusher.connection.bind('error', (error) => {
        console.error('Pusher connection error:', error);
        this.error = error.message || 'Connection error';
        this.connectionStatus = 'error';

        // Determine if we should reconnect based on error type
        if (
          error.type === 'WebSocketError' ||
          error.type === 'PusherError' ||
          error.data?.code === 4009
        ) {
          this.scheduleReconnection();
        }
      });

      // Failed initial connection
      pusher.connection.bind('failed', () => {
        console.error('Pusher connection failed');
        this.error = 'Connection failed';
        this.connectionStatus = 'error';
        this.scheduleReconnection();
      });
    },

    scheduleReconnection() {
      // Implement exponential backoff for reconnection attempts
      if (connectionAttempts < MAX_CONNECTION_ATTEMPTS) {
        connectionAttempts++;
        const backoff = INITIAL_BACKOFF * Math.pow(2, connectionAttempts - 1);


        setTimeout(() => {

          // Clean up existing connection
          if (this.echo?.connector?.pusher) {
            this.echo.connector.pusher.disconnect();
          }
          this.initializeEcho();
        }, backoff);
      } else {
        console.error(`Maximum reconnection attempts (${MAX_CONNECTION_ATTEMPTS}) reached`);
        // Reset attempts but don't automatically try again
        connectionAttempts = 0;
      }
    },

    reconnect() {
      if (this.echo?.connector?.pusher) {
        this.echo.connector.pusher.connect();
      } else {
        this.initializeEcho();
      }
    },

    disconnect() {
      if (this.echo?.connector?.pusher) {
        this.echo.connector.pusher.disconnect();
      }

      this.connectionStatus = 'disconnected';
    },

    // Get the underlying Pusher instance for direct use
    getPusherInstance() {
      return this.echo?.connector?.pusher || null;
    },
  });
});
