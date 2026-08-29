@extends('layouts.app')

@section('content')
    <div class="container py-5">

        <a href="{{ save_route('news.index') }}" class="btn btn-light mb-4">
            ← Back
        </a>

        <h1 class="mb-3">
            {{ $newsItem->title }}
        </h1>

        @if($newsItem->published_at_formatted)
            <div class="text-muted mb-4">
                {{ $newsItem->published_at_formatted }}
            </div>
        @endif

        @if($newsItem->has_image)
            <img
                src="{{ $newsItem->image }}"
                class="img-fluid rounded mb-4 news-card-image"
                alt="{{ $newsItem->title }}"
            >
        @endif

        <div>
            @include('components.builder', ['content' => $newsItem->content])
        </div>

    </div>
@endsection
