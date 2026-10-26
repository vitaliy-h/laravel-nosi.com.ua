@extends('layouts.app')

@section('body-class', 'news-item-page')

@section('canonical', url($newsItem->url))

@php
    $newsSchema = array_filter([
        '@context'         => 'https://schema.org',
        '@type'            => 'NewsArticle',
        'headline'         => Str::limit($newsItem->title, 110),
        'description'      => $newsItem->meta_description ?: $newsItem->short_text,
        'image'            => $newsItem->has_image ? [url($newsItem->image)] : null,
        'url'              => url($newsItem->url),
        'mainEntityOfPage' => url($newsItem->url),
        'publisher'        => ['@id' => url('/') . '#organization'],
    ]);
@endphp

@push('schema')
    <script type="application/ld+json">@json($newsSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG)</script>
@endpush

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

            <a href="{{ save_route('news.index') }}" class="link-arrow" style="margin-top: 32px;"><i class="bi bi-arrow-left"></i> {{ __('site.news.back') }}</a>
        </div>
    </div>
@endsection
