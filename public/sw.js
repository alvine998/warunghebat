/* Warung Hebat service worker: offline fallback + static asset cache.
   Versioned build output never needs precaching — it is cached on first use. */
const CACHE = 'warunghebat-v1';
const OFFLINE_URL = '/offline';
const CORE = [
    OFFLINE_URL,
    '/manifest.webmanifest',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
    '/icons/maskable-512.png',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE).then((cache) => cache.addAll(CORE)).then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(keys.filter((key) => key !== CACHE).map((key) => caches.delete(key))))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const { request } = event;

    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);

    if (url.origin !== self.location.origin) {
        return;
    }

    // Navigations: network first, friendly offline page when unreachable.
    if (request.mode === 'navigate') {
        event.respondWith(fetch(request).catch(() => caches.match(OFFLINE_URL)));

        return;
    }

    // Build output, icons, uploads: cache first, populate on miss.
    if (url.pathname.startsWith('/build/') || url.pathname.startsWith('/icons/') || url.pathname.startsWith('/storage/')) {
        event.respondWith(
            caches.match(request).then(
                (hit) => hit || fetch(request).then((res) => {
                    if (res.ok) {
                        const copy = res.clone();
                        caches.open(CACHE).then((cache) => cache.put(request, copy));
                    }

                    return res;
                })
            )
        );

        return;
    }

    // Everything else: network first, stale cache as backup.
    event.respondWith(fetch(request).catch(() => caches.match(request)));
});
