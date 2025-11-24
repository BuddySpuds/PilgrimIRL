/**
 * Debug Utilities for PilgrimIRL
 * Conditional logging that only outputs in development mode
 */

(function(window) {
    'use strict';

    // Detect development mode
    const isDevelopment = window.location.hostname === 'localhost' ||
                         window.location.hostname === '127.0.0.1' ||
                         window.location.hostname.includes('.local') ||
                         window.location.port === '10028';

    // Create debug logger
    window.PilgrimDebug = {
        log: function(...args) {
            if (isDevelopment && console && console.log) {
                console.log('[PilgrimIRL]', ...args);
            }
        },

        warn: function(...args) {
            if (isDevelopment && console && console.warn) {
                console.warn('[PilgrimIRL]', ...args);
            }
        },

        error: function(...args) {
            // Always log errors, even in production
            if (console && console.error) {
                console.error('[PilgrimIRL]', ...args);
            }
        },

        info: function(...args) {
            if (isDevelopment && console && console.info) {
                console.info('[PilgrimIRL]', ...args);
            }
        },

        isDevelopment: isDevelopment
    };

})(window);
