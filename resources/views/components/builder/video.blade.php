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

<section class="builder-video section">
    <div class="container" data-aos="zoom-in" data-aos-delay="100">

        @if($embedUrl)
            <div class="ratio ratio-16x9">
                <iframe src="{{ $embedUrl }}"
                        title="Video"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen
                        loading="lazy"></iframe>
            </div>
        @elseif($url)
            <div class="text-center">
                <a href="{{ $url }}" class="glightbox pulsating-play-btn"></a>
            </div>
        @endif

    </div>
</section>
