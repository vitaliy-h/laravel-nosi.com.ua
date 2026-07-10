@extends('layouts.app')

@php use Illuminate\Support\Str; @endphp

@section('content')
    @include('blocks.page_title', [
    'title' => $category->title,
    'items' => array_merge([
        ['name' => t('shop.main_page'), 'url' => save_route('index.index')],
        ['name' => $page->title ?? t('shop.categories'), 'url' => save_route('shop.index')],
    ], array_slice($breadcrumbs, 0, -1)),
])


    <div class="container mt-4">
        @if($category->text)
            <div class="mb-4">
                {!! $category->text !!}
            </div>
        @endif

        <div class="row">
            @foreach($categoryList as $item)
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        @if($item->image)
                            <img
                                src="{{ $item->image }}"
                                class="news-card-list-image"
                                alt="{{ $item->title ?? $item->name }}"
                            >
                        @endif

                        <div class="card-body">
                            <h5 class="card-title">
                                {{ $item->title ?? $item->name }}
                            </h5>

                            @if($item->text ?? $item->description ?? null)
                                <p class="card-text">
                                    {{ Str::limit(strip_tags($item->text ?? $item->description), 120) }}
                                </p>
                            @endif

                            <a href="{{ save_route('shop.category', $item->slug) }}" class="btn btn-primary">
                                {{ t('shop.open_category') }}
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
