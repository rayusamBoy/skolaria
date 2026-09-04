importScripts('https://storage.googleapis.com/workbox-cdn/releases/7.4.1/workbox-sw.js');

workbox.recipes.pageCache();

workbox.recipes.googleFontsCache();

workbox.recipes.staticResourceCache();

workbox.recipes.imageCache({
    maxEntries: 500,
});

workbox.recipes.offlineFallback({
    pageFallback: '/offline',
});