@if ($paginator->hasPages())
    @php
        $current = $paginator->currentPage();
        $last    = $paginator->lastPage();

        $show = collect([1, $current - 1, $current, $current + 1, $last])
            ->filter(fn($p) => $p >= 1 && $p <= $last)
            ->unique()
            ->sort()
            ->values();
    @endphp

    <div class="pagination-wrapper" data-aos="fade-up">
        {{-- Prev --}}
        @if ($paginator->onFirstPage())
            <button type="button" class="btn-prev" disabled>
                <i class="bi bi-chevron-left"></i>
            </button>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="btn-prev">
                <i class="bi bi-chevron-left"></i>
            </a>
        @endif

        {{-- Pages --}}
        <div class="page-numbers">
            @foreach ($show as $i => $page)
                @if ($i > 0 && $page > $show[$i - 1] + 1)
                    <span>...</span>
                @endif

                @if ($page === $current)
                    <button type="button" class="active">{{ $page }}</button>
                @else
                    <a href="{{ $paginator->url($page) }}">
                        <button type="button">{{ $page }}</button>
                    </a>
                @endif
            @endforeach
        </div>

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="btn-next">
                <i class="bi bi-chevron-right"></i>
            </a>
        @else
            <button type="button" class="btn-next" disabled>
                <i class="bi bi-chevron-right"></i>
            </button>
        @endif
    </div>
@endif
