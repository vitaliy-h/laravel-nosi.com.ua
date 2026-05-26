@extends('layouts.app')

@section('content')
    @include('blocks.page_title')

    <div class="container mt-4">

        <div class="row">
            @foreach($news as $item)
                <div class="col-md-4 mb-4">
                    <div class="card h-100">

                        @if($item->image)
                            <img
                                src="{{ $item->image }}"
                                class="news-card-list-image"
                                alt="{{ $item->title }}"
                            >
                        @endif

                        <div class="card-body">
                            <h5 class="card-title">
                                {{ $item->title }}
                            </h5>

                            <p class="card-text">
                                {{ $item->short_text }}
                            </p>

                            <a
                                href="{{ module_url(\App\Modules\Core\Enums\PageModule::NEWS,[$item->slug])}}"
                                class="btn btn-primary"
                            >
                                {{t('news.read_more')}}
                            </a>
                        </div>

                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $news->links() }}
        </div>

    </div>
@endsection
