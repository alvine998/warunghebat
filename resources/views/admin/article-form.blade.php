@extends('layouts.admin')

@section('title', ($article->exists ? 'Edit' : 'Tulis').' Artikel — Backoffice Warung Hebat')

@section('content')
<a href="{{ route('admin.articles') }}" class="text-[13px] font-extrabold text-ink-500 hover:text-ink-900">← Artikel</a>

<h1 class="font-black tracking-tight text-3xl mt-2">{{ $article->exists ? 'Edit artikel' : 'Tulis artikel' }}</h1>
<p class="text-sm font-medium text-ink-500">Isi artikel memakai format Markdown sederhana: <code class="rounded bg-cream-100 px-1.5 py-0.5 text-[12px] font-bold">## Subjudul</code>, <code class="rounded bg-cream-100 px-1.5 py-0.5 text-[12px] font-bold">- daftar</code>, <code class="rounded bg-cream-100 px-1.5 py-0.5 text-[12px] font-bold">**tebal**</code>.</p>

<form method="POST" action="{{ $article->exists ? route('admin.articles.update', $article) : route('admin.articles.store') }}" enctype="multipart/form-data" class="mt-5 max-w-3xl grid gap-3.5">
    @csrf
    @if($article->exists)
        @method('PUT')
    @endif

    <div class="rounded-[24px] bg-white border border-ink-900/10 p-5 sm:p-6 grid gap-4">
        <div>
            <label for="title" class="text-[13px] font-extrabold">Judul artikel *</label>
            <input id="title" name="title" required maxlength="140" value="{{ old('title', $article->title) }}" placeholder="cth. Cara Memilih Sembako Segar di Warung Tetangga" class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
            <p class="mt-1.5 text-xs font-semibold text-ink-500">Alamat artikel dibuat otomatis dari judul: <span class="font-extrabold text-ink-900">/artikel/{{ $article->slug ?: 'judul-artikelmu' }}</span></p>
        </div>

        <div>
            <label for="excerpt" class="text-[13px] font-extrabold">Ringkasan</label>
            <textarea id="excerpt" name="excerpt" rows="2" maxlength="300" placeholder="Satu sampai dua kalimat yang muncul di kartu artikel dan pratinjau media sosial." class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition resize-y">{{ old('excerpt', $article->excerpt) }}</textarea>
            <p class="mt-1.5 text-xs font-semibold text-ink-500">Maksimal 300 karakter. Kalau dikosongkan, ringkasan diambil dari awal isi artikel.</p>
        </div>

        <div>
            <label for="body" class="text-[13px] font-extrabold">Isi artikel *</label>
            <textarea id="body" name="body" rows="18" required placeholder="Tulis artikelnya di sini..." class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[14px] font-medium leading-relaxed outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition resize-y">{{ old('body', $article->body) }}</textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="status" class="text-[13px] font-extrabold">Status *</label>
                <select id="status" name="status" required class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
                    @foreach(\App\Models\Article::STATUSES as $status)
                        <option value="{{ $status }}" @selected(old('status', $article->status ?: \App\Models\Article::STATUS_DRAFT) === $status)>{{ \App\Models\Article::STATUS_LABELS[$status] }}</option>
                    @endforeach
                </select>
                <p class="mt-1.5 text-xs font-semibold text-ink-500">Draft tidak tampil di /artikel maupun sitemap.</p>
            </div>
            <div>
                <label for="cover" class="text-[13px] font-extrabold">Cover artikel</label>
                <input id="cover" name="cover" type="file" accept="image/png,image/jpeg,image/webp" class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[13px] font-semibold file:mr-3 file:rounded-full file:border-0 file:bg-ink-900 file:px-4 file:py-2 file:text-[12px] file:font-extrabold file:text-white outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
                <p class="mt-1.5 text-xs font-semibold text-ink-500">Opsional. JPG, PNG, atau WebP, maksimal 2MB. Dipakai juga sebagai gambar pratinjau saat dibagikan.</p>
                @if($article->cover_path)
                    <img src="{{ $article->cover_url }}" alt="Cover {{ $article->title }}" class="mt-2 w-40 rounded-2xl border border-ink-900/10">
                @endif
            </div>
        </div>
    </div>

    <div class="flex flex-col sm:flex-row gap-2">
        <button class="w-full sm:w-fit rounded-full bg-ink-900 hover:bg-brand-600 text-white font-extrabold text-sm px-8 py-3.5 transition">{{ $article->exists ? 'Simpan perubahan' : 'Simpan artikel' }}</button>
        <a href="{{ route('admin.articles') }}" class="w-full sm:w-fit text-center rounded-full border border-ink-900/15 hover:bg-ink-900 hover:text-white font-extrabold text-sm px-8 py-3.5 transition">Batal</a>
    </div>
</form>
@endsection
