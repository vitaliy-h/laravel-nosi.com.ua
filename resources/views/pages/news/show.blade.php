@extends('layouts.app')

@section('content')
    <div class="container py-5">

        <a href="{{ route('news') }}" class="btn btn-light mb-4">
            ← Back
        </a>

        <h1 class="mb-3">
            {{ $newsItem->title }}
        </h1>

        @if($newsItem->published_at)
            <div class="text-muted mb-4">
                {{ $newsItem->published_at->format('d.m.Y H:i') }}
            </div>
        @endif

        @if($newsItem->image)
            <img
                src="{{ $newsItem->image }}"
                class="img-fluid rounded mb-4 news-card-image"
                alt="{{ $newsItem->title }}"
            >
        @endif

        <div>
            {!! $newsItem->text !!}
        </div>

    </div>
@endsection
