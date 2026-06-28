@extends('layouts.app')

@section('body-class', 'index-page')

@section('content')
    <main class="main">

        @include('blocks.hero', [
                    'title' => t('index.hero_title'),
                    'subtitle' => t('index.hero_subtitle')
                ])

        @include('components.builder', ['content' => $page->content])

        <!-- Category Cards Section -->
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
                        <!-- Category Card 1 -->
                        <div class="swiper-slide">
                            <div class="category-card" data-aos="fade-up" data-aos-delay="100">
                                <div class="category-image">
                                    <img src="/demo/assets/img/product/product-1.webp" alt="Category" class="img-fluid">
                                </div>
                                <h3 class="category-title">Vestibulum ante</h3>
                                <p class="category-count">4 Products</p>
                                <a href="ctaegory.html" class="stretched-link"></a>
                            </div>
                        </div>

                        <!-- Category Card 2 -->
                        <div class="swiper-slide">
                            <div class="category-card" data-aos="fade-up" data-aos-delay="200">
                                <div class="category-image">
                                    <img src="/demo/assets/img/product/product-6.webp" alt="Category" class="img-fluid">
                                </div>
                                <h3 class="category-title">Maecenas nec</h3>
                                <p class="category-count">8 Products</p>
                                <a href="ctaegory.html" class="stretched-link"></a>
                            </div>
                        </div>

                        <!-- Category Card 3 -->
                        <div class="swiper-slide">
                            <div class="category-card" data-aos="fade-up" data-aos-delay="300">
                                <div class="category-image">
                                    <img src="/demo/assets/img/product/product-9.webp" alt="Category" class="img-fluid">
                                </div>
                                <h3 class="category-title">Aenean tellus</h3>
                                <p class="category-count">4 Products</p>
                                <a href="ctaegory.html" class="stretched-link"></a>
                            </div>
                        </div>

                        <!-- Category Card 4 -->
                        <div class="swiper-slide">
                            <div class="category-card" data-aos="fade-up" data-aos-delay="400">
                                <div class="category-image">
                                    <img src="/demo/assets/img/product/product-f-1.webp" alt="Category" class="img-fluid">
                                </div>
                                <h3 class="category-title">Donec quam</h3>
                                <p class="category-count">12 Products</p>
                                <a href="ctaegory.html" class="stretched-link"></a>
                            </div>
                        </div>

                        <!-- Category Card 5 -->
                        <div class="swiper-slide">
                            <div class="category-card" data-aos="fade-up" data-aos-delay="500">
                                <div class="category-image">
                                    <img src="/demo/assets/img/product/product-10.webp" alt="Category" class="img-fluid">
                                </div>
                                <h3 class="category-title">Phasellus leo</h3>
                                <p class="category-count">4 Products</p>
                                <a href="ctaegory.html" class="stretched-link"></a>
                            </div>
                        </div>

                        <!-- Category Card 6 -->
                        <div class="swiper-slide">
                            <div class="category-card" data-aos="fade-up" data-aos-delay="600">
                                <div class="category-image">
                                    <img src="/demo/assets/img/product/product-m-1.webp" alt="Category" class="img-fluid">
                                </div>
                                <h3 class="category-title">Quisque rutrum</h3>
                                <p class="category-count">2 Products</p>
                                <a href="ctaegory.html" class="stretched-link"></a>
                            </div>
                        </div>

                        <!-- Category Card 7 -->
                        <div class="swiper-slide">
                            <div class="category-card" data-aos="fade-up" data-aos-delay="700">
                                <div class="category-image">
                                    <img src="/demo/assets/img/product/product-10.webp" alt="Category" class="img-fluid">
                                </div>
                                <h3 class="category-title">Etiam ultricies</h3>
                                <p class="category-count">4 Products</p>
                                <a href="ctaegory.html" class="stretched-link"></a>
                            </div>
                        </div>

                        <!-- Category Card 8 -->
                        <div class="swiper-slide">
                            <div class="category-card" data-aos="fade-up" data-aos-delay="800">
                                <div class="category-image">
                                    <img src="/demo/assets/img/product/product-2.webp" alt="Category" class="img-fluid">
                                </div>
                                <h3 class="category-title">Fusce fermentum</h3>
                                <p class="category-count">4 Products</p>
                                <a href="ctaegory.html" class="stretched-link"></a>
                            </div>
                        </div>
                    </div>

                    <div class="swiper-button-next"></div>
                    <div class="swiper-button-prev"></div>
                </div>

            </div>

        </section><!-- /Category Cards Section -->

        <!-- Best Sellers Section -->
        <section id="best-sellers" class="best-sellers section">

            <!-- Section Title -->
            <div class="container section-title" data-aos="fade-up">
                <h2>Best Sellers</h2>
                <p>Necessitatibus eius consequatur ex aliquid fuga eum quidem sint consectetur velit</p>
            </div><!-- End Section Title -->

            <div class="container" data-aos="fade-up" data-aos-delay="100">

                <div class="row gy-4">
                    @foreach($bestSellers as $product)
                        @include('pages.shop.product-card', ['product' => $product, 'dataAttrs' => ['data-aos' => 'fade-up', 'data-aos-delay' => '100']])
                    @endforeach
                </div>

            </div>

        </section><!-- /Best Sellers Section -->

        <!-- Product List Section -->
        <section id="product-list" class="product-list section">

            <div class="container isotope-layout" data-aos="fade-up" data-aos-delay="100" data-default-filter="*" data-layout="masonry" data-sort="original-order">

                <div class="row">
                    <div class="col-12">
                        <div class="product-filters isotope-filters mb-5 d-flex justify-content-center" data-aos="fade-up">
                            <ul class="d-flex flex-wrap gap-2 list-unstyled">
                                <li class="filter-active" data-filter="*">All</li>
                                @foreach($featuredCategories as $category)
                                    <li data-filter=".filter_category_{{ $category->id }}">{{ $category->title }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="row product-container isotope-container best-sellers" data-aos="fade-up" data-aos-delay="200">

                    @foreach($featuredProducts as $product)
                        @php
                            $rootCategory = $product->category?->isRoot()
                                ? $product->category
                                : $product->category?->ancestors->first();
                            $filterClass = $rootCategory ? 'filter_category_' . $rootCategory->id : '';
                        @endphp
                        @include('pages.shop.product-card', [
                            'product'   => $product,
                            'colClass'  => ' product-item isotope-item ' . ($filterClass ? ' ' . $filterClass : ''),
                            'dataAttrs' => ['data-aos' => 'fade-up', 'data-aos-delay' => '100'],
                        ])
                    @endforeach

                </div>

                <div class="text-center mt-5" data-aos="fade-up">
                    <a href="{{module_url('shop')}}" class="view-all-btn">View All Products <i class="bi bi-arrow-right"></i></a>
                </div>

            </div>

        </section><!-- /Product List Section -->

    </main>
@endsection
