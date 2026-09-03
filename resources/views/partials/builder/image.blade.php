<section class="builder-image section">
    <div class="container" data-aos="fade-up">
        @if(!empty($block['data']['image']))
            <img src="{{ asset('storage/' . $block['data']['image']) }}"
                 alt="{{ $block['data']['title'] ?? '' }}"
                 class="img-fluid w-100 rounded"
                 loading="lazy">
        @endif

        @if(!empty($block['data']['title']))
            <p class="text-center text-muted mt-2 mb-0">{{ $block['data']['title'] }}</p>
        @endif
    </div>
</section>
