@extends('layouts.app')

@php use Illuminate\Support\Str; @endphp

@section('content')
    @include('blocks.page_title', [
    'title' => $category->title,
    'items' => array_merge([
        ['name' => 'Main Page', 'url' => module_url(\App\Modules\Core\Enums\PageModule::INDEX)],
        ['name' => $page->title ?? 'Shop', 'url' => module_url(\App\Modules\Core\Enums\PageModule::SHOP)],
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

                            <a href="{{ module_url(\App\Modules\Core\Enums\PageModule::SHOP,[$item->slug]) }}" class="btn btn-primary">
                                Open category
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
