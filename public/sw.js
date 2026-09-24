/* Warung Hebat service worker: offline fallback + static asset cache.
   Versioned build output never needs precaching — it is cached on first use.
   Push notifications (Firebase Cloud Messaging) ride on this same worker so
   the offline cache is never replaced by a second registration: the Firebase
   SDK is loaded lazily here and only initializes when public keys exist
   (see /push/firebase-sw-config.js, served by Laravel from .env). */

/* eslint-disable no-undef */
try {
    importScripts(
        'https://www.gstatic.com/firebasejs/10.12.2/firebase-app-compat.js',
        'https://www.gstatic.com/firebasejs/10.12.2/firebase-messaging-compat.js'
    );
} catch (e) {
    // CDN unreachable (offline install) — worker still serves the cache.
}

try {
    // Sets self.__FIREBASE_CONFIG__ to the public web config, or null when
    // the FIREBASE_* keys are not filled in yet.
    importScripts('/push/firebase-sw-config.js');
} catch (e) {
    self.__FIREBASE_CONFIG__ = null;
}

try {
    if (self.__FIREBASE_CONFIG__ && typeof firebase !== 'undefined' && firebase.messaging) {
        firebase.initializeApp(self.__FIREBASE_CONFIG__);

        const messaging = firebase.messaging();

        // Background push (app closed / in another tab): render the OS/owl
        // notification. Tapping it focuses or opens the linked page.
        messaging.onBackgroundMessage((payload) => {
            const notification = payload.notification || {};
            const data = payload.data || {};
            const title = notification.title || data.title || 'Warung Hebat';
            const options = {
                body: notification.body || data.body || 'Ada kabar baru buatmu.',
                icon: notification.icon || data.icon || '/icons/icon-192.png',
                badge: '/icons/icon-192.png',
                tag: data.tag || 'warunghebat',
                renotify: false,
                data: { url: data.url || notification.click_action || '/' },
            };

            self.registration.showNotification(title, options);
        });
    }
} catch (e) {
    // Misconfigured keys must never break offline caching.
}

self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const rawUrl = event.notification?.data?.url || '/';
    let url = '/';
    try {
        url = new URL(rawUrl, self.location.origin).pathname || '/';
    } catch (e) {
        url = '/';
    }

    event.waitUntil(
        self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windows) => {
            for (const win of windows) {
                try {
                    if (new URL(win.url).pathname === url && 'focus' in win) {
                        return win.focus();
                    }
                } catch (e) {
                    // Cross-origin client — ignore and open fresh below.
                }
            }

            return self.clients.openWindow(url);
        })
    );
});

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
