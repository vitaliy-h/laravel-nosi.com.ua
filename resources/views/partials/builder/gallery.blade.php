@php($images = $block['data']['images'] ?? [])

@if($images !== [])
    <section class="block">
        @if(!empty($block['data']['title']))
            <h2 class="block-title">{{ $block['data']['title'] }}</h2>
        @endif
        <div class="block-gallery">
            @foreach($images as $image)
                <a href="{{ asset('storage/' . $image) }}" target="_blank" rel="noopener">
                    <img src="{{ asset('storage/' . $image) }}" alt="" loading="lazy">
                </a>
            @endforeach
        </div>
    </section>
@endif
