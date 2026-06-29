@if($specialCategories->isNotEmpty())
    <section id="category-cards" class="category-cards section">

        <div class="container" data-aos="fade-up" data-aos-delay="100">

            <div class="category-slider swiper init-swiper">
                <script type="application/json" class="swiper-config">
                    {
                      "loop": true,
                      "autoplay": {
                        "delay": 5000,
                        "disableOnInteraction": false
                      },
                      "grabCursor": true,
                      "speed": 600,
                      "slidesPerView": "auto",
                      "spaceBetween": 20,
                      "navigation": {
                        "nextEl": ".swiper-button-next",
                        "prevEl": ".swiper-button-prev"
                      },
                      "breakpoints": {
                        "320": {
                          "slidesPerView": 2,
                          "spaceBetween": 15
                        },
                        "576": {
                          "slidesPerView": 3,
                          "spaceBetween": 15
                        },
                        "768": {
                          "slidesPerView": 4,
                          "spaceBetween": 20
                        },
                        "992": {
                          "slidesPerView": 5,
                          "spaceBetween": 20
                        },
                        "1200": {
                          "slidesPerView": 6,
                          "spaceBetween": 20
                        }
                      }
                    }
                </script>

                <div class="swiper-wrapper">
                    @foreach($specialCategories as $index => $category)
                        <div class="swiper-slide">
                            <div class="category-card" data-aos="fade-up" data-aos-delay="{{ ($index % 8 + 1) * 100 }}">
                                <div class="category-image">
                                    <img src="{{ Storage::url($category->img) }}" alt="{{ $category->title }}" class="img-fluid">
                                </div>
                                <h3 class="category-title">{{ $category->title }}</h3>
                                <p class="category-count">{{ $category->products_count }} Products</p>
                                <a href="{{ module_url('shop', [$category->slug]) }}" class="stretched-link"></a>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            </div>

        </div>

    </section><!-- /Category Cards Section -->
@endif
