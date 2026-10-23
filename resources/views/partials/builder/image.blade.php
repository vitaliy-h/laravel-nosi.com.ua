@if(!empty($block['data']['image']))
    <figure class="block block-image">
        <img src="{{ asset('storage/' . $block['data']['image']) }}" alt="{{ $block['data']['title'] ?? '' }}" loading="lazy">
        @if(!empty($block['data']['title']))
            <figcaption>{{ $block['data']['title'] }}</figcaption>
        @endif
    </figure>
@endif
