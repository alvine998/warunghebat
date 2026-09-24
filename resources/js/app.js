// PWA: service worker + install prompt. Deferred past first paint, so the
// offline cache and install banner never compete with the landing content.
(function registerPwa() {
    if (!('serviceWorker' in navigator)) return;
    const register = () => navigator.serviceWorker.register('/sw.js', { scope: '/' }).catch(() => {});
    if (document.readyState === 'complete') {
        setTimeout(register, 1);
    } else {
        window.addEventListener('load', () => setTimeout(register, 1), { once: true });
    }
})();

// Push notifications (Firebase Cloud Messaging). Permission is ONLY requested
// after the user taps "Aktifkan" — never on page load. Until the FIREBASE_*
// keys are set, the banner component is not rendered and this stays idle.
(function pushNotifications() {
    const box = document.getElementById('push-notify');
    if (!box) return;

    let config = null;
    try {
        config = JSON.parse(box.dataset.firebaseConfig || 'null');
    } catch {
        config = null;
    }
    if (!config || !config.vapidKey) return;

    const supported = 'Notification' in window
        && 'serviceWorker' in navigator
        && 'PushManager' in window
        && window.isSecureContext === true;
    if (!supported) return;

    const statusEl = box.querySelector('[data-push-status]');
    const enableBtn = box.querySelector('[data-push-enable]');
    const disableBtn = box.querySelector('[data-push-disable]');
    const TOKEN_KEY = 'wh-push-token';
    const DISMISSED_KEY = 'wh-push-dismissed';

    const setStatus = (msg) => {
        if (!statusEl) return;
        if (!msg) {
            statusEl.classList.add('hidden');
            statusEl.textContent = '';
        } else {
            statusEl.textContent = msg;
            statusEl.classList.remove('hidden');
        }
    };
    const show = () => box.classList.remove('hidden');
    const hide = () => box.classList.add('hidden');
    const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

    let firebasePromise = null;
    const loadFirebase = () => {
        if (firebasePromise) return firebasePromise;
        firebasePromise = new Promise((resolve, reject) => {
            if (window.firebase?.messaging) {
                resolve(window.firebase);
                return;
            }
            const version = '10.12.2';
            const files = ['firebase-app-compat.js', 'firebase-messaging-compat.js'];
            const loadOne = (i) => {
                if (i >= files.length) {
                    if (window.firebase?.messaging) resolve(window.firebase);
                    else reject(new Error('sdk'));
                    return;
                }
                const s = document.createElement('script');
                s.src = `https://www.gstatic.com/firebasejs/${version}/${files[i]}`;
                s.async = true;
                s.onload = () => loadOne(i + 1);
                s.onerror = () => reject(new Error('sdk'));
                document.head.appendChild(s);
            };
            loadOne(window.firebase ? 1 : 0);
        });
        return firebasePromise;
    };

    const getMessaging = async () => {
        const fb = await loadFirebase();
        const app = fb.apps?.length
            ? fb.app()
            : fb.initializeApp({
                apiKey: config.apiKey,
                authDomain: config.authDomain,
                projectId: config.projectId,
                storageBucket: config.storageBucket,
                messagingSenderId: config.messagingSenderId,
                appId: config.appId,
            });
        return fb.messaging(app);
    };

    const saveToken = async (token) => {
        const res = await fetch('/push/subscriptions', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrf(),
            },
            body: JSON.stringify({ token, device_name: (navigator.platform || '').slice(0, 100) || null }),
        });
        if (!res.ok) throw new Error('save');
        window.localStorage.setItem(TOKEN_KEY, token);
    };

    const listenForeground = (messaging, fb) => {
        try {
            messaging.onMessage((payload) => {
                const n = payload.notification || {};
                const data = payload.data || {};
                try {
                    new Notification(n.title || data.title || 'Warung Hebat', {
                        body: n.body || data.body || 'Ada kabar baru buatmu.',
                        icon: n.icon || data.icon || '/icons/icon-192.png',
                        tag: data.tag || 'warunghebat',
                    });
                } catch {
                    // Foreground toast fallback is the banner status line.
                    setStatus(n.body || data.body || 'Ada kabar baru buatmu.');
                }
                void fb;
            });
        } catch {
            // Foreground listener is best-effort; background SW still delivers.
        }
    };

    const enable = async () => {
        if (!enableBtn) return;
        enableBtn.disabled = true;
        setStatus('');

        try {
            const permission = await Notification.requestPermission();
            if (permission !== 'granted') {
                if (permission === 'denied') renderDenied();
                else setStatus('Izin notifikasi belum diberikan.');
                enableBtn.disabled = false;
                return;
            }
            setStatus('Menghubungkan…');
            const messaging = await getMessaging();
            const token = await messaging.getToken({ vapidKey: config.vapidKey });
            if (!token) throw new Error('token');
            await saveToken(token);
            listenForeground(messaging, window.firebase);
            window.localStorage.removeItem(DISMISSED_KEY);
            setStatus('Notifikasi aktif di perangkat ini. 🎉');
            enableBtn.classList.add('hidden');
            disableBtn?.classList.remove('hidden');
            setTimeout(hide, 2500);
        } catch {
            setStatus('Gagal mengaktifkan. Cek koneksi lalu coba lagi.');
            enableBtn.disabled = false;
        }
    };

    const disable = async () => {
        const token = window.localStorage.getItem(TOKEN_KEY);
        try {
            if (token) {
                await fetch('/push/subscriptions', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrf(),
                    },
                    body: JSON.stringify({ token }),
                });
            }
        } catch {
            // Token stays server-side; user can retry — never fatal.
        }
        window.localStorage.removeItem(TOKEN_KEY);
        disableBtn?.classList.add('hidden');
        enableBtn?.classList.remove('hidden');
        if (enableBtn) enableBtn.disabled = false;
        setStatus('Notifikasi dimatikan di perangkat ini.');
    };

    const dismiss = () => {
        try {
            window.localStorage.setItem(DISMISSED_KEY, '1');
        } catch {
            // Private mode — banner just hides for this session.
        }
        hide();
    };

    const renderDenied = () => {
        setStatus('Notifikasi diblokir. Buka ikon 🔒/⚙️ di address bar → izinkan Notifikasi untuk situs ini.');
        enableBtn?.classList.add('hidden');
        show();
    };

    enableBtn?.addEventListener('click', enable);
    disableBtn?.addEventListener('click', disable);
    box.querySelector('[data-push-dismiss]')?.addEventListener('click', dismiss);
    box.querySelector('[data-push-later]')?.addEventListener('click', dismiss);

    // Initial state — no permission prompt here, ever.
    const dismissed = (() => {
        try {
            return window.localStorage.getItem(DISMISSED_KEY) === '1';
        } catch {
            return false;
        }
    })();

    if (Notification.permission === 'granted') {
        // Already allowed earlier: silently refresh the token (no prompt).
        hide();
        (async () => {
            try {
                const messaging = await getMessaging();
                const token = await messaging.getToken({ vapidKey: config.vapidKey });
                if (token && token !== window.localStorage.getItem(TOKEN_KEY)) {
                    await saveToken(token);
                }
                listenForeground(messaging, window.firebase);
                enableBtn?.classList.add('hidden');
                disableBtn?.classList.remove('hidden');
            } catch {
                // Offline or SDK blocked — retry on next visit.
            }
        })();
    } else if (Notification.permission === 'denied') {
        renderDenied();
    } else if (!dismissed) {
        show();
    }
})();

