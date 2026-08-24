const CACHE_NAME = 'mua-manager-v3';
const STATIC_ASSETS = [
    '/manifest.json',
    '/offline.html',
    '/lip-matt.png',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(STATIC_ASSETS);
        })
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames
                    .filter((name) => name !== CACHE_NAME)
                    .map((name) => caches.delete(name))
            );
        })
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    // Network-first strategy for API/auth requests
    if (event.request.url.includes('/api/') || event.request.url.includes('/login') || event.request.url.includes('/logout')) {
        return;
    }

    // Only handle same-origin and GET requests
    if (event.request.method !== 'GET') {
        return;
    }

    const url = new URL(event.request.url);
    if (url.origin !== self.location.origin) {
        return;
    }

    event.respondWith(
        fetch(event.request)
            .then((response) => {
                if (response && response.status === 200) {
                    const responseClone = response.clone();
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(event.request, responseClone);
                    });
                }
                return response;
            })
            .catch(async () => {
                const cached = await caches.match(event.request);
                if (cached) {
                    return cached;
                }

                // If navigating to a page and offline, serve the offline fallback page
                if (event.request.mode === 'navigate') {
                    return caches.match('/offline.html');
                }

                return new Response('Offline', { status: 503, statusText: 'Service Unavailable' });
            })
    );
});
