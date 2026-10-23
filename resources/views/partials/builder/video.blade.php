@php
    $url = $block['data']['url'] ?? '';
    $embedUrl = null;
    if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/', $url, $m)) {
        $embedUrl = 'https://www.youtube.com/embed/' . $m[1];
    } elseif (str_contains($url, 'youtube.com/embed/')) {
        $embedUrl = $url;
    } elseif (preg_match('/vimeo\.com\/(\d+)/', $url, $m)) {
        $embedUrl = 'https://player.vimeo.com/video/' . $m[1];
    }
@endphp

@if($embedUrl)
    <div class="block block-video">
        <iframe src="{{ $embedUrl }}" title="Video"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen loading="lazy"></iframe>
    </div>
@elseif($url)
    <p class="block"><a href="{{ $url }}" class="link-arrow" target="_blank" rel="noopener"><i class="bi bi-play-circle"></i> {{ $url }}</a></p>
@endif
