{{-- Shown after a cart conflict: the buyer tried to add a product from another warung. --}}
@php($cartConflict = session('cart_conflict'))
@if(is_array($cartConflict) && ! empty($cartConflict['product_id']))
<div id="modal-cart-conflict" class="hidden fixed inset-0 z-[70] modal-backdrop" data-auto-open>
    <div class="absolute inset-0 bg-ink-900/60 backdrop-blur-sm" data-close-modal></div>
    <div class="absolute inset-0 overflow-y-auto" data-lenis-prevent>
        <div class="min-h-full flex items-end sm:items-center justify-center p-0 sm:p-6">
            <div class="modal-card relative w-full sm:max-w-md bg-cream-50 rounded-t-[28px] sm:rounded-[28px] p-6 sm:p-8 shadow-2xl">
                <button type="button" data-close-modal class="absolute top-4 right-4 w-9 h-9 grid place-items-center rounded-full bg-ink-900/5 hover:bg-ink-900 hover:text-white transition" aria-label="Tutup">✕</button>
                <div class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 text-amber-800 text-[11px] font-extrabold px-3 py-1.5 mb-4">1 transaksi • 1 warung</div>
                <p class="font-extrabold text-xl tracking-tight leading-tight">Ganti isi keranjang?</p>
                <p class="text-sm text-ink-500 font-medium mt-2 leading-relaxed">
                    Keranjang kamu sekarang berisi produk dari <strong class="text-ink-900">{{ $cartConflict['current_store_name'] }}</strong>.
                    Kalau lanjut tambah dari <strong class="text-ink-900">{{ $cartConflict['new_store_name'] }}</strong>, sisa isi keranjang akan diganti.
                </p>
                <form method="POST" action="{{ route('cart.store') }}" class="mt-5 grid gap-2.5">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $cartConflict['product_id'] }}">
                    <input type="hidden" name="force" value="1">
                    <button type="submit" class="w-full py-3.5 rounded-2xl bg-brand-500 text-white font-extrabold text-[15px] hover:bg-brand-600 active:scale-[.99] transition shadow-lg shadow-brand-500/30">Ya, ganti keranjang →</button>
                    <button type="button" data-close-modal class="w-full py-3.5 rounded-2xl border-2 border-ink-900/10 bg-white font-extrabold text-[15px] hover:border-ink-900 transition">Batal</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
