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
            @foreach($categoryList as $category)
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        @if($category->image)
                            <img
                                src="{{ $category->image }}"
                                class="news-card-list-image"
                                alt="{{ $category->title ?? $category->name }}"
                            >
                        @endif

                        <div class="card-body">
                            <h5 class="card-title">
                                {{ $category->title ?? $category->name }}
                            </h5>

                            @if($category->text ?? $category->description ?? null)
                                <p class="card-text">
                                    {{ Str::limit(strip_tags($category->text ?? $category->description), 120) }}
                                </p>
                            @endif

                            <a href="{{ save_route('shop.category', $category->slug) }}" class="btn btn-primary">
                                {{ t('shop.open_category') }}
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
