@extends('layouts.admin')

@section('title', 'Pengaturan Platform — Backoffice Warung Hebat')

@php
    // One tab per group of settings. `fields` maps the tab back to the inputs it
    // owns, so a failed save can reopen the tab that needs attention.
    $tabs = [
        ['key' => 'keuangan', 'label' => 'Keuangan', 'hint' => 'Penarikan & komisi', 'icon' => 'wallet', 'fields' => ['withdrawal_min', 'withdrawal_max', 'commission_percent']],
        ['key' => 'kontak', 'label' => 'Kontak', 'hint' => 'CS, email & alamat', 'icon' => 'phone', 'fields' => ['cs_whatsapp', 'official_email', 'office_address']],
        ['key' => 'jam', 'label' => 'Jam Layanan', 'hint' => 'Hari & jam operasional', 'icon' => 'clock', 'fields' => ['operational_days', 'operational_hours', 'operational_note']],
        ['key' => 'sosial', 'label' => 'Media Sosial', 'hint' => 'Link di footer situs', 'icon' => 'globe', 'fields' => ['social_instagram', 'social_tiktok', 'social_x']],
    ];

    $activeTab = null;
    foreach ($tabs as $tab) {
        foreach ($tab['fields'] as $field) {
            if ($errors->has($field)) {
                $activeTab = $tab['key'];
                break 2;
            }
        }
    }
    $activeTab ??= $tabs[0]['key'];
@endphp

