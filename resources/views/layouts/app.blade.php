{{-- Storefront layout of the Nosi design (design/home.html), shared by every page.
     nosi.css — the home page design, nosi-pages.css — inner pages in the same style. --}}
<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $meta_title ?? config('app.name') }}</title>
    <meta name="description" content="{{ $meta_description ?? '' }}">

    {{-- Error pages (404, 500 — they get $exception) and pages switched off in admin stay out of the index. --}}
    @if(isset($exception) || (($page ?? null) && !$page->is_indexable))
        <meta name="robots" content="noindex, nofollow">
    @else
        <meta name="robots" content="index, follow, max-image-preview:large">
    @endif
    {{-- A page sets its own with @section('canonical', ...); otherwise the current URL
         without the query string, keeping only ?page=N of a paginated list. --}}
    @php
        $canonicalUrl = trim($__env->yieldContent('canonical'))
            ?: url()->current() . (request()->integer('page') > 1 ? '?page=' . request()->integer('page') : '');
        $siteName = Str::ucfirst(t('header.logo') ?: config('app.name'));
        $ogTitle = $meta_title ?? config('app.name');
        // A page sets its picture with @section('og_image', absolute url); otherwise — or when
        // it is the no_image.svg placeholder, which messengers do not show — public/og-image.jpg.
        $ogImage = trim($__env->yieldContent('og_image'));
        if ($ogImage === '' || str_ends_with(strtolower($ogImage), '.svg')) {
            $ogImage = asset('og-image.jpg');
        }
    @endphp
    <link rel="canonical" href="{{ $canonicalUrl }}">
    {{-- The storefront is single-language: declare it and make it the default. --}}
    <link rel="alternate" hreflang="{{ config('app.locale') }}" href="{{ $canonicalUrl }}">
    <link rel="alternate" hreflang="x-default" href="{{ $canonicalUrl }}">

    {{-- OpenGraph / Twitter: link previews in Telegram, Viber, Facebook etc. --}}
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:locale" content="{{ config('app.locale') === 'uk' ? 'uk_UA' : str_replace('-', '_', config('app.locale')) }}">
    <meta property="og:title" content="{{ $ogTitle }}">
    <meta property="og:description" content="{{ $meta_description ?? '' }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:image:alt" content="{{ $ogTitle }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $ogTitle }}">
    <meta name="twitter:description" content="{{ $meta_description ?? '' }}">
    <meta name="twitter:image" content="{{ $ogImage }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="{{ asset('favicon.ico') }}?v=2" rel="icon" sizes="48x48">
    <link href="{{ asset('favicon.svg') }}?v=2" rel="icon" type="image/svg+xml">
    <link href="{{ asset('apple-touch-icon.png') }}?v=2" rel="apple-touch-icon">

    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@400;500;600;700&family=Onest:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link href="{{ asset('assets/css/nosi.css') }}?v={{ filemtime(public_path('assets/css/nosi.css')) }}" rel="stylesheet">
    <link href="{{ asset('assets/css/nosi-pages.css') }}?v={{ filemtime(public_path('assets/css/nosi-pages.css')) }}" rel="stylesheet">

    @include('partials.head')

    @include('partials.schema')
    @stack('schema')

    @stack('styles')

    {!! t('counters.head') !!}
</head>
<body class="@yield('body-class', 'default-page')">

{!! t('counters.body') !!}

@include('partials.announce')

@include('partials.site_header')

<main>
    @yield('content')
</main>

@include('partials.site_footer')

<script src="/assets/js/abc-response.js?2"></script>
<script src="{{ asset('assets/js/nosi.js') }}?v={{ filemtime(public_path('assets/js/nosi.js')) }}"></script>

@include('partials.scripts')

@stack('scripts')

</body>
</html>
