{{-- Global toast notifications: session success/error + validation errors. --}}
@if (session('success') || session('error') || $errors->any())
<div id="toasts" class="fixed z-[100] inset-x-4 bottom-20 sm:inset-x-auto sm:right-6 sm:bottom-6 sm:w-[380px] grid gap-2.5">
    @if (session('success'))
    <div data-toast class="toast-in flex items-start gap-3 rounded-2xl bg-ink-900 text-white border border-white/10 shadow-2xl p-4">
        <span class="w-8 h-8 shrink-0 rounded-xl bg-leaf-500 grid place-items-center text-base">✓</span>
        <p class="flex-1 text-[13px] font-bold leading-snug pt-1">{{ session('success') }}</p>
        <button type="button" data-toast-close class="shrink-0 text-white/50 hover:text-white font-black px-1" aria-label="Tutup">✕</button>
    </div>
    @endif
    @if (session('error'))
    <div data-toast class="toast-in flex items-start gap-3 rounded-2xl bg-ink-900 text-white border border-red-400/40 shadow-2xl p-4">
        <span class="w-8 h-8 shrink-0 rounded-xl bg-red-500 grid place-items-center text-base">!</span>
        <p class="flex-1 text-[13px] font-bold leading-snug pt-1">{{ session('error') }}</p>
        <button type="button" data-toast-close class="shrink-0 text-white/50 hover:text-white font-black px-1" aria-label="Tutup">✕</button>
    </div>
    @endif
    @if ($errors->any())
    <div data-toast class="toast-in flex items-start gap-3 rounded-2xl bg-ink-900 text-white border border-red-400/40 shadow-2xl p-4">
        <span class="w-8 h-8 shrink-0 rounded-xl bg-red-500 grid place-items-center text-base">!</span>
        <div class="flex-1 min-w-0 pt-0.5">
            <p class="text-[13px] font-extrabold">⚠️ {{ $errors->count() === 1 ? 'Ada 1 kesalahan:' : 'Ada ' . $errors->count() . ' kesalahan:' }}</p>
            <ul class="mt-1 grid gap-0.5 text-[13px] font-semibold text-white/80">
                @foreach ($errors->all() as $i => $e)
                    @if ($i < 5)
                    <li class="leading-snug">• {{ $e }}</li>
                    @endif
                @endforeach
                @if ($errors->count() > 5)
                <li class="text-white/50">• +{{ $errors->count() - 5 }} lainnya…</li>
                @endif
            </ul>
        </div>
        <button type="button" data-toast-close class="shrink-0 text-white/50 hover:text-white font-black px-1" aria-label="Tutup">✕</button>
    </div>
    @endif
</div>
<style>
@keyframes toast-in { from { opacity: 0; transform: translateY(12px) scale(.98); } to { opacity: 1; transform: none; } }
.toast-in { animation: toast-in .3s cubic-bezier(.22,1,.36,1); }
.toast-out { opacity: 0; transform: translateY(8px); transition: all .25s ease; }
</style>
<script>
(function () {
    var box = document.getElementById('toasts');
    if (!box) return;
    box.querySelectorAll('[data-toast]').forEach(function (el) {
        var t = setTimeout(function () { dismiss(el); }, 6000);
        el.querySelector('[data-toast-close]').addEventListener('click', function () { clearTimeout(t); dismiss(el); });
    });
    function dismiss(el) {
        el.classList.add('toast-out');
        setTimeout(function () { el.remove(); var b = document.getElementById('toasts'); if (b && !b.children.length) b.remove(); }, 260);
    }
})();
</script>
@endif