@section('content')
<style>
    /* Tabs are plain buttons; the selected state lives in CSS so JS only flips ARIA. */
    [data-tab] { transition: background-color .2s ease, border-color .2s ease, color .2s ease; }
    [data-tab][aria-selected="true"] { background-color: var(--color-ink-900); border-color: var(--color-ink-900); color: #fff; }
    [data-tab][aria-selected="true"] .tab-icon { background-color: rgba(255, 255, 255, .14); color: #fff; }
    [data-tab][aria-selected="true"] .tab-hint { color: rgba(255, 255, 255, .6); }
    [data-tab]:focus-visible { outline: 2px solid var(--color-brand-500); outline-offset: 2px; }
</style>

<p class="flex items-center gap-2 text-[11px] font-extrabold tracking-[0.2em] text-brand-600"><x-icon name="bolt" class="w-4 h-4" /> PENGATURAN</p>
<h1 class="font-black tracking-tight text-3xl mt-1">Pengaturan platform</h1>
<p class="text-sm font-medium text-ink-500">Atur batas penarikan, komisi, info kontak, dan media sosial Warung Hebat di sini.</p>

<form method="POST" action="{{ route('admin.settings.update') }}" class="mt-5 max-w-2xl grid gap-3.5">
    @csrf
    @method('PUT')

    <div role="tablist" aria-label="Bagian pengaturan" data-active="{{ $activeTab }}" class="grid grid-cols-2 sm:grid-cols-4 gap-2">
        @foreach($tabs as $tab)
        <button
            type="button"
            role="tab"
            id="tab-{{ $tab['key'] }}"
            aria-controls="panel-{{ $tab['key'] }}"
            aria-selected="{{ $activeTab === $tab['key'] ? 'true' : 'false' }}"
            tabindex="{{ $activeTab === $tab['key'] ? '0' : '-1' }}"
            data-tab="{{ $tab['key'] }}"
            class="flex items-center gap-2.5 rounded-2xl border border-ink-900/10 bg-white px-3.5 py-3 text-left text-ink-900 hover:border-ink-900/30"
        >
            <span class="tab-icon grid place-items-center w-9 h-9 shrink-0 rounded-xl bg-cream-100 text-ink-700"><x-icon name="{{ $tab['icon'] }}" class="w-4 h-4" /></span>
            <span class="min-w-0">
                <span class="block text-[13px] font-extrabold truncate">{{ $tab['label'] }}</span>
                <span class="tab-hint hidden sm:block text-[11px] font-semibold text-ink-500 truncate">{{ $tab['hint'] }}</span>
            </span>
        </button>
        @endforeach
    </div>

    {{-- ===== KEUANGAN ===== --}}
    <div role="tabpanel" id="panel-keuangan" aria-labelledby="tab-keuangan" data-tab-panel="keuangan" class="grid gap-3.5"{{ $activeTab === 'keuangan' ? '' : ' hidden' }}>
        <div class="rounded-[24px] bg-white border border-ink-900/10 p-5 sm:p-6 grid gap-4">
            <p class="text-[11px] font-extrabold tracking-[0.18em] text-ink-500">KEUANGAN</p>
            <div>
                <label for="withdrawal_min" class="text-[13px] font-extrabold">Penarikan minimal (Rp) *</label>
                <input id="withdrawal_min" name="withdrawal_min" required data-numeric inputmode="numeric" value="{{ old('withdrawal_min', $withdrawalMin) }}" class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
                <p class="mt-1.5 text-xs font-semibold text-ink-500">Mitra tidak bisa mengajukan penarikan di bawah nominal ini.</p>
            </div>

            <div>
                <label for="withdrawal_max" class="text-[13px] font-extrabold">Penarikan maksimal (Rp) *</label>
                <input id="withdrawal_max" name="withdrawal_max" required data-numeric inputmode="numeric" value="{{ old('withdrawal_max', $withdrawalMax) }}" class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
                <p class="mt-1.5 text-xs font-semibold text-ink-500">Harus lebih besar atau sama dengan batas minimal.</p>
            </div>

            <div>
                <label for="commission_percent" class="text-[13px] font-extrabold">Komisi platform (%) *</label>
                <input id="commission_percent" name="commission_percent" type="number" min="0" max="100" step="1" required value="{{ old('commission_percent', $commissionPercent) }}" class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
                <p class="mt-1.5 text-xs font-semibold text-ink-500">Dipotong dari setiap pesanan yang selesai, sebelum masuk ke saldo warung. Isi 0 untuk komisi 0%.</p>
            </div>
        </div>

        <div class="rounded-[24px] bg-cream-100 border border-ink-900/10 p-4">
            <p class="text-[13px] font-extrabold">Contoh perhitungan</p>
            <p class="text-[12px] font-semibold text-ink-500 mt-1">Pesanan Rp 100.000 dengan komisi {{ $commissionPercent }}% → saldo warung bertambah Rp {{ number_format(100000 - (int) round(100000 * $commissionPercent / 100), 0, ',', '.') }}.</p>
        </div>
    </div>

    {{-- ===== KONTAK & ALAMAT ===== --}}
    <div role="tabpanel" id="panel-kontak" aria-labelledby="tab-kontak" data-tab-panel="kontak" class="grid gap-3.5"{{ $activeTab === 'kontak' ? '' : ' hidden' }}>
        <div class="rounded-[24px] bg-white border border-ink-900/10 p-5 sm:p-6 grid gap-4">
            <p class="text-[11px] font-extrabold tracking-[0.18em] text-ink-500">KONTAK & ALAMAT</p>
            <div>
                <label for="cs_whatsapp" class="text-[13px] font-extrabold">WhatsApp CS *</label>
                <input id="cs_whatsapp" name="cs_whatsapp" required inputmode="tel" value="{{ old('cs_whatsapp', $csWhatsapp) }}" placeholder="cth. 6281234567890" class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
                <p class="mt-1.5 text-xs font-semibold text-ink-500">Angka saja, diawali 62. Dipakai untuk tombol WhatsApp di halaman Hubungi Kami dan footer.</p>
            </div>

            <div>
                <label for="official_email" class="text-[13px] font-extrabold">Email resmi *</label>
                <input id="official_email" name="official_email" type="email" required maxlength="255" value="{{ old('official_email', $officialEmail) }}" placeholder="cth. halo@warunghebat.id" class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
            </div>

            <div>
                <label for="office_address" class="text-[13px] font-extrabold">Alamat Warung Hebat *</label>
                <textarea id="office_address" name="office_address" rows="2" required maxlength="500" placeholder="cth. Jl. Tebet Raya No. 12, Jakarta Selatan" class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition resize-y">{{ old('office_address', $officeAddress) }}</textarea>
            </div>
        </div>
    </div>

    {{-- ===== JAM OPERASIONAL ===== --}}
    <div role="tabpanel" id="panel-jam" aria-labelledby="tab-jam" data-tab-panel="jam" class="grid gap-3.5"{{ $activeTab === 'jam' ? '' : ' hidden' }}>
        <div class="rounded-[24px] bg-white border border-ink-900/10 p-5 sm:p-6 grid gap-4">
            <p class="text-[11px] font-extrabold tracking-[0.18em] text-ink-500">JAM OPERASIONAL</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="operational_days" class="text-[13px] font-extrabold">Hari *</label>
                    <input id="operational_days" name="operational_days" required maxlength="100" value="{{ old('operational_days', $operationalDays) }}" placeholder="cth. Senin–Sabtu" class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
                </div>
                <div>
                    <label for="operational_hours" class="text-[13px] font-extrabold">Jam *</label>
                    <input id="operational_hours" name="operational_hours" required maxlength="100" value="{{ old('operational_hours', $operationalHours) }}" placeholder="cth. 07.00–22.00 WIB" class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
                </div>
            </div>

            <div>
                <label for="operational_note" class="text-[13px] font-extrabold">Catatan</label>
                <input id="operational_note" name="operational_note" maxlength="255" value="{{ old('operational_note', $operationalNote) }}" placeholder="cth. Minggu & tanggal merah: slow response" class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
                <p class="mt-1.5 text-xs font-semibold text-ink-500">Opsional. Kosongkan untuk menyembunyikan catatan di halaman Hubungi Kami.</p>
            </div>
        </div>
    </div>

    {{-- ===== MEDIA SOSIAL ===== --}}
    <div role="tabpanel" id="panel-sosial" aria-labelledby="tab-sosial" data-tab-panel="sosial" class="grid gap-3.5"{{ $activeTab === 'sosial' ? '' : ' hidden' }}>
        <div class="rounded-[24px] bg-white border border-ink-900/10 p-5 sm:p-6 grid gap-4">
            <p class="text-[11px] font-extrabold tracking-[0.18em] text-ink-500">MEDIA SOSIAL</p>
            <div>
                <label for="social_instagram" class="text-[13px] font-extrabold">Instagram</label>
                <input id="social_instagram" name="social_instagram" type="url" inputmode="url" maxlength="255" value="{{ old('social_instagram', $socialInstagram) }}" placeholder="https://instagram.com/warunghebat" class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
            </div>

            <div>
                <label for="social_tiktok" class="text-[13px] font-extrabold">TikTok</label>
                <input id="social_tiktok" name="social_tiktok" type="url" inputmode="url" maxlength="255" value="{{ old('social_tiktok', $socialTiktok) }}" placeholder="https://tiktok.com/@warunghebat" class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
            </div>

            <div>
                <label for="social_x" class="text-[13px] font-extrabold">X</label>
                <input id="social_x" name="social_x" type="url" inputmode="url" maxlength="255" value="{{ old('social_x', $socialX) }}" placeholder="https://x.com/warunghebat" class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
            </div>
            <p class="text-xs font-semibold text-ink-500">Opsional. Link yang diisi tampil di footer situs; yang kosong disembunyikan.</p>
        </div>
    </div>

    <div class="grid gap-2">
        <button class="w-full sm:w-fit rounded-full bg-ink-900 hover:bg-brand-600 text-white font-extrabold text-sm px-8 py-3.5 transition">Simpan pengaturan</button>
        <p class="text-xs font-semibold text-ink-500">Tab hanya memisahkan tampilan — satu tombol simpan menyimpan semua bagian sekaligus.</p>
    </div>
</form>

<script>
(function () {
    var tablist = document.querySelector('[role="tablist"][data-active]');
    if (!tablist) return;

    var tabs = Array.prototype.slice.call(tablist.querySelectorAll('[role="tab"]'));
    var panels = Array.prototype.slice.call(document.querySelectorAll('[data-tab-panel]'));

    function exists(key) {
        return tabs.some(function (tab) { return tab.dataset.tab === key; });
    }

    function select(key, moveFocus) {
        tabs.forEach(function (tab) {
            var active = tab.dataset.tab === key;
            tab.setAttribute('aria-selected', active ? 'true' : 'false');
            tab.tabIndex = active ? 0 : -1;
            if (active && moveFocus) tab.focus();
        });
        panels.forEach(function (panel) {
            panel.hidden = panel.dataset.tabPanel !== key;
        });
    }

    function fromHash() {
        var key = (window.location.hash || '').replace('#', '');
        return exists(key) ? key : null;
    }

    function syncHash(key) {
        window.history.replaceState(null, '', '#' + key);
    }

    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            select(tab.dataset.tab, false);
            syncHash(tab.dataset.tab);
        });
    });

    // Arrow keys move between tabs, as expected of role="tablist".
    tablist.addEventListener('keydown', function (event) {
        var current = tabs.findIndex(function (tab) { return tab.getAttribute('aria-selected') === 'true'; });
        var next = null;

        if (event.key === 'ArrowRight') next = (current + 1) % tabs.length;
        else if (event.key === 'ArrowLeft') next = (current - 1 + tabs.length) % tabs.length;
        else if (event.key === 'Home') next = 0;
        else if (event.key === 'End') next = tabs.length - 1;
        if (next === null) return;

        event.preventDefault();
        select(tabs[next].dataset.tab, true);
        syncHash(tabs[next].dataset.tab);
    });

    window.addEventListener('hashchange', function () {
        var key = fromHash();
        if (key) select(key, false);
    });

    // Deep links win, then the tab the server flagged (first field with an error).
    select(fromHash() || tablist.dataset.active || tabs[0].dataset.tab, false);
})();
</script>
@endsection
