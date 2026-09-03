<!DOCTYPE html>
<html lang="{{config('app.locale')}}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $meta_title ?? config('app.name') }}</title>
    <meta name="description" content="{{$meta_description ?? ''}}">

    @if($page && !$page->is_indexable)
        <meta name="robots" content="noindex, nofollow">
    @endif
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicons -->
    <link href="/demo/assets/img/favicon.png" rel="icon">
    <link href="/demo/assets/img/apple-touch-icon.png" rel="apple-touch-icon">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Nunito:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="/demo/vendors/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="/demo/vendors/swiper/swiper-bundle.min.css" rel="stylesheet">
    <link href="/demo/vendors/aos/aos.css" rel="stylesheet">
    <link href="/demo/vendors/glightbox/css/glightbox.min.css" rel="stylesheet">
    <link href="/demo/vendors/drift-zoom/drift-basic.css" rel="stylesheet">

    <!-- Main CSS File -->
    <link href="/demo/assets/css/main.css?v={{ file_exists(public_path('demo/assets/css/main.css')) ? filemtime(public_path('demo/assets/css/main.css')) : time() }}" rel="stylesheet">
    <link href="{{asset('css/static/static.css')}}?v={{ file_exists(public_path('css/static/static.css')) ? filemtime(public_path('css/static/static.css')) : time() }}" rel="stylesheet">

    <!-- App CSS File (our styles on top of the template) -->
    <link href="{{asset('assets/css/app.css')}}?v={{ file_exists(public_path('assets/css/app.css')) ? filemtime(public_path('assets/css/app.css')) : time() }}" rel="stylesheet">

    {{-- Global styles --}}
    @include('partials.head')

    {{-- Page styles --}}
    @stack('styles')
</head>
<body class="@yield('body-class', 'default-page')">

{{-- Navbar --}}
@include('partials.navbar')

<main class="py-4">
    @yield('content')
</main>

{{-- Footer --}}
@include('partials.footer')
@include('partials.modals')

<!-- Vendor JS Files -->
<script src="/demo/vendors/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="/demo/vendors/php-email-form/validate.js"></script>
<script src="/demo/vendors/swiper/swiper-bundle.min.js"></script>
<script src="/demo/vendors/aos/aos.js"></script>
<script src="/demo/vendors/imagesloaded/imagesloaded.pkgd.min.js"></script>
<script src="/demo/vendors/isotope-layout/isotope.pkgd.min.js"></script>
<script src="/demo/vendors/glightbox/js/glightbox.min.js"></script>
<script src="/demo/vendors/drift-zoom/Drift.min.js"></script>
<script src="/demo/vendors/purecounter/purecounter_vanilla.js"></script>

<!-- Main JS File -->
<script src="/demo/assets/js/main.js?v={{ file_exists(public_path('demo/assets/js/main.js')) ? filemtime(public_path('demo/assets/js/main.js')) : time() }}"></script>

<!-- abc-response -->
<script src="/assets/js/abc-response.js?2"></script>

<!-- App JS File (our scripts on top of the template) -->
<script src="{{asset('assets/js/app.js')}}?v={{ file_exists(public_path('assets/js/app.js')) ? filemtime(public_path('assets/js/app.js')) : time() }}"></script>

{{-- Global scripts --}}
@include('partials.scripts')

{{-- Page scripts --}}
@stack('scripts')

</body>
</html>