let deferredInstallPrompt = null;

document.addEventListener('DOMContentLoaded', () => {
    const installBar = document.getElementById('pwa-install');
    if (!installBar) return;

    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredInstallPrompt = e;
        installBar.classList.remove('hidden');
    });

    document.getElementById('pwa-install-btn')?.addEventListener('click', async () => {
        if (!deferredInstallPrompt) return;
        deferredInstallPrompt.prompt();
        deferredInstallPrompt = null;
        installBar.classList.add('hidden');
    });

    document.getElementById('pwa-install-dismiss')?.addEventListener('click', () => {
        installBar.classList.add('hidden');
    });
});

// Landing interactions. Lenis is lazy-loaded (separate chunk) and only on
// desktop pointers — mobile (the main audience) never downloads it.
document.addEventListener('DOMContentLoaded', () => {
    const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const finePointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
    const shouldUseLenis = !prefersReduced && finePointer;

    const runIdle = (fn) => {
        if ('requestIdleCallback' in window) {
            requestIdleCallback(fn, { timeout: 2000 });
        } else {
            setTimeout(fn, 1);
        }
    };

    // ---------- Lenis smooth scroll (lazy, desktop only) ----------
    if (shouldUseLenis) {
        runIdle(async () => {
            try {
                const { default: Lenis } = await import('lenis');
                const lenis = new Lenis({
                    duration: 1.15,
                    easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
                    smoothWheel: true,
                });

                function raf(time) {
                    lenis.raf(time);
                    requestAnimationFrame(raf);
                }
                requestAnimationFrame(raf);
                window.__lenis = lenis;

                // Route anchor clicks through Lenis
                document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
                    if (anchor.dataset.lenisBound) return;
                    anchor.dataset.lenisBound = '1';
                    anchor.addEventListener('click', (e) => {
                        const id = anchor.getAttribute('href');
                        if (id && id.length > 1) {
                            const target = document.querySelector(id);
                            if (target) {
                                e.preventDefault();
                                closeMobileMenu();
                                lenis.scrollTo(target, { offset: -72, duration: 1.4 });
                            }
                        }
                    });
                });
            } catch {
                // CDN/chunk failed — native anchors still work.
            }
        });
    }

    // Fallback anchor behavior when Lenis is absent (mobile / reduced motion):
    // native smooth scroll via CSS is disabled, so do it gently here.
    if (!shouldUseLenis) {
        document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
            anchor.addEventListener('click', (e) => {
                const id = anchor.getAttribute('href');
                if (id && id.length > 1) {
                    const target = document.querySelector(id);
                    if (target) {
                        e.preventDefault();
                        closeMobileMenu();
                        target.scrollIntoView({ behavior: prefersReduced ? 'auto' : 'smooth', block: 'start' });
                    }
                }
            });
        });
    }

    // ---------- Navbar scroll state + parallax (rAF-throttled, cached nodes) ----------
    const navbar = document.getElementById('navbar');
    const parallaxEls = Array.from(document.querySelectorAll('[data-parallax]')).map((el) => ({
        el,
        speed: parseFloat(el.dataset.parallax || '0.1'),
    }));
    let ticking = false;
    const onScroll = () => {
        if (ticking) return;
        ticking = true;
        requestAnimationFrame(() => {
            ticking = false;
            const y = window.scrollY;
            if (navbar) navbar.classList.toggle('is-scrolled', y > 24);
            // Skip parallax work when blobs are offscreen (past hero)
            if (y < window.innerHeight * 1.2) {
                for (const { el, speed } of parallaxEls) {
                    el.style.transform = `translateY(${(y * speed).toFixed(1)}px)`;
                }
            }
        });
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    // ---------- Mobile menu ----------
    const menuBtn = document.getElementById('menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    const menuIconOpen = document.getElementById('menu-icon-open');
    const menuIconClose = document.getElementById('menu-icon-close');

    function closeMobileMenu() {
        if (!mobileMenu) return;
        mobileMenu.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        menuIconOpen?.classList.remove('hidden');
        menuIconClose?.classList.add('hidden');
        window.__lenis?.start();
    }
    window.closeMobileMenu = closeMobileMenu;

    menuBtn?.addEventListener('click', () => {
        const isHidden = mobileMenu.classList.contains('hidden');
        if (isHidden) {
            mobileMenu.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
            menuIconOpen?.classList.add('hidden');
            menuIconClose?.classList.remove('hidden');
            window.__lenis?.stop();
        } else {
            closeMobileMenu();
        }
    });

    // ---------- Reveal on scroll ----------
    const io = new IntersectionObserver(
        (entries) => {
            for (const entry of entries) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    io.unobserve(entry.target);
                    if (entry.target.hasAttribute('data-count')) {
                        animateCount(entry.target);
                    }
                }
            }
        },
        { threshold: 0.15, rootMargin: '0px 0px -40px 0px' }
    );
    document.querySelectorAll('.reveal, .reveal-scale, [data-count]').forEach((el) => io.observe(el));

    function animateCount(el) {
        if (prefersReduced) {
            const target = parseFloat(el.dataset.count);
            el.textContent = (target >= 1000 ? Math.round(target).toLocaleString('id-ID') : target) + (el.dataset.suffix || '');
            return;
        }
        const target = parseFloat(el.dataset.count);
        const suffix = el.dataset.suffix || '';
        const duration = 1400;
        const start = performance.now();
        function tick(now) {
            const p = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - p, 3);
            const val = target * eased;
            el.textContent = (target >= 1000 ? Math.round(val).toLocaleString('id-ID') : target % 1 !== 0 ? val.toFixed(1) : Math.round(val)) + suffix;
            if (p < 1) requestAnimationFrame(tick);
        }
        requestAnimationFrame(tick);
    }

    // ---------- Pause marquee when offscreen (saves GPU) ----------
    const marqueeBar = document.querySelector('.animate-marquee')?.parentElement;
    if (marqueeBar) {
        new IntersectionObserver(([entry]) => {
            document.body.classList.toggle('marquee-paused', !entry.isIntersecting);
        }).observe(marqueeBar);
    }

    // ---------- Auth + cart-conflict modals ----------
    const loginModal = document.getElementById('modal-login');
    const registerModal = document.getElementById('modal-register');
    const cartConflictModal = document.getElementById('modal-cart-conflict');

    function openModal(modal) {
        if (!modal) return;
        modal.classList.remove('hidden');
        requestAnimationFrame(() => modal.classList.add('modal-open'));
        document.body.classList.add('overflow-hidden');
        window.__lenis?.stop();
    }
    function closeModal(modal) {
        if (!modal) return;
        modal.classList.remove('modal-open');
        setTimeout(() => modal.classList.add('hidden'), 200);
        if (mobileMenu?.classList.contains('hidden')) {
            document.body.classList.remove('overflow-hidden');
            window.__lenis?.start();
        }
    }
    window.openLogin = () => {
        closeModal(registerModal);
        closeModal(cartConflictModal);
        openModal(loginModal);
    };
    window.openRegister = () => {
        closeModal(loginModal);
        closeModal(cartConflictModal);
        openModal(registerModal);
    };
    window.openCartConflict = () => {
        closeModal(loginModal);
        closeModal(registerModal);
        openModal(cartConflictModal);
    };
    window.closeAuthModals = () => {
        closeModal(loginModal);
        closeModal(registerModal);
        closeModal(cartConflictModal);
    };

    // Register role radios drive the "Daftar dengan Google" link's ?role so a
    // brand-new Google account is created with the chosen role.
    document.querySelectorAll('[data-google-role]').forEach((link) => {
        const form = link.closest('form');
        if (!form) return;
        const sync = () => {
            const checked = form.querySelector('input[name="role"]:checked');
            const url = new URL(link.href);
            url.searchParams.set('role', checked ? checked.value : 'pembeli');
            link.href = url.toString();
        };
        form.querySelectorAll('input[name="role"]').forEach((radio) => radio.addEventListener('change', sync));
        sync();
    });

    // ---------- Product image zoom (store detail lightbox) ----------
    const zoomModal = document.getElementById('modal-product-zoom');
    const zoomImg = document.getElementById('product-zoom-img');
    const zoomCaption = document.getElementById('product-zoom-caption');

    document.querySelectorAll('[data-zoom-src]').forEach((trigger) => {
        trigger.addEventListener('click', () => {
            if (!zoomModal || !zoomImg) return;
            zoomImg.src = trigger.dataset.zoomSrc;
            zoomImg.alt = trigger.dataset.zoomAlt || '';
            if (zoomCaption) zoomCaption.textContent = trigger.dataset.zoomCaption || '';
            openModal(zoomModal);
        });
    });
    // Close on backdrop / ✕ clicks — clicks inside the figure (image, caption) stay open.
    zoomModal?.addEventListener('click', (e) => {
        if (e.target.closest('figure')) return;
        closeModal(zoomModal);
    });

    document.querySelectorAll('[data-open-login]').forEach((b) => b.addEventListener('click', (e) => { e.preventDefault(); window.openLogin(); }));
    document.querySelectorAll('[data-open-register]').forEach((b) => b.addEventListener('click', (e) => { e.preventDefault(); window.openRegister(); }));
    document.querySelectorAll('[data-close-modal]').forEach((b) => b.addEventListener('click', () => window.closeAuthModals()));
    [loginModal, registerModal, cartConflictModal].forEach((m) => {
        m?.addEventListener('click', (e) => {
            if (e.target === m) window.closeAuthModals();
        });
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            window.closeAuthModals();
            closeModal(zoomModal);
            closeMobileMenu();
        }
    });
    // Server flash for cross-store cart conflict — open once after redirect.
    if (cartConflictModal?.hasAttribute('data-auto-open')) {
        openModal(cartConflictModal);
    }

    // ---------- Password toggles ----------
    document.querySelectorAll('[data-toggle-password]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const input = document.querySelector(btn.dataset.togglePassword);
            if (!input) return;
            input.type = input.type === 'password' ? 'text' : 'password';
            btn.querySelector('.eye-open')?.classList.toggle('hidden');
            btn.querySelector('.eye-closed')?.classList.toggle('hidden');
        });
    });

    // ---------- Thousand-separator numeric inputs ----------
    // Digits only, grouped with "." (id-ID style, matching number_format($n, 0, ',', '.')).
    // These are type="text" inputs, so there is no spinner and the scroll wheel
    // never changes the value. Raw digits are restored before the form posts.
    const toDigits = (s) => (s || '').replace(/\D/g, '');
    const groupDigits = (digits) =>
        digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    const digitsToCaret = (text, caret) => toDigits(text.slice(0, caret)).length;
    const caretForDigits = (text, count) => {
        let pos = 0;
        let seen = 0;
        while (pos < text.length && seen < count) {
            if (text[pos] >= '0' && text[pos] <= '9') seen++;
            pos++;
        }
        return pos;
    };

    document.querySelectorAll('[data-numeric]').forEach((input) => {
        const max = input.dataset.max ? Number(input.dataset.max) : Infinity;

        const reformat = () => {
            const raw = input.value;
            const caret = input.selectionStart ?? raw.length;
            const count = digitsToCaret(raw, caret);
            const hadFocus = document.activeElement === input;

            let digits = toDigits(raw).replace(/^0+(?=\d)/, '');
            if (max !== Infinity && digits && Number(digits) > max) digits = String(max);
            input.value = groupDigits(digits);

            if (hadFocus) {
                const pos = caretForDigits(input.value, count);
                input.setSelectionRange(pos, pos);
            }
        };

        // Ignore keys that aren't digits or editing keys.
        input.addEventListener('beforeinput', (e) => {
            if (e.inputType && e.inputType.startsWith('insert') && !/^insertText$|^insertFromPaste$/.test(e.inputType)) {
                e.preventDefault();
            }
        });
        input.addEventListener('input', reformat);

        // Format whatever is prefilled (old() or the model value).
        reformat();

        // Post raw digits so server-side integer validation still passes.
        input.form?.addEventListener('submit', () => {
            input.value = toDigits(input.value);
        });
    });

    // ---------- Location pill shuffle (pauses when tab hidden) ----------
    const locations = ['Tebet, Jaksel', 'Sleman, Jogja', 'Buah Batu, Bandung', 'Denpasar, Bali', 'Tembalang, Semarang'];
    const locEl = document.getElementById('hero-location');
    if (locEl && !prefersReduced) {
        let locIdx = 0;
        setInterval(() => {
            if (document.hidden) return;
            locIdx = (locIdx + 1) % locations.length;
            locEl.style.opacity = '0';
            setTimeout(() => {
                if (document.hidden) return;
                locEl.textContent = locations[locIdx];
                locEl.style.opacity = '1';
            }, 250);
        }, 3200);
    }

    // ---------- FAQ accordion ----------
    document.querySelectorAll('[data-faq]').forEach((item) => {
        const btn = item.querySelector('[data-faq-btn]');
        const panel = item.querySelector('[data-faq-panel]');
        btn?.addEventListener('click', () => {
            const isOpen = item.classList.contains('faq-open');
            document.querySelectorAll('[data-faq].faq-open').forEach((o) => {
                o.classList.remove('faq-open');
                o.querySelector('[data-faq-panel]').style.maxHeight = '';
                o.querySelector('[data-faq-chevron]')?.classList.remove('rotate-180');
            });
            if (!isOpen) {
                item.classList.add('faq-open');
                panel.style.maxHeight = panel.scrollHeight + 'px';
                item.querySelector('[data-faq-chevron]')?.classList.add('rotate-180');
            }
        });
    });
});
