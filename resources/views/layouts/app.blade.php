{{-- Storefront layout of the Nosi design (design/home.html), shared by every page.
     nosi.css — the home page design, nosi-pages.css — inner pages in the same style. --}}
<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $meta_title ?? config('app.name') }}</title>
    <meta name="description" content="{{ $meta_description ?? '' }}">

    @if(($page ?? null) && !$page->is_indexable)
        <meta name="robots" content="noindex, nofollow">
    @endif
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

    @stack('styles')
</head>
<body class="@yield('body-class', 'default-page')">

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
