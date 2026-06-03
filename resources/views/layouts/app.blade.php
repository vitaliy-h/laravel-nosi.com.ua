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
    <link href="/demo/assets/css/main.css" rel="stylesheet">
    <link href="{{asset('css/static/static.css')}}" rel="stylesheet">

    {{-- Global styles --}}
    @include('components.head')

    {{-- Page styles --}}
    @stack('styles')
</head>
<!-- todo body class -->
<body class="index-page">

{{-- Navbar --}}
@include('components.navbar')

<main class="py-4">
    @yield('content')
</main>

{{-- Footer --}}
@include('components.footer')

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
<script src="/demo/assets/js/main.js"></script>

{{-- Global scripts --}}
@include('components.scripts')

{{-- Page scripts --}}
@stack('scripts')

</body>
</html>
