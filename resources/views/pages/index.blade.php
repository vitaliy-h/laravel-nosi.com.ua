@extends('layouts.app')

@section('body-class', 'index-page')

@section('content')
    <main class="main">

        @include('blocks.hero', [
                    'title' => t('index.hero_title'),
                    'subtitle' => t('index.hero_subtitle')
                ])

        @include('components.builder', ['content' => $page->content])

        @include('pages.shop.category-cards', ['specialCategories' => $specialCategories])

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

            <div class="container isotope-layout" data-aos="fade-up" data-aos-delay="100" data-default-filter="*"
                 data-layout="masonry" data-sort="original-order">

                <div class="row">
                    <div class="col-12">
                        <div class="product-filters isotope-filters mb-5 d-flex justify-content-center"
                             data-aos="fade-up">
                            <ul class="d-flex flex-wrap gap-2 list-unstyled">
                                <li class="filter-active" data-filter="*">All</li>
                                @foreach($featuredCategories as $category)
                                    <li data-filter=".filter_category_{{ $category->id }}">{{ $category->title }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="row product-container isotope-container best-sellers" data-aos="fade-up"
                     data-aos-delay="200">

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
                    <a href="{{save_route('shop.index')}}" class="view-all-btn">View All Products <i
                            class="bi bi-arrow-right"></i></a>
                </div>

            </div>

        </section><!-- /Product List Section -->

    </main>
@endsection
