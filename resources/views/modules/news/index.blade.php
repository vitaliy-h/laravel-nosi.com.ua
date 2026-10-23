@extends('layouts.app')

@section('body-class', 'news-page')

@section('content')
    @include('partials.page_title')

    <div class="container page">
        <div class="cat-grid" style="grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));">
            @foreach($news as $item)
                {{-- `image` always resolves — to the placeholder when the item has none. --}}
                <a href="{{ $item->url }}" class="cat-tile">
                    <img src="{{ $item->image }}" alt="{{ $item->title }}" loading="lazy">
                    <div class="cat-label">
                        <div>
                            <h3>{{ $item->title }}</h3>
                            @if($item->published_at_formatted)
                                <small style="opacity: .8;">{{ $item->published_at_formatted }}</small>
                            @endif
                        </div>
                        <span><i class="bi bi-arrow-right"></i></span>
                    </div>
                </a>
            @endforeach
        </div>

        {{ $news->links() }}
    </div>
@endsection
