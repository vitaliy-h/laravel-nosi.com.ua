@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>{{ $page->title }}</h1>

        @if($page->text)
            <div class="mb-4">
                {!! $page->text !!}
            </div>
        @endif

        <div class="row">
            @foreach($categoryList as $category)
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        @if($category->image)
                            <img src="{{ $category->image }}" class="card-img-top" alt="{{ $category->name }}">
                        @endif

                        <div class="card-body">
                            <h5 class="card-title">{{ $category->name }}</h5>

                            @if($category->description)
                                <p class="card-text">{{ $category->description }}</p>
                            @endif

                            <a href="{{ route('shop.category', $category->slug) }}" class="btn btn-primary">
                                Open category
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
