<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="/">{{ $page->name ?? config('app.name') }}</a>

        <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div id="nav" class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto">
                @foreach($headerMenu as $item)
                    <li class="nav-item">
                        <a class="nav-link" href="{{\App\Modules\Core\Facades\UrlFacade::normalize($item->url)}}">{{$item->name}}</a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</nav>
