@extends('layouts.app')

@section('body-class', 'error-404')

@section('content')
    <div class="container error-page">
        <div>
            <div class="error-code">404</div>
            <h1>{{ t('errors.404_h1') }}</h1>
            <p>{{ t('errors.404_text') }}</p>
            <a href="{{ url('/') }}" class="btn btn-dark">{{ t('buttons.go_home') }} <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
@endsection
