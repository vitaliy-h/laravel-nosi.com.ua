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
            <?=nl2br("Your shopping cart is currently empty.

You haven't added any products yet. Browse our catalog and choose the items you need. Once you add products to your cart, they will appear here and be ready for checkout.")?>
        </div>

            <div class="empty_basket">
                <div class="continue-shopping">
                    <a href="{{module_url('shop')}}" class="btn btn-link w-100">
                        <i class="bi bi-arrow-left"></i> Continue Shopping
                    </a>
                </div>
            </div>

    </div>
@endsection
