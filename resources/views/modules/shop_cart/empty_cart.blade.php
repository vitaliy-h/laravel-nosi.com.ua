@extends('layouts.app')

@php use Illuminate\Support\Str; @endphp

@section('content')
    @include('partials.page_title')

    <div class="container mt-4">
        @include('partials.page_content')

        <div class="row">
            <?=nl2br(t('shop.empty_cart_title')."\n\n".t('shop.empty_cart_text'))?>
        </div>

            <div class="empty_cart">
                <div class="continue-shopping">
                    <a href="{{save_route('shop.index')}}" class="btn btn-link w-100">
                        <i class="bi bi-arrow-left"></i> {{ t('shop.continue_shopping') }}
                    </a>
                </div>
            </div>

    </div>
@endsection
