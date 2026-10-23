@extends('layouts.app')

@section('body-class', 'news-item-page')

@section('content')
    @include('partials.page_title', ['title' => $newsItem->title, 'lead' => $newsItem->published_at_formatted])

    <div class="container page">
        <div class="page-narrow">
            @if($newsItem->has_image)
                <figure class="block block-image">
                    <img src="{{ $newsItem->image }}" alt="{{ $newsItem->title }}">
                </figure>
            @endif

            @include('partials.builder', ['content' => $newsItem->content])

            <a href="{{ save_route('news.index') }}" class="link-arrow" style="margin-top: 32px;"><i class="bi bi-arrow-left"></i> {{ t('news.back') }}</a>
        </div>
    </div>
@endsection
