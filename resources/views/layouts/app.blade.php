<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $meta_title ?? config('app.name') }}</title>
    <meta name="description" content="{{$meta_description ?? ''}}">

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{asset('css/static/static.css')}}" rel="stylesheet">

    {{-- Global styles --}}
    @include('components.head')

    {{-- Page styles --}}
    @stack('styles')
</head>
<body>

{{-- Navbar --}}
@include('components.navbar')

<main class="py-4">
    @yield('content')
</main>

{{-- Footer --}}
@include('components.footer')

{{-- Bootstrap JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

{{-- Global scripts --}}
@include('components.scripts')

{{-- Page scripts --}}
@stack('scripts')

</body>
</html>
