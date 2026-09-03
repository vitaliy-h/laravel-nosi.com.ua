{{-- Builder content of the current CMS page. `$page` is shared by PageContextSharer
     and is null on screens with no CMS page behind them (order confirmation). --}}
@include('partials.builder', ['content' => $page?->content ?? []])
