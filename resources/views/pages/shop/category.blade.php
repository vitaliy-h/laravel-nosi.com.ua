@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>{{ $category->title ?: $category->name }}</h1>

        @if(!empty($breadcrumbs))
            <nav class="mb-4">
                @foreach($breadcrumbs as $breadcrumb)
                    <a href="{{ $breadcrumb['url'] }}">{{ $breadcrumb['name'] }}</a>
                    @if(!$loop->last)
                        <span>/</span>
                    @endif
                @endforeach
            </nav>
        @endif

        @if($category->text)
            <div class="mb-4">
                {!! $category->text !!}
            </div>
        @endif

        <div class="row">
            @foreach($products as $product)
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        @if($product->image)
                            <img src="{{ $product->image }}" class="card-img-top" alt="{{ $product->name }}">
                        @endif

                        <div class="card-body">
                            <h5 class="card-title">{{ $product->name }}</h5>

                            @if($product->description)
                                <p class="card-text">{{ $product->description }}</p>
                            @endif

                            @if($product->price)
                                <div class="mb-3">
                                    <strong>{{ $product->price }}</strong>
                                </div>
                            @endif

                            <a href="{{ route('shop.product', [$category->slug, $product->slug]) }}" class="btn btn-primary">
                                View product
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $products->links() }}
        </div>
    </div>
@endsection
