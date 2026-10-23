@extends('layouts.app')

@section('body-class', 'cart-page')

@section('content')
    @include('partials.page_title')

    <div class="container page">
        @include('partials.page_content')

        <div class="result">
            <div class="result-icon"><i class="bi bi-bag"></i></div>
            <h2>{{ t('shop.empty_cart_title') }}</h2>
            <p>{{ t('shop.empty_cart_text') }}</p>
            <a href="{{ save_route('shop.index') }}" class="btn btn-dark">{{ t('shop.continue_shopping') }} <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
@endsection
