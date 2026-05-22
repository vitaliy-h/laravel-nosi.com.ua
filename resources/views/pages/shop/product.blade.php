@extends('layouts.app')

@section('content')
    <div class="container">
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

        <h1>{{ $product->title ?: $product->name }}</h1>

        <div class="row">
            <div class="col-md-5">
                @if($product->image)
                    <img src="{{ $product->image }}" class="img-fluid" alt="{{ $product->name }}">
                @endif
            </div>

            <div class="col-md-7">
                @if($product->description)
                    <p>{{ $product->description }}</p>
                @endif

                @if($product->price)
                    <div class="mb-4">
                        <strong>{{ $product->price }}</strong>
                    </div>
                @endif

                @if($product->text)
                    <div>
                        {!! $product->text !!}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
