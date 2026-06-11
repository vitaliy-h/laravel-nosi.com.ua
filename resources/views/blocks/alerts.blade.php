@php
    $successKey = $successKey ?? 'success';
    $errorKey = $errorKey ?? 'error';
@endphp

@if(session()->has($successKey))
    <div class="card card-body py-3 alert alert-success">
        {{ session($successKey) }}
    </div>
@endif

@if(session()->has($errorKey))
    <div class="card card-body py-3 alert alert-danger">
        {{ session($errorKey) }}
    </div>
@endif
