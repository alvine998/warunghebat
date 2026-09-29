{{-- Camera capture via getUserMedia (desktop webcam + phone front/rear camera).
     Include once per page, then call:
     window.openCameraCapture(async () => { ... }) -> File (JPEG) or null.
     Caller falls back to <input type="file" capture> when this returns null. --}}
<div id="camera-capture-modal" class="hidden fixed inset-0 z-[80] items-center justify-center p-4">
    <div id="camera-capture-backdrop" class="absolute inset-0 bg-ink-900/70"></div>
    <div class="relative w-full max-w-md rounded-[24px] bg-white p-5 shadow-2xl">
        <div class="flex items-center justify-between gap-2">
            <p class="font-extrabold">Ambil foto</p>
            <button type="button" id="camera-capture-close" class="w-9 h-9 grid place-items-center rounded-full border border-ink-900/15 font-black hover:bg-ink-900 hover:text-white transition" aria-label="Tutup kamera">✕</button>
        </div>
        <div class="mt-3 overflow-hidden rounded-2xl bg-ink-900">
            <video id="camera-capture-video" class="hidden w-full aspect-[3/4] object-cover" playsinline muted></video>
        </div>
        <p id="camera-capture-status" class="mt-3 rounded-2xl bg-cream-100 px-4 py-3 text-[13px] font-bold text-ink-500" aria-live="polite">Menyiapkan kamera…</p>
        <div class="mt-3 grid grid-cols-2 gap-2">
            <button type="button" id="camera-capture-shoot" class="rounded-2xl bg-ink-900 px-4 py-3 text-[14px] font-extrabold text-white hover:bg-brand-600 transition disabled:opacity-50" disabled>📸 Ambil</button>
            <button type="button" id="camera-capture-cancel" class="rounded-2xl border border-ink-900/15 bg-white px-4 py-3 text-[14px] font-extrabold text-ink-900 hover:border-brand-500 transition">Batal</button>
        </div>
    </div>
</div>

<script>
(function () {
    if (window.openCameraCapture) return;

    const modal = document.getElementById('camera-capture-modal');
    const video = document.getElementById('camera-capture-video');
    const statusEl = document.getElementById('camera-capture-status');
    const shootBtn = document.getElementById('camera-capture-shoot');
    if (!modal || !video || !statusEl || !shootBtn) return;

    let stream = null;
    let resolveOpen = null;

    function stopStream() {
        if (stream) {
            stream.getTracks().forEach((t) => t.stop());
            stream = null;
        }
        video.srcObject = null;
    }

    function closeModal() {
        stopStream();
        video.classList.add('hidden');
        shootBtn.disabled = true;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
        if (resolveOpen) {
            resolveOpen(null);
            resolveOpen = null;
        }
    }

    function takePhoto() {
        if (!stream || !video.videoWidth) return;
        const canvas = document.createElement('canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d', { alpha: false }).drawImage(video, 0, 0);
        canvas.toBlob((blob) => {
            const file = blob ? new File([blob], `foto-produk-${Date.now()}.jpg`, { type: 'image/jpeg' }) : null;
            closeModal();
            if (resolveOpen) {
                resolveOpen(file);
                resolveOpen = null;
            }
        }, 'image/jpeg', 0.92);
    }

    window.openCameraCapture = function () {
        if (!window.isSecureContext || !navigator.mediaDevices?.getUserMedia) {
            return Promise.resolve(null);
        }
        if (resolveOpen) resolveOpen(null);

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
        statusEl.textContent = 'Menyiapkan kamera…';

        const result = new Promise((resolve) => { resolveOpen = resolve; });

        navigator.mediaDevices.getUserMedia({ video: true, audio: false })
            .then(async (s) => {
                stream = s;
                video.srcObject = s;
                await video.play();
                video.classList.remove('hidden');
                shootBtn.disabled = false;
                statusEl.textContent = 'Posisikan produk di dalam bingkai, lalu tekan Ambil.';
            })
            .catch(() => {
                statusEl.textContent = 'Kamera tidak bisa dibuka. Periksa izin kamera browser.';
                stopStream();
            });

        return result;
    };

    shootBtn.addEventListener('click', takePhoto);
    document.getElementById('camera-capture-close')?.addEventListener('click', closeModal);
    document.getElementById('camera-capture-backdrop')?.addEventListener('click', closeModal);
    document.getElementById('camera-capture-cancel')?.addEventListener('click', closeModal);
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
    });
})();
</script>
