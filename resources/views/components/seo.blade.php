{{-- Social + search metadata. Pages pass seoTitle/seoDescription/seoImage (and
     seoType/seoPublishedAt for articles) as view data; everything else falls back here.
     Included from layouts/app so every public page shares the same defaults. --}}
@php
    $seoTitle = $seoTitle ?? $__env->yieldContent('title', 'Warung Hebat — Belanja Dekat, Hidup Hebat');
    $seoDescription = $seoDescription ?? 'Marketplace digital mobile-first: belanja makanan, minuman & kebutuhan harian dari warung terdekatmu. Pesan dari HP, ambil sendiri atau diantar cepat.';
    $seoDefaultImage = asset('og-image.png');
    $seoImage = ($seoImage ?? null) ?: $seoDefaultImage;
    $seoType = $seoType ?? 'website';
    $seoUrl = $seoUrl ?? url()->current();
    $seoPublishedAt = $seoPublishedAt ?? null;

    // Private areas keep out of search results; buyer flows have no SEO value.
    $seoRobots = $seoRobots ?? (request()->routeIs(
        'cart.*', 'checkout.*', 'orders.*', 'dashboard', 'login', 'register', 'password.*', 'admin.*', 'seller.*'
    ) ? 'noindex, nofollow' : 'index, follow');
@endphp
<meta name="description" content="{{ $seoDescription }}">
<meta name="robots" content="{{ $seoRobots }}">
<link rel="canonical" href="{{ $seoUrl }}">
<meta property="og:site_name" content="Warung Hebat">
<meta property="og:locale" content="id_ID">
<meta property="og:type" content="{{ $seoType }}">
<meta property="og:title" content="{{ $seoTitle }}">
<meta property="og:description" content="{{ $seoDescription }}">
<meta property="og:url" content="{{ $seoUrl }}">
<meta property="og:image" content="{{ $seoImage }}">
<meta property="og:image:alt" content="{{ $seoTitle }}">
@if($seoImage === $seoDefaultImage)
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
@endif
@if($seoPublishedAt)
<meta property="article:published_time" content="{{ $seoPublishedAt->toIso8601String() }}">
@endif
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $seoTitle }}">
<meta name="twitter:description" content="{{ $seoDescription }}">
<meta name="twitter:image" content="{{ $seoImage }}">
