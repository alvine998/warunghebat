@extends('layouts.admin')

@section('title', 'Pesan Masuk — Backoffice Warung Hebat')

@section('content')
<p class="flex items-center gap-2 text-[11px] font-extrabold tracking-[0.2em] text-brand-600"><x-icon name="chat" class="w-4 h-4" /> PESAN MASUK</p>
<h1 class="font-black tracking-tight text-3xl mt-1">Pesan dari Hubungi Kami</h1>
<p class="text-sm font-medium text-ink-500">Balas lewat email pengirim. Pesan terbaru tampil paling atas.</p>

<div class="mt-5 flex flex-wrap gap-2 text-[13px] font-extrabold">
    <a href="{{ route('admin.inquiries', ['search' => request('search')]) }}" class="px-4 py-2 rounded-full {{ ! $subject ? 'bg-ink-900 text-white' : 'bg-white border border-ink-900/10' }}">Semua ({{ $counts['all'] }})</a>
    @foreach(\App\Models\ContactMessage::SUBJECTS as $value)
        <a href="{{ route('admin.inquiries', ['subject' => $value, 'search' => request('search')]) }}" class="px-4 py-2 rounded-full {{ $subject === $value ? 'bg-ink-900 text-white' : 'bg-white border border-ink-900/10' }}">
            {{ $value }} ({{ $counts[$value] }})
        </a>
    @endforeach
</div>

<form method="GET" action="{{ route('admin.inquiries') }}" class="mt-4 flex flex-col sm:flex-row gap-2">
    @if(request('subject'))
        <input type="hidden" name="subject" value="{{ request('subject') }}">
    @endif
    <label class="sr-only" for="inquiry-search">Cari pesan</label>
    <div class="flex flex-1 items-center gap-2 rounded-2xl bg-white border border-ink-900/10 px-4 focus-within:border-brand-500 focus-within:ring-4 focus-within:ring-brand-500/10">
        <x-icon name="search" class="w-5 h-5 text-ink-400" />
        <input id="inquiry-search" name="search" value="{{ request('search') }}" type="search" placeholder="Cari nama, email, topik, atau isi pesan..." class="w-full bg-transparent py-3 text-sm font-semibold outline-none placeholder:text-ink-400">
    </div>
    <button type="submit" class="rounded-2xl bg-ink-900 px-5 py-3 text-sm font-extrabold text-white hover:bg-brand-600 transition">Cari</button>
</form>

<div class="mt-4 grid gap-2.5">
    @forelse($messages as $message)
        <div class="rounded-[24px] bg-white border border-ink-900/10 p-4 sm:p-5">
            <div class="flex items-center gap-2 flex-wrap">
                <p class="font-extrabold">{{ $message->name }}</p>
                <span class="text-[11px] font-extrabold rounded-full px-3 py-1 bg-cream-100 text-ink-700">{{ $message->subject }}</span>
            </div>
            <p class="mt-1 text-[13px] font-semibold text-ink-500">
                <a href="mailto:{{ $message->email }}" class="font-bold text-brand-600 hover:underline">{{ $message->email }}</a>
                • {{ $message->created_at->format('d M Y, H:i') }}
            </p>
            <p class="mt-2 text-[14px] font-medium text-ink-700 leading-relaxed">{{ $message->message }}</p>
            <a href="mailto:{{ $message->email }}?subject={{ rawurlencode('Re: '.$message->subject.' — Warung Hebat') }}" class="mt-3 inline-flex w-fit text-[13px] font-extrabold px-4 py-2 rounded-full bg-ink-900 text-white hover:bg-brand-600 transition">Balas via email →</a>
        </div>
    @empty
        <div class="rounded-[24px] bg-white border border-dashed border-ink-900/15 p-8 text-center">
            @if(request('search'))
                <p class="font-extrabold">Tidak ada pesan yang cocok dengan "{{ request('search') }}"</p>
                <p class="text-sm font-medium text-ink-500 mt-1">Coba kata kunci lain atau pilih tab Semua.</p>
            @else
                <p class="font-extrabold">Tidak ada pesan di topik ini</p>
                <p class="text-sm font-medium text-ink-500 mt-1">Pesan dari formulir Hubungi Kami akan muncul di sini.</p>
            @endif
        </div>
    @endforelse
</div>

<div class="mt-4">{{ $messages->links() }}</div>
@endsection
