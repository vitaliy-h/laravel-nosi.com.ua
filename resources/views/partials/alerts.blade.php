@php
    $successKey = $successKey ?? 'success';
    $errorKey = $errorKey ?? 'error';
@endphp

@if(session()->has($successKey))
    <div class="alert alert-success" role="status">{{ session($successKey) }}</div>
@endif

@if(session()->has($errorKey))
    <div class="alert alert-error" role="alert">{{ session($errorKey) }}</div>
@endif
