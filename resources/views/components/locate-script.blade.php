@push('scripts')
<script>
(function () {
    var btn = document.getElementById('locate-btn');
    var status = document.getElementById('locate-status');
    if (!btn || !navigator.geolocation) { if (btn && !navigator.geolocation) btn.style.display = 'none'; return; }
    function say(msg) { if (status) { status.classList.remove('hidden'); status.textContent = msg; } }
    btn.addEventListener('click', function () {
        btn.disabled = true;
        say('⏳ Mencari lokasimu...');
        navigator.geolocation.getCurrentPosition(function (pos) {
            var url = new URL(window.location.href);
            url.searchParams.set('lat', pos.coords.latitude.toFixed(6));
            url.searchParams.set('lng', pos.coords.longitude.toFixed(6));
            window.location.href = url.toString().split('#')[0] + @json($locateHash ?? '');
        }, function () {
            btn.disabled = false;
            say('Tidak bisa mendapatkan lokasimu. Pastikan izin lokasi diaktifkan.');
        }, { enableHighAccuracy: true, timeout: 8000 });
    });
})();
</script>
@endpush
