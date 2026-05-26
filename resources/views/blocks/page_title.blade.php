@php
    $title = $title ?? $page->title;
    $items = $items ?? $breadcrumbs ?? [];
@endphp

<div class="page-title light-background">
    <div class="container d-lg-flex justify-content-between align-items-center">
        <h1 class="mb-2 mb-lg-0">{{ $title }}</h1>

        <nav class="breadcrumbs">
            <ol>
                @foreach($items as $item)
                    <li>
                        @if(is_array($item))
                            <a href="{{ $item['url'] }}">{{ $item['name'] }}</a>
                        @else
                            <a href="{{ \App\Modules\Core\Facades\UrlFacade::normalizeForLink($item->slug) }}">
                                {{ $item->title }}
                            </a>
                        @endif
                    </li>
                @endforeach

                <li class="current">{{ $title }}</li>
            </ol>
        </nav>
    </div>
</div>
