<section class="info-cards section light-background">
    <div class="container" data-aos="fade-up" data-aos-delay="100">

        @if(!empty($block['data']['title']))
            <div class="section-title text-center">
                <h2>{{ $block['data']['title'] }}</h2>
            </div>
        @endif

        <div class="row g-4 justify-content-center">
            @foreach($block['data']['items'] ?? [] as $item)
                <div class="col-12 col-sm-6 col-lg-3" data-aos="fade-up" data-aos-delay="200">
                    <div class="info-card text-center">
                        <div class="icon-box">
                            <i class="bi {{ $item['icon'] }}"></i>
                        </div>

                        <h3>{{ $item['title'] }}</h3>

                        <p>{{ $item['text'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>

    </div>
</section>
