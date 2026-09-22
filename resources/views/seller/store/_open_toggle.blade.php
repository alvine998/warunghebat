{{-- Warung open/closed toggle for seller topbars. Expects $store. --}}
<form method="POST" action="{{ route('seller.store.toggle') }}" class="inline-flex">
    @csrf
    @method('PATCH')
    <button type="submit" title="{{ $store->is_open ? 'Tutup warung sementara' : 'Buka warung' }}"
        class="inline-flex items-center gap-2 rounded-full border pl-1.5 pr-3.5 py-1.5 text-[12px] font-extrabold transition {{ $store->is_open ? 'bg-leaf-100 border-leaf-500/30 text-leaf-700 hover:bg-leaf-600 hover:text-white' : 'bg-white border-ink-900/15 text-ink-500 hover:border-ink-900 hover:text-ink-900' }}">
        <span class="relative inline-flex w-9 h-5 rounded-full transition {{ $store->is_open ? 'bg-leaf-500' : 'bg-ink-900/20' }}">
            <span class="absolute top-0.5 w-4 h-4 rounded-full bg-white shadow transition-all {{ $store->is_open ? 'left-[18px]' : 'left-0.5' }}"></span>
        </span>
        {{ $store->is_open ? '● Buka' : '○ Tutup' }}
    </button>
</form>
