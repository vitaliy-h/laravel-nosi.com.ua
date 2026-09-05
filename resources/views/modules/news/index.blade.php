@extends('layouts.app')

@section('content')
    @include('partials.page_title')

    <div class="container mt-4">

        <div class="row">
            @foreach($news as $item)
                <div class="col-md-4 mb-4">
                    <div class="card h-100">

                        {{-- `image` always resolves — to the placeholder when the item has none. --}}
                        <img
                            src="{{ $item->image }}"
                            class="news-card-list-image"
                            alt="{{ $item->title }}"
                        >

                        <div class="card-body">
                            <h5 class="card-title">
                                {{ $item->title }}
                            </h5>

                            <p class="card-text">
                                {{ $item->short_text }}
                            </p>

                            <a
                                href="{{ $item->url }}"
                                class="btn btn-primary"
                            >
                                {{t('news.read_more')}}
                            </a>
                        </div>

                    </div>
                </div>
            @endforeach
        </div>

        <div class="category-pagination mt-4">
            {{ $news->links() }}
        </div>

    </div>
@endsection
