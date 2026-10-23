{{-- Site footer of the Nosi design. Texts and contacts — dictionary (footer.*, contacts.*),
     «Каталог» — root categories (SiteChromeComposer), «Покупцям» — CMS pages marked for the footer menu. --}}
@php
    $socials = [
        'instagram' => 'Instagram',
        'telegram'  => 'Telegram',
        'facebook'  => 'Facebook',
        'tiktok'    => 'TikTok',
    ];
@endphp
<footer class="fa">
    <div class="container">
        <div class="fa-main">
            <div class="fa-about">
                <a href="{{ save_route('index.index') }}" class="logo">{{ t('header.logo') }}<b>.</b></a>
                @if(t('footer.about') !== '')
                    <p>{{ t('footer.about') }}</p>
                @endif
                <div class="socials">
                    @foreach($socials as $key => $label)
                        @if(t("contacts.social_{$key}") !== '')
                            <a href="{{ t("contacts.social_{$key}") }}" target="_blank" rel="noopener" aria-label="{{ $label }}"><i class="bi bi-{{ $key }}"></i></a>
                        @endif
                    @endforeach
                </div>
            </div>

            @if(!empty($footer_categories) && $footer_categories->isNotEmpty())
                <div>
                    <h4 class="f-h">{{ t('footer.catalog_title') }}</h4>
                    <ul class="f-links">
                        @foreach($footer_categories as $category)
                            <li><a href="{{ $category->url }}" @class(['is-active' => is_active_url($category->url)])>{{ $category->title }}</a></li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div>
                <h4 class="f-h">{{ t('footer.buyers_title') }}</h4>
                <ul class="f-links">
                    @foreach($footer_menu ?? [] as $item)
                        <li><a href="{{ $item->url }}" @class(['is-active' => is_active_url($item->url)])>{{ $item->title }}</a></li>
                    @endforeach
                    @if(t('footer.faq_link') !== '')
                        <li><a href="{{ save_route('index.index') }}#faq">{{ t('footer.faq_link') }}</a></li>
                    @endif
                </ul>
            </div>

            <div>
                <h4 class="f-h">{{ t('footer.contacts_title') }}</h4>
                <ul class="f-links">
                    @if(t('contacts.phone') !== '')
                        <li><a href="{{ t('contacts.phone_url') ?: '#' }}">{{ t('contacts.phone') }}</a></li>
                    @endif
                    @if(t('contacts.email') !== '')
                        <li><a href="mailto:{{ t('contacts.email') }}">{{ t('contacts.email') }}</a></li>
                    @endif
                    @if(t('contacts.hours') !== '')
                        <li>{{ t('contacts.hours') }}</li>
                    @endif
                    @if(t('contacts.address_short') !== '')
                        <li>{{ t('contacts.address_short') }}</li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
    <div class="fa-bottom">
        <div class="container">
            <div class="pay">{{ t('footer.copyright', ['year' => date('Y')]) }}</div>
            @if(t('footer.privacy') !== '')
                <span><a href="{{ t('footer.privacy_url') ?: '#' }}">{{ t('footer.privacy') }}</a></span>
            @endif

            <nav class="f-legal">
                <p class="site-legal footer-credit">
                    {{ t('footer.developer') }}
                    <a href="https://ua.devep.pro/" target="_blank" rel="noopener"><img src="https://flowersmakeyourday.com/assets/images/logo-devep.svg?v=1791960407" class="footer-credit-logo" width="171" height="36" alt="devep.pro" loading="lazy" decoding="async"></a>
                </p>
            </nav>
        </div>
    </div>
</footer>
