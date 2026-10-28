// Service Worker minimal untuk syarat install PWA browser
self.addEventListener('install', (event) => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(self.clients.claim());
});

self.addEventListener('fetch', (event) => {
    // Pass-through default fetch
    event.respondWith(fetch(event.request).catch(() => {}));
});
