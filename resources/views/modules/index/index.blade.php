@extends('layouts.app')

@section('body-class', 'index-page')

@if($slides->isNotEmpty())
    @section('og_image', url($slides->first()->image))
@endif

@section('content')

    {{-- Hero slider (admin → Home page → Slider) --}}
    @if($slides->isNotEmpty())
        <section class="hero">
            <div class="container">
                <div class="hero-slides" id="hero">
                    @foreach($slides as $slide)
                        <div class="hero-slide{{ $loop->first ? ' is-active' : '' }}">
                            <img src="{{ $slide->image }}" alt="{{ $slide->title }}" @unless($loop->first) loading="lazy" @endunless>
                            <div class="hero-text">
                                @if($slide->kicker)
                                    <span class="kicker">{{ $slide->kicker }}</span>
                                @endif
                                @if($slide->title)
                                    @if($loop->first)
                                        <h1>{{ $slide->title }}</h1>
                                    @else
                                        <h2 class="h1">{{ $slide->title }}</h2>
                                    @endif
                                @endif
                                @if($slide->text)
                                    <p>{{ $slide->text }}</p>
                                @endif
                                @if($slide->button_text && $slide->url)
                                    <a href="{{ $slide->url }}" class="btn btn-lime">{{ $slide->button_text }} <i class="bi bi-arrow-right"></i></a>
                                @endif
                            </div>
                        </div>
                    @endforeach

                    @if($slides->count() > 1)
                        <div class="hero-dots" id="hero-dots" data-label="Слайд"></div>

                        <div class="hero-arrows">
                            <button class="round-btn" type="button" data-hero="prev" aria-label="{{ t('home.slide_prev') }}"><i class="bi bi-arrow-left"></i></button>
                            <button class="round-btn" type="button" data-hero="next" aria-label="{{ t('home.slide_next') }}"><i class="bi bi-arrow-right"></i></button>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    @endif



    {{-- Top sales / New: tabs + carousel --}}
    @if($top_products->isNotEmpty() || $new_products->isNotEmpty())
        @php $firstTab = $top_products->isNotEmpty() ? 'top' : 'new'; @endphp
        <section class="section" id="top">
            <div class="container">
                <div class="section-head tabs-head">
                    <h2 class="title">{{ t('home.top_title') }}</h2>
                    @if($top_products->isNotEmpty() && $new_products->isNotEmpty())
                        <div class="tabs" role="tablist">
                            <button type="button" class="is-active" role="tab" data-tab="top">{{ t('home.tab_top') }}</button>
                            <button type="button" role="tab" data-tab="new">{{ t('home.tab_new') }}</button>
                        </div>
                    @endif
                </div>

                <div class="carousel" data-carousel>
                    @if($top_products->isNotEmpty())
                        <div class="carousel-track" data-pane="top">
                            @foreach($top_products as $product)
                                <x-product-tile :product="$product" />
                            @endforeach
                        </div>
                    @endif
                    @if($new_products->isNotEmpty())
                        <div class="carousel-track" data-pane="new" @if($firstTab !== 'new') hidden @endif>
                            @foreach($new_products as $product)
                                <x-product-tile :product="$product" :new="true" />
                            @endforeach
                        </div>
                    @endif
                    @include('modules.index.partials.carousel_nav')
                </div>

                @if(t('home.catalog_link') !== '')
                    <div class="section-cta">
                        <a href="{{ t('home.catalog_url') ?: save_route('shop.index') }}" class="link-arrow">{{ t('home.catalog_link') }} <i class="bi bi-arrow-right"></i></a>
                    </div>
                @endif
            </div>
        </section>
    @endif

    {{-- Categories: carousel of up to 8 --}}
    @if($categories->isNotEmpty())
        <section class="section categories" id="categories">
            <div class="container">
                <div class="section-head">
                    <h2 class="title">{{ t('home.categories_title') }}</h2>
                    @if(t('home.categories_subtitle') !== '')
                        <p class="subtitle">{{ t('home.categories_subtitle') }}</p>
                    @endif
                </div>

                <div class="carousel cat-carousel" data-carousel>
                    <div class="carousel-track cat-track">
                        @foreach($categories->take(8) as $category)
                            <a href="{{ $category->url }}" class="cat-tile">
                                <img src="{{ $category->image }}" alt="{{ $category->title }}" loading="lazy">
                                <div class="cat-label"><h3>{{ $category->title }}</h3><span><i class="bi bi-arrow-right"></i></span></div>
                            </a>
                        @endforeach
                    </div>
                    @include('modules.index.partials.carousel_nav')
                </div>
            </div>
        </section>
    @endif

    {{-- Promo banners (admin → Home page → Promo banners): the grid follows how many there are --}}
    @if($promos->isNotEmpty())
        <section class="section" style="padding-bottom: 0;">
            <div class="container">
                <div class="promo-grid">
                    @foreach($promos as $promo)
                        <a href="{{ $promo->url ?? '#' }}" class="promo{{ $promo->style !== 'plum' ? ' ' . $promo->style : '' }}">
                            @if($promo->has_image)
                                <img src="{{ $promo->image }}" alt="{{ $promo->title }}" loading="lazy">
                            @endif
                            <div class="promo-body">
                                @if($promo->kicker)
                                    <span class="kicker">{{ $promo->kicker }}</span>
                                @endif
                                @if($promo->big_text)
                                    <div class="promo-big">{{ $promo->big_text }}</div>
                                @endif
                                @if($promo->title)
                                    <h2>{{ $promo->title }}</h2>
                                @endif
                                @if($promo->text)
                                    <p @if($promo->big_text && !$promo->title) style="margin-top: 14px;" @endif>{{ $promo->text }}</p>
                                @endif
                                @if($promo->button_text)
                                    <span class="btn {{ $promo->style === 'lime' ? 'btn-dark' : 'btn-white' }}">{{ $promo->button_text }} <i class="bi bi-arrow-right"></i></span>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Reviews of the shop (admin → Home page → Reviews). Shown with no reviews yet
         too, so the first customer can leave one through the button. --}}
    @if($reviews_enabled)
        <section class="section" id="reviews">
            <div class="container">
                <div class="reviews-head">
                    <div>
                        <h2 class="title">{{ t('home.reviews_title') }}</h2>
                        @if(t('home.reviews_subtitle') !== '')
                            <p class="subtitle">{{ t('home.reviews_subtitle') }}</p>
                        @endif
                    </div>
                    @if($rating_stars > 0)
                        <div class="rating-sum">
                            <strong>{{ $rating_value }}</strong>
                            <div>
                                <div class="stars"><x-rating-stars :value="$rating_stars" /></div>
                                <small>{{ t('home.reviews_count', ['count' => $reviews_total]) }}</small>
                            </div>
                        </div>
                    @endif
                </div>

                @if($reviews->isNotEmpty())
                    <div class="carousel" data-carousel>
                        <div class="carousel-track review-track">
                            @foreach($reviews as $review)
                                <article class="review">
                                    <div class="stars"><x-rating-stars :value="$review->rating" /></div>
                                    <p>{!! nl2br(e($review->text)) !!}</p>
                                    <div class="review-author">
                                        <span class="review-avatar">{{ $review->initial }}</span>
                                        <div><b>{{ $review->author }}</b>@if($review->city)<small>{{ $review->city }}</small>@endif</div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                        @include('modules.index.partials.carousel_nav')
                    </div>
                @endif

                <div class="section-cta">
                    <button type="button" class="link-arrow" data-dialog-open="review-dialog">{{ t('home.review_button') }} <i class="bi bi-arrow-right"></i></button>
                </div>
            </div>
        </section>

        @include('modules.index.partials.review_dialog')
    @endif

    {{-- Contacts: messenger cards (dictionary → contacts); a card with an empty value is skipped --}}
    @php
        $contactCards = collect([
            ['key' => 'telegram',  'class' => 'ca-tg', 'icon' => 'bi-telegram',        'go' => t('contacts.write')],
            ['key' => 'viber',     'class' => 'ca-vb', 'icon' => 'bi-chat-dots-fill',  'go' => t('contacts.write')],
            ['key' => 'instagram', 'class' => 'ca-ig', 'icon' => 'bi-instagram',       'go' => t('contacts.write')],
            ['key' => 'phone',     'class' => 'ca-ph', 'icon' => 'bi-telephone-fill',  'go' => t('contacts.call')],
        ])->filter(fn (array $card) => t("contacts.{$card['key']}") !== '');
        $contactInfo = collect([
            ['icon' => 'bi-clock', 'text' => t('contacts.hours')],
            ['icon' => 'bi-envelope', 'text' => t('contacts.email')],
            ['icon' => 'bi-geo-alt', 'text' => t('contacts.address')],
        ])->filter(fn (array $info) => $info['text'] !== '');
    @endphp
    @if($contactCards->isNotEmpty())
        <section class="section" id="contacts" style="padding-top: 0;">
            <div class="container">
                <div class="section-head">
                    <h2 class="title">{{ t('home.contacts_title') }}</h2>
                    @if(t('home.contacts_subtitle') !== '')
                        <p class="subtitle">{{ t('home.contacts_subtitle') }}</p>
                    @endif
                </div>
                <div class="ca-grid">
                    @foreach($contactCards as $card)
                        <a href="{{ t("contacts.{$card['key']}_url") ?: '#' }}" class="ca-card {{ $card['class'] }}" @if(str_starts_with(t("contacts.{$card['key']}_url"), 'http')) target="_blank" rel="noopener" @endif>
                            <span class="ico"><i class="bi {{ $card['icon'] }}"></i></span>
                            <small>{{ t("contacts.{$card['key']}_title") }}</small>
                            <strong>{{ t("contacts.{$card['key']}") }}</strong>
                            <span class="go">{{ $card['go'] }} <i class="bi bi-arrow-right"></i></span>
                        </a>
                    @endforeach
                </div>
                @if($contactInfo->isNotEmpty())
                    <div class="ca-info">
                        @foreach($contactInfo as $info)
                            <span><i class="bi {{ $info['icon'] }}"></i> {{ $info['text'] }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    @endif

    {{-- FAQ «Корисно знати» (admin → Home page → Good to know) --}}
    @if($faqs->isNotEmpty())
        <section class="section categories" id="faq">
            <div class="container">
                <div class="faq-grid">
                    <div class="faq-aside">
                        <h2 class="title">{{ t('home.faq_title') }}</h2>
                        @if(t('home.faq_text') !== '')
                            <p>{{ t('home.faq_text') }}</p>
                        @endif
                        @if(t('home.faq_button') !== '')
                            <a href="{{ t('home.faq_button_url') ?: '#contacts' }}" class="btn btn-dark">{{ t('home.faq_button') }}</a>
                        @endif
                    </div>

                    <div class="faq-list">
                        @foreach($faqs as $faq)
                            <details class="faq-item" @if($loop->first) open @endif>
                                <summary>{{ $faq->title }} <span class="faq-sign"></span></summary>
                                <div class="faq-answer">{!! $faq->text !!}</div>
                            </details>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- SEO text: text blocks of the home page (admin → Pages → Головна) --}}
    @php
        $seoBlocks = collect($page?->content ?? [])
            ->filter(fn ($block) => ($block['type'] ?? null) === 'text'
                && (!empty($block['data']['title']) || !empty($block['data']['content'])));
    @endphp
    @if($seoBlocks->isNotEmpty())
        <section class="section">
            <div class="container">
                <div class="seo-box" id="seo-box">
                    <div class="seo-cols">
                        @foreach($seoBlocks as $block)
                            @if(!empty($block['data']['title']))
                                <h2>{{ $block['data']['title'] }}</h2>
                            @endif
                            {!! str($block['data']['content'] ?? '')->sanitizeHtml() !!}
                        @endforeach
                    </div>
                </div>
                <button class="link-arrow seo-toggle" type="button" id="seo-toggle" data-more="{{ t('home.seo_more') }}" data-less="{{ t('home.seo_less') }}">{{ t('home.seo_more') }} <i class="bi bi-chevron-down"></i></button>
            </div>
        </section>
    @endif

    @php
        $benefits = collect(range(1, 4))
            ->map(fn (int $i) => [
                'icon'  => t("benefits.benefit_{$i}_icon"),
                'title' => t("benefits.benefit_{$i}_title"),
                'text'  => t("benefits.benefit_{$i}_text"),
            ])
            ->filter(fn (array $b) => $b['title'] !== '' && $b['text'] !== '');
    @endphp

    @if($benefits->isNotEmpty())
        <section class="benefits">
            <div class="container">
                @foreach($benefits as $benefit)
                    <div class="benefit"><i class="bi {{ $benefit['icon'] ?: 'bi-check2-circle' }}"></i><div><b>{{ $benefit['title'] }}</b><span>{{ $benefit['text'] }}</span></div></div>
                @endforeach
            </div>
        </section>
    @endif

@endsection
