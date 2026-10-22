{{-- Storefront layout of the Nosi design (design/home.html). Own CSS/JS, no Bootstrap theme. --}}
<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $meta_title ?? config('app.name') }}</title>
    <meta name="description" content="{{ $meta_description ?? '' }}">

    @if($page && !$page->is_indexable)
        <meta name="robots" content="noindex, nofollow">
    @endif
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="{{ asset('favicon.svg') }}" rel="icon">

    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@400;500;600;700&family=Onest:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link href="{{ asset('assets/css/nosi.css') }}?v={{ filemtime(public_path('assets/css/nosi.css')) }}" rel="stylesheet">

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
