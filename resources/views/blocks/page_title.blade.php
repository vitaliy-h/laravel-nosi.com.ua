<div class="page-title light-background">
    <div class="container d-lg-flex justify-content-between align-items-center">
        <h1 class="mb-2 mb-lg-0">{{ $breadcrumbs->last()?->title }}</h1>

        <nav class="breadcrumbs">
            <ol>
                @foreach($breadcrumbs as $item)
                    @if($loop->last)
                        <li class="current">{{ $item->title }}</li>
                    @else
                        <li><a href="{{ $item->url }}">{{ $item->title }}</a></li>
                    @endif
                @endforeach
            </ol>
        </nav>
    </div>
</div>
