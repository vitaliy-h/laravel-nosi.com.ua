{{-- Site header of the Nosi design: logo left, menu in the middle, profile and cart right.
     Menu — categories (with a dropdown of their subcategories), then CMS pages marked for
     the header menu (PageContextSharer). The item of the current page gets `.is-active`.
     Desktop opens a dropdown on hover/focus, the chevron opens it on touch (nosi.js).
     Up to 1024px the inline menu is hidden and the burger opens `#m-menu` — a drawer from the
     left: a category with subcategories gets an arrow to the side and opens its own panel
     with the list of its subcategories. --}}
<header class="header">
    <div class="container header-bar">
        <button class="icon-btn menu-toggle" id="menu-toggle" type="button" aria-label="{{ t('header.menu') }}" aria-controls="m-menu" aria-expanded="false"><i class="bi bi-list"></i></button>

        <a href="{{ save_route('index.index') }}" class="logo">{{ t('header.logo') }}<b>.</b></a>

        <nav class="nav" id="nav">
            @foreach($header_menu ?? [] as $item)
                @if($item->children)
                    <div @class(['nav-item', 'is-active' => $item->is_active])>
                        <a href="{{ $item->url }}" @class(['is-active' => $item->is_active]) @if($item->is_active) aria-current="page" @endif>{{ $item->title }}</a>
                        <button class="nav-sub-toggle" type="button" aria-expanded="false" aria-label="{{ $item->title }}"><i class="bi bi-chevron-down"></i></button>
                        <div class="nav-sub">
                            @foreach($item->children as $child)
                                <a href="{{ $child->url }}" @class(['is-active' => $child->is_active]) @if($child->is_active) aria-current="page" @endif>{{ $child->title }}</a>
                            @endforeach
                        </div>
                    </div>
                @else
                    <a href="{{ $item->url }}" @class(['is-active' => $item->is_active]) @if($item->is_active) aria-current="page" @endif>{{ $item->title }}</a>
                @endif
            @endforeach
        </nav>

        <div class="header-actions">
            <a class="icon-btn hide-sm" href="{{ save_route('profile.index') }}" aria-label="{{ t('header.account') }}"><i class="bi bi-person"></i></a>
            <a class="cart-pill" href="{{ save_route('cart.index') }}" aria-label="{{ t('header.cart') }}"><span class="label_cart">{{ t('header.cart') }}</span><span class="count js_cart_count">{{ $cart_count ?? 0 }}</span></a>
        </div>
    </div>
</header>

<div class="m-menu" id="m-menu" aria-hidden="true">
    <div class="m-menu-backdrop" data-m-menu-close></div>

    <div class="m-menu-drawer" role="dialog" aria-modal="true" aria-label="{{ t('header.menu') }}">
        <div class="m-menu-panel is-current" id="m-menu-root">
            <div class="m-menu-head">
                <a href="{{ save_route('index.index') }}" class="logo">{{ t('header.logo') }}<b>.</b></a>
                <button class="icon-btn" type="button" data-m-menu-close aria-label="{{ t('header.menu') }}"><i class="bi bi-x-lg"></i></button>
            </div>

            <nav class="m-menu-body m-menu-list">
                @foreach($header_menu ?? [] as $item)
                    @if($item->children)
                        <button type="button" @class(['m-menu-link', 'is-active' => $item->is_active]) data-m-menu-open="m-menu-sub-{{ $loop->index }}" aria-controls="m-menu-sub-{{ $loop->index }}">
                            <span>{{ $item->title }}</span><i class="bi bi-chevron-right"></i>
                        </button>
                    @else
                        <a href="{{ $item->url }}" @class(['m-menu-link', 'is-active' => $item->is_active]) @if($item->is_active) aria-current="page" @endif><span>{{ $item->title }}</span></a>
                    @endif
                @endforeach

                <a href="{{ save_route('profile.index') }}" class="m-menu-link m-menu-link--extra"><span><i class="bi bi-person"></i> {{ t('header.account') }}</span></a>
            </nav>
        </div>

        @foreach($header_menu ?? [] as $item)
            @if($item->children)
                <div class="m-menu-panel" id="m-menu-sub-{{ $loop->index }}">
                    <div class="m-menu-head">
                        <button class="m-menu-back" type="button" data-m-menu-back><i class="bi bi-chevron-left"></i> {{ t('header.back') }}</button>
                        <button class="icon-btn" type="button" data-m-menu-close aria-label="{{ t('header.menu') }}"><i class="bi bi-x-lg"></i></button>
                    </div>

                    @php($is_category_page = $item->is_active && !collect($item->children)->contains('is_active', true))
                    <nav class="m-menu-body m-menu-list">
                        @foreach($item->children as $child)
                            <a href="{{ $child->url }}" @class(['m-menu-link', 'is-active' => $child->is_active]) @if($child->is_active) aria-current="page" @endif><span>{{ $child->title }}</span></a>
                        @endforeach

                        <a href="{{ $item->url }}" @class(['m-menu-link', 'm-menu-link--all', 'is-active' => $is_category_page]) @if($is_category_page) aria-current="page" @endif><span>{{ t('header.all_in_category') }}</span><i class="bi bi-arrow-right"></i></a>
                    </nav>
                </div>
            @endif
        @endforeach
    </div>
</div>
