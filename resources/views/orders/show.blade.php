@extends('layouts.app')

@section('title', $order->code().' — Warung Hebat')

@section('content')
@php
    $tone = match ($order->status) {
        'completed', 'paid' => 'bg-leaf-100 text-leaf-700',
        'cancelled' => 'bg-red-100 text-red-700',
        'waiting_verification' => 'bg-amber-100 text-amber-800',
        default => 'bg-brand-100 text-brand-700',
    };
@endphp

<section class="pt-24 sm:pt-28 pb-10 sm:pb-14 max-w-2xl mx-auto px-4 sm:px-6">
    <a href="{{ route('orders.index') }}" class="inline-flex items-center gap-1.5 min-h-10 text-[13px] font-extrabold text-ink-500 hover:text-ink-900">← Semua pesanan</a>

    <div class="mt-3 flex items-start justify-between gap-3">
        <div class="min-w-0">
            <h1 class="font-black tracking-tight text-[26px] sm:text-3xl">{{ $order->code() }}</h1>
            <p class="text-[13px] font-semibold text-ink-500 mt-1">
                <a href="{{ $order->store ? route('store.show', $order->store) : route('home').'#warung' }}" class="font-extrabold text-ink-900 underline decoration-ink-900/20">{{ $order->warung_name }}</a>
                • {{ $order->created_at->format('d M Y, H:i') }}
            </p>
        </div>
        <span class="shrink-0 text-[11px] font-extrabold rounded-full px-3 py-1.5 {{ $tone }}">{{ $order->statusLabel() }}</span>
    </div>

    {{-- ===== STATUS BANNER ===== --}}
    <div class="mt-4 rounded-[24px] p-4 sm:p-5 @if($order->status === 'paid' || $order->status === 'completed') bg-leaf-50 border border-leaf-500/20 @elseif($order->status === 'cancelled') bg-red-50 border border-red-200 @elseif($order->status === 'waiting_verification') bg-amber-50 border border-amber-200 @else bg-brand-50 border border-brand-200 @endif">
        @if($order->status === 'pending_payment')
            <p class="font-extrabold text-brand-700">💳 Menunggu pembayaran</p>
            <p class="mt-1 text-[13px] font-medium text-ink-700">Transfer sebesar <strong>Rp {{ number_format($order->total, 0, ',', '.') }}</strong> ke rekening <strong>Warung Hebat</strong> di bawah, lalu unggah bukti transfernya.</p>
        @elseif($order->status === 'waiting_verification')
            <p class="font-extrabold text-amber-900">⏳ Bukti transfer sedang diverifikasi</p>
            <p class="mt-1 text-[13px] font-medium text-ink-700">Admin akan memverifikasi pembayaranmu. Kamu akan melihat statusnya berubah di halaman ini.</p>
        @elseif($order->status === 'paid')
            <p class="font-extrabold text-leaf-700">✅ Pembayaran terverifikasi</p>
            <p class="mt-1 text-[13px] font-medium text-ink-700">Dana ditahan Warung Hebat dan akan diteruskan ke warung setelah pesanan selesai.</p>
        @elseif($order->status === 'completed')
            <p class="font-extrabold text-leaf-700">🎉 Pesanan selesai</p>
            <p class="mt-1 text-[13px] font-medium text-ink-700">Terima kasih! Dana sudah diteruskan ke {{ $order->warung_name }}.</p>
        @else
            <p class="font-extrabold text-red-700">Pesanan dibatalkan</p>
            <p class="mt-1 text-[13px] font-medium text-ink-700">Pesanan ini dibatalkan dan stok dikembalikan ke warung.</p>
        @endif
    </div>

    {{-- ===== RATING: one per completed order ===== --}}
    @if($order->isCompleted() && $order->store)
        <div class="mt-3 rounded-[28px] bg-white border border-ink-900/10 p-4 sm:p-5">
            @if($order->rating)
                <p class="font-extrabold">Penilaianmu</p>
                <p class="mt-1.5 text-[22px] leading-none tracking-wider text-amber-400" aria-label="Penilaian {{ $order->rating->rating }} dari 5">
                    @for($i = 1; $i <= 5; $i++)
                        <span class="{{ $i <= $order->rating->rating ? '' : 'text-ink-900/15' }}">★</span>
                    @endfor
                </p>
                @if($order->rating->comment)
                    <p class="mt-2 text-[13px] font-medium text-ink-700">{{ $order->rating->comment }}</p>
                @endif
                <p class="mt-1.5 text-[12px] font-semibold text-ink-500">Dikirim {{ $order->rating->created_at->diffForHumans() }}</p>
            @elseif($order->canBeRated())
                <p class="font-extrabold">Beri penilaian warung</p>
                <p class="mt-0.5 text-[12px] font-semibold text-ink-500">1 pesanan = 1 penilaian. Nilai pengalamanmu di {{ $order->warung_name }}.</p>

                <form method="POST" action="{{ route('orders.rate', $order) }}" class="mt-3">
                    @csrf
                    <fieldset>
                        <legend class="sr-only">Bintang 1 sampai 5</legend>
                        <div class="flex items-center gap-1">
                            @for($star = 1; $star <= 5; $star++)
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="rating" value="{{ $star }}" required class="peer sr-only">
                                    <span class="inline-flex items-center justify-center w-11 h-11 text-[28px] leading-none text-ink-900/15 peer-checked:text-amber-400 peer-focus-visible:ring-4 peer-focus-visible:ring-brand-500/30 transition select-none" aria-label="{{ $star }} bintang">★</span>
                                </label>
                            @endfor
                        </div>
                    </fieldset>

                    <label for="rating-comment" class="sr-only">Komentar (opsional)</label>
                    <textarea id="rating-comment" name="comment" rows="2" maxlength="500" placeholder="Komentar (opsional)..." class="mt-2 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[13px] font-semibold placeholder:text-ink-500/60 outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition resize-none"></textarea>
                    @error('rating')
                        <p class="mt-1 text-[12px] font-bold text-red-600">{{ $message }}</p>
                    @enderror
                    @error('comment')
                        <p class="mt-1 text-[12px] font-bold text-red-600">{{ $message }}</p>
                    @enderror

                    <button class="mt-2 w-full min-h-11 rounded-full bg-ink-900 text-white text-[13px] font-extrabold hover:bg-brand-500 active:scale-[.98] transition">Kirim penilaian</button>
                </form>
            @endif
        </div>
    @endif

    {{-- ===== PAYMENT: METHOD + PROOF ===== --}}
    @if($order->status === 'pending_payment')
        @if($latestPayment?->isRejected())
            <div class="mt-3 rounded-[24px] border border-red-200 bg-red-50 p-4">
                <p class="font-extrabold text-red-700">Bukti transfer sebelumnya ditolak</p>
                <p class="mt-1 text-[13px] font-medium text-ink-700">Alasan: {{ $latestPayment->rejection_reason }}</p>
                <p class="mt-1 text-[12px] font-semibold text-ink-500">Silakan unggah ulang bukti transfer yang benar.</p>
            </div>
        @endif

        <div class="mt-3 rounded-[28px] bg-white border border-ink-900/10 p-4 sm:p-6">
            @if($paymentMethods->isEmpty())
                <div class="rounded-2xl border border-dashed border-ink-900/15 p-6 text-center">
                    <p class="font-extrabold">Belum ada metode pembayaran</p>
                    <p class="text-[13px] font-semibold text-ink-500 mt-1">Hubungi admin Warung Hebat untuk menyelesaikan pembayaran pesanan ini.</p>
                </div>
            @else
                <form method="POST" action="{{ route('orders.proof', $order) }}" enctype="multipart/form-data">
                    @csrf

                    <p class="font-extrabold">1. Pilih tujuan transfer</p>
                    <div class="mt-2 grid gap-2">
                        @foreach($paymentMethods as $method)
                            <label class="block rounded-2xl border border-ink-900/10 p-3 cursor-pointer has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50 transition">
                                <div class="flex items-start gap-3">
                                    <input type="radio" name="payment_method_id" value="{{ $method->id }}" required @checked($loop->first) class="mt-1 accent-brand-500">
                                    <div class="flex-1 min-w-0">
                                        <p class="font-extrabold text-[14px]">{{ $method->name }}</p>
                                        <p class="text-[11px] font-bold text-ink-500">{{ $method->typeLabel() }}</p>

                                        @if($method->account_number)
                                            <p class="mt-1 font-black tracking-wide select-all">{{ $method->account_number }}</p>
                                        @endif
                                        @if($method->account_name)
                                            <p class="text-[12px] font-semibold text-ink-500">a/n {{ $method->account_name }}</p>
                                        @endif
                                        @if($method->instructions)
                                            <p class="mt-1.5 text-[12px] font-medium text-ink-700">{{ $method->instructions }}</p>
                                        @endif
                                        @if($method->image_path)
                                            <img src="{{ $method->image_url }}" alt="Kode {{ $method->name }}" class="mt-2 w-40 rounded-xl border border-ink-900/10">
                                        @endif
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>

                    <p class="mt-4 font-extrabold">2. Unggah bukti transfer</p>
                    <p class="text-[12px] font-semibold text-ink-500 mt-0.5">Nominal: <span class="font-black text-ink-900">Rp {{ number_format($order->total, 0, ',', '.') }}</span></p>

                    <label for="proof" class="sr-only">Bukti transfer</label>
                    <input id="proof" name="proof" type="file" accept="image/png,image/jpeg,image/webp" required class="mt-2 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[13px] font-semibold file:mr-3 file:rounded-full file:border-0 file:bg-ink-900 file:px-4 file:py-2 file:text-[12px] file:font-extrabold file:text-white outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
                    <p class="mt-1 text-[11px] font-semibold text-ink-500">JPG, PNG, atau WebP. Maksimal 2MB.</p>

                    <button class="mt-3 w-full min-h-12 rounded-full bg-brand-500 hover:bg-brand-600 text-white font-extrabold text-sm transition">Kirim bukti transfer</button>
                </form>
            @endif
        </div>
    @endif

    {{-- ===== LATEST PROOF ===== --}}
    @if($latestPayment?->proof_path)
        <div class="mt-3 rounded-[24px] bg-white border border-ink-900/10 p-4">
            <p class="font-extrabold text-[13px]">Bukti transfer terakhir</p>
            <div class="mt-2 flex items-center gap-3">
                <a href="{{ $latestPayment->proof_url }}" target="_blank" rel="noopener">
                    <img src="{{ $latestPayment->proof_url }}" alt="Bukti transfer" class="w-24 h-24 rounded-2xl object-cover border border-ink-900/10">
                </a>
                <div class="min-w-0 text-[12px] font-semibold text-ink-500">
                    <p>Metode: <span class="font-extrabold text-ink-900">{{ $latestPayment->paymentMethod?->name ?? '—' }}</span></p>
                    <p class="mt-0.5">Status: <span class="font-extrabold text-ink-900">{{ $latestPayment->statusLabel() }}</span></p>
                    <p class="mt-0.5">Dikirim {{ $latestPayment->created_at->diffForHumans() }}</p>
                </div>
            </div>
        </div>
    @endif

    {{-- ===== ITEMS ===== --}}
    <div class="mt-3 rounded-[28px] bg-white border border-ink-900/10 overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-ink-900/5">
            <p class="font-extrabold">Rincian pesanan</p>
        </div>
        <div class="divide-y divide-ink-900/5">
            @foreach($order->items as $item)
                <div class="p-4 flex items-center gap-3">
                    <div class="flex-1 min-w-0">
                        <p class="font-extrabold text-[14px] break-words">{{ $item->name }}</p>
                        <p class="text-[12px] font-semibold text-ink-500">{{ $item->qty }} × Rp {{ number_format($item->price, 0, ',', '.') }}</p>
                    </div>
                    <p class="font-black text-[14px] whitespace-nowrap">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</p>
                </div>
            @endforeach
        </div>
        <div class="p-4 sm:p-5 border-t border-ink-900/5 bg-cream-50/60">
            <div class="flex items-center justify-between gap-3">
                <p class="text-sm font-bold text-ink-500">Total dibayar</p>
                <p class="font-black text-xl whitespace-nowrap">Rp {{ number_format($order->total, 0, ',', '.') }}</p>
            </div>
            @if($order->commission_amount > 0)
                <p class="mt-1 text-[12px] font-semibold text-ink-500 text-right">Komisi platform Rp {{ number_format($order->commission_amount, 0, ',', '.') }}</p>
            @endif
        </div>
    </div>

    <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2">
        <a href="{{ $order->store ? route('store.show', $order->store) : route('home').'#warung' }}" class="text-center min-h-11 grid place-items-center rounded-full bg-cream-100 text-[13px] font-extrabold hover:bg-ink-900 hover:text-white transition">Kunjungi warung</a>
        <a href="{{ route('orders.index') }}" class="text-center min-h-11 grid place-items-center rounded-full border border-ink-900/15 text-[13px] font-extrabold hover:bg-ink-900 hover:text-white transition">Semua pesanan</a>
    </div>
</section>
@endsection
