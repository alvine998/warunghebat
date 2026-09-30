<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Struk #{{ $transaction->id }} — {{ $store->name }}</title>
    <style>
        /* Thermal-friendly: 58mm paper, no app chrome, no external assets. */
        body { margin: 0; padding: 16px; background: #f4f4f5; font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 12px; line-height: 1.5; color: #000; }
        .receipt { width: 58mm; margin: 0 auto; padding: 6mm 4mm; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,.2); }
        .center { text-align: center; }
        .right { text-align: right; }
        .bold { font-weight: 700; }
        .row { display: flex; justify-content: space-between; gap: 8px; }
        hr { border: 0; border-top: 1px dashed #000; margin: 6px 0; }
        .toolbar { display: flex; flex-wrap: wrap; gap: 8px; justify-content: center; margin-bottom: 16px; }
        .toolbar a, .toolbar button { min-height: 40px; display: inline-flex; align-items: center; padding: 0 16px; border: 1px solid #000; border-radius: 999px; background: #000; color: #fff; font: inherit; font-weight: 700; text-decoration: none; cursor: pointer; }
        .toolbar .ghost { background: #fff; color: #000; }
        .note { max-width: 58mm; margin: 12px auto 0; text-align: center; font-size: 12px; }
        @page { margin: 4mm; }
        @media print {
            body { padding: 0; background: #fff; }
            .no-print { display: none !important; }
            .receipt { width: auto; margin: 0; padding: 0; box-shadow: none; }
            .note { display: none; }
        }
    </style>
</head>
<body>
    <div class="toolbar no-print">
        <button type="button" onclick="window.print()">Cetak lagi</button>
        <a class="ghost" href="{{ route('seller.transactions.create') }}">Catat transaksi baru</a>
        <a class="ghost" href="{{ route('seller.transactions.index') }}">Selesai</a>
    </div>

    @if(session('success'))
        <p class="note no-print">{{ session('success') }}</p>
    @endif

    <div class="receipt">
        <p class="center bold" style="font-size: 15px; margin: 0;">{{ $store->name }}</p>
        @if($store->address)
            <p class="center" style="margin: 0;">{{ $store->address }}</p>
        @endif
        @if($store->phone)
            <p class="center" style="margin: 0;">{{ $store->phone }}</p>
        @endif

        <hr>
        <p class="center bold" style="margin: 0;">STRUK TRANSAKSI</p>
        <div class="row"><span>No.</span><span>#{{ $transaction->id }}</span></div>
        <div class="row"><span>Tanggal</span><span>{{ $transaction->created_at->format('d/m/Y H:i') }}</span></div>
        <div class="row"><span>Pembeli</span><span>{{ $transaction->buyer_name ?: 'Pembeli langsung' }}</span></div>
        @if($transaction->buyer_phone)
            <div class="row"><span>No. HP</span><span>{{ $transaction->buyer_phone }}</span></div>
        @endif

        <hr>
        @foreach($transaction->items as $item)
            <div class="row"><span class="bold">{{ $item->name }}</span><span class="right">{{ number_format($item->subtotal, 0, ',', '.') }}</span></div>
            <div class="row"><span>&nbsp;&nbsp;{{ number_format($item->qty, 0, ',', '.') }} × {{ number_format($item->price, 0, ',', '.') }}</span></div>
        @endforeach

        <hr>
        <div class="row bold" style="font-size: 14px;"><span>TOTAL</span><span>Rp {{ number_format($transaction->total, 0, ',', '.') }}</span></div>

        <hr>
        <p class="center" style="margin: 0;">Terima kasih sudah belanja!</p>
        <p class="center" style="margin: 0;">Barang yang sudah dibeli tidak dapat dikembalikan.</p>
    </div>

    <p class="note no-print">Tekan Ctrl/Cmd + P bila dialog cetak tidak muncul.</p>

    <script>
        window.addEventListener('load', () => window.print());
    </script>
</body>
</html>
