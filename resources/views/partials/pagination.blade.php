{{-- Storefront pagination (Paginator::defaultView in AppServiceProvider). Shows the first,
     the last and the pages around the current one. --}}
@if ($paginator->hasPages())
    @php
        $current = $paginator->currentPage();
        $last = method_exists($paginator, 'lastPage') ? $paginator->lastPage() : $current + ($paginator->hasMorePages() ? 1 : 0);
        $pages = collect([1, $current - 1, $current, $current + 1, $last])
            ->filter(fn ($p) => $p >= 1 && $p <= $last)
            ->unique()
            ->sort()
            ->values();
    @endphp
    <nav aria-label="Pagination">
        <ul class="pagination">
            @if ($paginator->onFirstPage())
                <li class="is-disabled"><span><i class="bi bi-chevron-left"></i></span></li>
            @else
                <li><a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous"><i class="bi bi-chevron-left"></i></a></li>
            @endif

            @foreach ($pages as $i => $page)
                @if ($i > 0 && $page > $pages[$i - 1] + 1)
                    <li class="is-gap"><span>…</span></li>
                @endif
                @if ($page === $current)
                    <li class="is-active" aria-current="page"><span>{{ $page }}</span></li>
                @else
                    <li><a href="{{ $paginator->url($page) }}">{{ $page }}</a></li>
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <li><a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next"><i class="bi bi-chevron-right"></i></a></li>
            @else
                <li class="is-disabled"><span><i class="bi bi-chevron-right"></i></span></li>
            @endif
        </ul>
    </nav>
@endif
