<section class="block">
    @if(!empty($block['data']['title']))
        <h2 class="block-title">{{ $block['data']['title'] }}</h2>
    @endif
    <div class="block-teasers">
        @foreach($block['data']['items'] ?? [] as $item)
            <div class="benefit">
                <i class="bi {{ $item['icon'] ?? 'bi-check2-circle' }}"></i>
                <div><b>{{ $item['title'] ?? '' }}</b><span>{{ $item['text'] ?? '' }}</span></div>
            </div>
        @endforeach
    </div>
</section>
