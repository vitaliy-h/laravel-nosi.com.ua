{{-- Site header of the Nosi design: logo left, menu in the middle, profile and cart right.
     Menu — categories (with a dropdown of their subcategories), then CMS pages marked for
     the header menu (PageContextSharer). The item of the current page gets `.is-active`.
     Desktop opens a dropdown on hover/focus, the chevron opens it on touch and in the
     mobile panel (nosi.js). --}}
<header class="header">
    <div class="container header-bar">
        <button class="icon-btn menu-toggle" id="menu-toggle" type="button" aria-label="{{ t('header.menu') }}" aria-expanded="false"><i class="bi bi-list"></i></button>

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