{{-- Page head: breadcrumbs + h1. The title is the last breadcrumb unless the page passes
     its own (`$title`); `$lead` adds a line under it; `$hideTitle` keeps only the
     breadcrumbs (the product page has its h1 next to the photos). --}}
<section class="page-head">
    <div class="container">
        @if(($breadcrumbs ?? collect())->count() > 1)
            <ol class="crumbs">
                @foreach($breadcrumbs as $item)
                    @if($loop->last)
                        <li aria-current="page">{{ $item->title }}</li>
                    @else
                        <li><a href="{{ $item->url }}">{{ $item->title }}</a></li>
                    @endif
                @endforeach
            </ol>
        @endif

        @unless($hideTitle ?? false)
            <h1>{{ $title ?? ($breadcrumbs ?? collect())->last()?->title }}</h1>
        @endunless

        @if(!empty($lead))
            <p class="lead">{{ $lead }}</p>
        @endif
    </div>
</section>
