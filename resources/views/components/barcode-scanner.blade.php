{{-- Reusable barcode scanner modal. Include once per page, then call:
     window.openBarcodeScanner((code) => { ... }) from any button.
     Uses the native BarcodeDetector API when available, otherwise falls
     back to the html5-qrcode library (loaded on demand from CDN). --}}
<div id="barcode-scanner-modal" class="hidden fixed inset-0 z-[80] items-center justify-center p-4">
    <div id="barcode-scanner-backdrop" class="absolute inset-0 bg-ink-900/70"></div>
    <div class="relative w-full max-w-md rounded-[24px] bg-white p-5 shadow-2xl">
        <div class="flex items-center justify-between gap-2">
            <p class="font-extrabold">Scan barcode</p>
            <button type="button" id="barcode-scanner-close" class="w-9 h-9 grid place-items-center rounded-full border border-ink-900/15 font-black hover:bg-ink-900 hover:text-white transition" aria-label="Tutup pemindai">✕</button>
        </div>
        <p class="mt-1 text-[13px] font-medium text-ink-500">Arahkan kamera ke barcode produk.</p>
        <div class="mt-3 overflow-hidden rounded-2xl bg-ink-900">
            <video id="barcode-scanner-video" class="hidden w-full aspect-[4/3] object-cover" playsinline muted></video>
            <div id="barcode-scanner-reader" class="hidden w-full"></div>
        </div>
        <p id="barcode-scanner-status" class="mt-3 rounded-2xl bg-cream-100 px-4 py-3 text-[13px] font-bold text-ink-500" aria-live="polite">Menyiapkan kamera…</p>
    </div>
</div>

<script>
(function () {
    if (window.openBarcodeScanner) return;

    const modal = document.getElementById('barcode-scanner-modal');
    const video = document.getElementById('barcode-scanner-video');
    const readerEl = document.getElementById('barcode-scanner-reader');
    const statusEl = document.getElementById('barcode-scanner-status');
    if (!modal || !video || !statusEl) return;

    let stream = null;
    let rafId = 0;
    let detector = null;
    let html5Qr = null;
    let callback = null;
    let done = false;

    function setStatus(msg) {
        statusEl.textContent = msg;
    }

    function show(el) {
        el.classList.remove('hidden');
    }

    function hide(el) {
        el.classList.add('hidden');
    }

    function finish(code) {
        if (done) return;
        done = true;
        const value = String(code || '').trim();
        closeScanner();
        if (value && typeof callback === 'function') callback(value);
    }

    function stopTracks() {
        if (stream) {
            stream.getTracks().forEach((t) => t.stop());
            stream = null;
        }
        cancelAnimationFrame(rafId);
        video.srcObject = null;
    }

    async function stopHtml5Qr() {
        if (html5Qr) {
            try {
                await html5Qr.stop();
                await html5Qr.clear();
            } catch (e) { /* already stopped */ }
            html5Qr = null;
        }
        if (readerEl) readerEl.innerHTML = '';
    }

    function closeScanner() {
        stopTracks();
        stopHtml5Qr();
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
    }

    function loadScript(src) {
        return new Promise((resolve, reject) => {
            if (document.querySelector(`script[src="${src}"]`)) return resolve();
            const s = document.createElement('script');
            s.src = src;
            s.async = true;
            s.onload = () => resolve();
            s.onerror = () => reject(new Error('gagal memuat pemindai'));
            document.head.appendChild(s);
        });
    }

    async function startNative() {
        const formats = ['ean_13', 'ean_8', 'upc_a', 'upc_e', 'code_128', 'code_39', 'itf', 'qr_code'];
        try {
            detector = new BarcodeDetector({ formats });
        } catch (e) {
            detector = new BarcodeDetector();
        }
        stream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'environment' },
            audio: false,
        });
        video.srcObject = stream;
        await video.play();
        show(video);
        setStatus('Arahkan kamera ke barcode…');

        const tick = async () => {
            if (done || !stream) return;
            try {
                const codes = await detector.detect(video);
                if (codes && codes.length) {
                    finish(codes[0].rawValue);
                    return;
                }
            } catch (e) { /* keep scanning */ }
            rafId = requestAnimationFrame(() => setTimeout(tick, 250));
        };
        tick();
    }

    async function startFallback() {
        await loadScript('https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js');
        show(readerEl);
        setStatus('Arahkan kamera ke barcode…');
        html5Qr = new Html5Qrcode('barcode-scanner-reader');
        await html5Qr.start(
            { facingMode: 'environment' },
            { fps: 10, qrbox: { width: 250, height: 150 } },
            (decoded) => finish(decoded),
            () => { /* no match in this frame */ }
        );
    }

    window.openBarcodeScanner = async function (onResult) {
        if (typeof onResult === 'function') callback = onResult;
        done = false;
        hide(video);
        hide(readerEl);
        if (readerEl) readerEl.innerHTML = '';
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
        setStatus('Menyiapkan kamera…');

        if (!window.isSecureContext || !navigator.mediaDevices?.getUserMedia) {
            setStatus('Kamera butuh koneksi aman (HTTPS) dan izin browser. Ketik barcode manual ya.');
            return;
        }

        try {
            if ('BarcodeDetector' in window) {
                await startNative();
            } else {
                await startFallback();
            }
        } catch (e) {
            // Native failed (no permission / no camera) — try the fallback library once.
            if (!html5Qr && !stream) {
                try {
                    await startFallback();
                    return;
                } catch (e2) { /* show help below */ }
            }
            stopTracks();
            setStatus('Kamera tidak bisa dibuka. Pastikan izin kamera diizinkan, lalu ketik barcode manual.');
        }
    };

    window.closeBarcodeScanner = closeScanner;

    document.getElementById('barcode-scanner-close')?.addEventListener('click', closeScanner);
    document.getElementById('barcode-scanner-backdrop')?.addEventListener('click', closeScanner);
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeScanner();
    });
})();
</script>
