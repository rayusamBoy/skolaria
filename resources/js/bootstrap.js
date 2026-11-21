/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

import axios from "axios";

window.axios = axios;

window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

/**
 * Automatically load the broadcasting service configured in .env.
 * Supported drivers: reverb, pusher
 * The VITE_BROADCAST_CONNECTION variable mirrors BROADCAST_CONNECTION.
 */

const broadcastConnection = (
    import.meta.env.VITE_BROADCAST_CONNECTION || "reverb"
).toLowerCase();

(async () => {
    try {
        switch (broadcastConnection) {
            case "reverb":
                await import("./reverb");
                break;

            case "pusher":
                await import("./pusher");
                break;

            default:
                console.warn(
                    `Unsupported broadcasting connection: ${broadcastConnection}`
                );
        }
    } catch (error) {
        console.error(
            `Failed to initialize ${broadcastConnection} broadcasting:`,
            error
        );
    }
})();
