importScripts(
    "https://storage.googleapis.com/workbox-cdn/releases/7.4.1/workbox-sw.js",
);

/* ------------------------------------------------------------------
 * Offline preference (persisted in a dedicated marker cache).
 * ------------------------------------------------------------------ */
const PREF_CACHE = "app-prefs";
const PREF_KEY = "offline-enabled";
const AUTH_PAGE_CACHE = "auth-pages";
const PUBLIC_PAGE_CACHE = "public-pages";
const PAGE_CACHES = [AUTH_PAGE_CACHE, PUBLIC_PAGE_CACHE];
let OFFLINE_ENABLED = null; // in‑memory, kept in sync with messages

async function readPref() {
    if (OFFLINE_ENABLED !== null) return OFFLINE_ENABLED;
    try {
        const cache = await caches.open(PREF_CACHE);
        const resp = await cache.match(PREF_KEY);
        OFFLINE_ENABLED = resp ? (await resp.text()) === "1" : false;
    } catch (_) {
        OFFLINE_ENABLED = false;
    }
    return OFFLINE_ENABLED;
}

async function writePref(enabled) {
    const cache = await caches.open(PREF_CACHE);
    if (enabled) {
        await cache.put(PREF_KEY, new Response("1"));
    } else {
        await cache.delete(PREF_KEY);
    }
    OFFLINE_ENABLED = enabled;
}

/* ------------------------------------------------------------------
 * Navigation strategy
 * ------------------------------------------------------------------ */
workbox.routing.registerRoute(
    ({ request }) => request.mode === "navigate",
    async ({ request, url, event }) => {
        const enabled = await readPref();

        if (!enabled) {
            // Opted out: pass-through, network only
            try {
                return await fetch(event.request);
            } catch (_) {
                // Offline and no cache → send to offline page
                const fallback = await caches.match("/offline");
                return fallback || Response.error();
            }
        }

        // Opted in: network first, fall back to cache
        return new workbox.strategies.NetworkFirst({
            cacheName: AUTH_PAGE_CACHE,
            networkTimeoutSeconds: 5,
            plugins: [
                new workbox.cacheableResponse.CacheableResponsePlugin({
                    statuses: [200],
                }),
                new workbox.expiration.ExpirationPlugin({
                    maxEntries: 50,
                    maxAgeSeconds: 60 * 60 * 24, // 24 h
                }),
            ],
        }).handle({ request, event, url });
    },
);

/* ------------------------------------------------------------------
 * Static assets: always cached
 * ------------------------------------------------------------------ */
workbox.recipes.googleFontsCache();
workbox.recipes.staticResourceCache();
workbox.recipes.imageCache({ maxEntries: 500 });

/* ------------------------------------------------------------------
 * Offline fallback
 * ------------------------------------------------------------------ */
workbox.recipes.offlineFallback({ pageFallback: "/offline" });

/* ------------------------------------------------------------------
 * Preference messages from the main thread
 * ------------------------------------------------------------------ */
self.addEventListener("message", (event) => {
    const data = event.data || {};

    if (data.type === "SET_OFFLINE_PREF") {
        writePref(!!data.enabled).then(async () => {
            if (!data.purge) return;
            // Only wipe the HTML page caches — keep static assets
            await Promise.all(PAGE_CACHES.map((name) => caches.delete(name)));
        });
    }

    if (data.type === "CLEAR_CACHES") {
        // Logout: remove everything except the preference marker
        caches
            .keys()
            .then((keys) =>
                Promise.all(
                    keys
                        .filter((k) => k !== PREF_CACHE)
                        .map((k) => caches.delete(k)),
                ),
            );
    }
});
