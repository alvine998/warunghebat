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

    // ---------- Nearby search (rAF-debounced) ----------
    const searchInput = document.getElementById('hero-search');
    const searchHint = document.getElementById('search-hint');
    const storeCards = Array.from(document.querySelectorAll('[data-store-name]')).map((card) => ({
        card,
        name: (card.dataset.storeName || '').toLowerCase(),
        cat: (card.dataset.storeCat || '').toLowerCase(),
    }));
    let searchQueued = false;
    searchInput?.addEventListener('input', (e) => {
        if (searchQueued) return;
        searchQueued = true;
        requestAnimationFrame(() => {
            searchQueued = false;
            const raw = e.target.value;
            const q = raw.toLowerCase().trim();
            let visible = 0;
            for (const { card, name, cat } of storeCards) {
                const match = !q || name.includes(q) || cat.includes(q);
                card.style.display = match ? '' : 'none';
                if (match) visible++;
            }
            if (searchHint) {
                searchHint.textContent = q
                    ? `${visible} warung ditemukan untuk "${raw}" di dekatmu`
                    : 'Coba ketik "gorengan", "kopi", atau "sembako"...';
            }
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
