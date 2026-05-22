<!-- Page Title -->
<div class="page-title light-background">
    <div class="container d-lg-flex justify-content-between align-items-center">
        <h1 class="mb-2 mb-lg-0">{{ $page->title }}</h1>
        <nav class="breadcrumbs">
            <ol>
                @foreach($breadcrumbs as $item)
                    <li><a href="{{\App\Modules\Core\Facades\UrlFacade::normalizeForLink($item->slug)}}">{{$item->title}}</a></li>
                @endforeach

                <li class="current">{{ $page->title }}</li>
            </ol>
        </nav>
    </div>
</div>
<!-- End Page Title -->
