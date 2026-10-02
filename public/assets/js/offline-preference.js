/**
 * Reads the user's offline preference from a meta tag and syncs it with
 * the service worker. If disabled, requests a full cache purge.
 */
(function () {
    "use strict";

    const meta = (name) =>
        document.querySelector(`meta[name="${name}"]`)?.content ?? null;

    const allow = meta("allow-offline") === "1";
    const purge = meta("purge-offline-caches") === "1";

    // If the SW is already active, tell it the current preference.
    const send = () => {
        if (!navigator.serviceWorker?.controller) return;
        navigator.serviceWorker.controller.postMessage({
            type: "SET_OFFLINE_PREF",
            enabled: allow,
            purge: purge || !allow, // always purge when disabled
        });
    };

    // Wait for the SW to be ready so the message is delivered.
    if ("serviceWorker" in navigator) {
        navigator.serviceWorker.ready.then(send);
    }

    // If the server told us to purge (user just opted out), do it now
    // as well as in the SW, so the current tab doesn't keep serving cache.
    if (purge || !allow) {
        if ("caches" in window) {
            caches
                .keys()
                .then((keys) => Promise.all(keys.map((k) => caches.delete(k))));
        }
    }
})();
