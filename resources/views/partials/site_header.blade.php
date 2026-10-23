{{-- Site header of the Nosi design. Menu — categories, then CMS pages marked for the header
     menu (PageContextSharer); the highlighted «Розпродаж» item comes from the dictionary
     (header.menu_sale) and is hidden when its title is empty or nothing is on sale
     (has_sale, SiteChromeComposer). --}}
<header class="header">
    <div class="container">
        <button class="icon-btn menu-toggle" id="menu-toggle" type="button" aria-label="{{ t('header.menu') }}" aria-expanded="false"><i class="bi bi-list"></i></button>

        <nav class="nav" id="nav">
            @foreach($header_menu ?? [] as $item)
                <a href="{{ $item->url }}" @class(['is-active' => $item->is_active])>{{ $item->title }}</a>
            @endforeach
            @if(($has_sale ?? false) && t('header.menu_sale') !== '')
                <a href="{{ t('header.menu_sale_url') ?: '#' }}" class="is-sale">{{ t('header.menu_sale') }}</a>
            @endif
        </nav>

        <a href="{{ save_route('index.index') }}" class="logo">{{ t('header.logo') }}<b>.</b></a>

        <div class="header-actions">
            <a class="icon-btn hide-sm" href="{{ save_route('shop.index') }}" aria-label="{{ t('header.search') }}"><i class="bi bi-search"></i></a>
            <a class="icon-btn hide-sm" href="{{ save_route('profile.index') }}" aria-label="{{ t('header.account') }}"><i class="bi bi-person"></i></a>
            <a class="cart-pill" href="{{ save_route('cart.index') }}" aria-label="{{ t('header.cart') }}"><span class="label_cart">{{ t('header.cart') }}</span><span class="count js_cart_count">{{ $cart_count ?? 0 }}</span></a>
        </div>
    </div>
</header>
