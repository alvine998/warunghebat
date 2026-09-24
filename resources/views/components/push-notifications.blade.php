{{-- Push notification opt-in (Firebase Cloud Messaging). Rendered for signed-in
      users only, and only when the FIREBASE_* keys are configured — otherwise
      the browser permission is never requested and nothing is shown. --}}
@auth
@if(! empty($firebaseConfig))
<div id="push-notify" data-firebase-config='@json($firebaseConfig)' class="hidden fixed z-40 inset-x-3 bottom-36 sm:inset-x-auto sm:right-6 sm:bottom-28 sm:w-[380px] rounded-3xl bg-ink-900 text-white border border-white/10 shadow-2xl p-4" role="dialog" aria-live="polite" aria-label="Aktifkan notifikasi">
    <div class="flex items-center gap-3">
        <span class="w-11 h-11 rounded-2xl bg-brand-500 grid place-items-center text-xl shrink-0" aria-hidden="true">🔔</span>
        <div class="flex-1 min-w-0">
            <p class="text-[14px] font-extrabold">Aktifkan Notifikasi?</p>
            <p class="text-[12px] font-semibold text-white/60">Info pesanan & promo penting, langsung di HP-mu.</p>
        </div>
        <button type="button" data-push-dismiss class="shrink-0 text-white/50 hover:text-white font-black px-2" aria-label="Tutup">✕</button>
    </div>
    <p data-push-status class="hidden mt-2 text-[12px] font-semibold text-white/70"></p>
    <div class="mt-3 grid grid-cols-[1fr_auto] gap-2">
        <button type="button" data-push-enable class="py-3 px-4 rounded-2xl bg-brand-500 text-sm font-extrabold hover:bg-brand-600 active:scale-[.98] transition">Aktifkan</button>
        <button type="button" data-push-disable class="hidden py-3 px-4 rounded-2xl bg-white/10 text-sm font-bold hover:bg-white/20 transition">Matikan</button>
        <button type="button" data-push-later class="py-3 px-4 rounded-2xl bg-white/10 text-sm font-bold hover:bg-white/20 transition">Nanti</button>
    </div>
</div>
@endif
@endauth
