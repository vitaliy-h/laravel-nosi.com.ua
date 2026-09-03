@php
    $images = $block['data']['images'] ?? [];
    $galleryId = 'gallery-' . substr(md5(serialize($images)), 0, 8);
@endphp

<section class="builder-gallery section light-background">
    <div class="container" data-aos="fade-up" data-aos-delay="100">

        @if(!empty($block['data']['title']))
            <div class="section-title text-center">
                <h2>{{ $block['data']['title'] }}</h2>
            </div>
        @endif

        <div class="row g-3">
            @foreach($images as $image)
                <div class="col-6 col-md-4 col-lg-3" data-aos="zoom-in" data-aos-delay="200">
                    <a href="{{ asset('storage/' . $image) }}"
                       class="glightbox"
                       data-gallery="{{ $galleryId }}">
                        <img src="{{ asset('storage/' . $image) }}"
                             class="img-fluid rounded w-100"
                             style="height: 220px; object-fit: cover;"
                             loading="lazy"
                             alt="">
                    </a>
                </div>
            @endforeach
        </div>

    </div>
</section>
