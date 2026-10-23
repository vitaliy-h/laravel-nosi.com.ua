{{-- Site header of the Nosi design. Menu items come from the dictionary (header.*):
     an item with an empty title is hidden; the item of the current page gets `.is-active`. --}}
<header class="header">
    <div class="container">
        <button class="icon-btn menu-toggle" id="menu-toggle" type="button" aria-label="{{ t('header.menu') }}" aria-expanded="false"><i class="bi bi-list"></i></button>

        <nav class="nav" id="nav">
            @foreach(['menu_1', 'menu_2', 'menu_3'] as $item)
                @if(t("header.{$item}") !== '')
                    @php($url = t("header.{$item}_url") ?: '#')
                    <a href="{{ $url }}" @class(['is-active' => is_active_url($url)]) @if(is_active_url($url)) aria-current="page" @endif>{{ t("header.{$item}") }}</a>
                @endif
            @endforeach
            @if(t('header.menu_sale') !== '')
                @php($url = t('header.menu_sale_url') ?: '#')
                <a href="{{ $url }}" @class(['is-sale', 'is-active' => is_active_url($url)]) @if(is_active_url($url)) aria-current="page" @endif>{{ t('header.menu_sale') }}</a>
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
