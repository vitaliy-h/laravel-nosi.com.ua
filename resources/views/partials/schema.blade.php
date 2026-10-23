{{-- schema.org markup of every page (JSON-LD): the shop as an Organization and the WebSite.
     Name, contacts and socials come from the dictionary (header.logo, contacts.*). Pages add
     their own blocks with @push('schema') — product, news, breadcrumbs (partials.page_title). --}}
@php
    $sameAs = collect(['instagram', 'telegram', 'facebook', 'tiktok'])
        ->map(fn (string $key) => t("contacts.social_{$key}"))
        ->filter()
        ->values()
        ->all();

    $contactPoint = array_filter([
        '@type'       => 'ContactPoint',
        'contactType' => 'customer service',
        'telephone'   => t('contacts.phone') ?: null,
        'email'       => t('contacts.email') ?: null,
    ]);

    $schema = [
        '@context' => 'https://schema.org',
        '@graph'   => [
            array_filter([
                '@type'        => 'Organization',
                '@id'          => url('/') . '#organization',
                'name'         => t('header.logo') ?: config('app.name'),
                'url'          => url('/'),
                'logo'         => asset('apple-touch-icon.png'),
                'sameAs'       => $sameAs ?: null,
                'contactPoint' => count($contactPoint) > 2 ? $contactPoint : null,
            ]),
            [
                '@type'      => 'WebSite',
                '@id'        => url('/') . '#website',
                'name'       => t('header.logo') ?: config('app.name'),
                'url'        => url('/'),
                'inLanguage' => app()->getLocale(),
                'publisher'  => ['@id' => url('/') . '#organization'],
            ],
        ],
    ];
@endphp
<script type="application/ld+json">@json($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG)</script>
