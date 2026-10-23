{{-- Standalone on purpose: a server error may come from the database, and the site
     layout reads it (menus, cart, dictionary). Every text falls back to a literal. --}}
@php
    $text = fn (string $key, string $fallback) => rescue(fn () => t($key), $fallback, false) ?: $fallback;
@endphp
<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>{{ $text('errors.500_title', '500') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@600;700&family=Onest:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="{{ asset('assets/css/nosi.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/nosi-pages.css') }}" rel="stylesheet">
</head>
<body>
<header class="header">
    <div class="container" style="grid-template-columns: 1fr;">
        <a href="{{ url('/') }}" class="logo" style="justify-self: center;">nosi<b>.</b></a>
    </div>
</header>
<main class="container error-page">
    <div>
        <div class="error-code">500</div>
        <h1>{{ $text('errors.500_h1', 'Щось пішло не так') }}</h1>
        <p>{{ $text('errors.500_text', 'Ми вже працюємо над цим. Спробуйте, будь ласка, пізніше.') }}</p>
        <a href="{{ url('/') }}" class="btn btn-dark">{{ $text('buttons.go_home', 'На головну') }} <i class="bi bi-arrow-right"></i></a>
    </div>
</main>
</body>
</html>
