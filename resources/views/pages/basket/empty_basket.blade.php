@extends('layouts.app')

@php use Illuminate\Support\Str; @endphp

@section('content')
    @include('blocks.page_title')

    <div class="container mt-4">
        @if($page->text)
            <div class="mb-4">
                {!! $page->text !!}
            </div>
        @endif

        <div class="row">
            <?=nl2br(t('shop.empty_basket_title')."\n\n".t('shop.empty_basket_text'))?>
        </div>

            <div class="empty_basket">
                <div class="continue-shopping">
                    <a href="{{save_route('shop.index')}}" class="btn btn-link w-100">
                        <i class="bi bi-arrow-left"></i> {{ t('shop.continue_shopping') }}
                    </a>
                </div>
            </div>

    </div>
@endsection
