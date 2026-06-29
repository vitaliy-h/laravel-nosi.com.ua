<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <sitemap>
        <loc>{{ url('/sitemap-base.xml') }}</loc>
    </sitemap>
    @for($i = 1; $i <= $newsTotalPages; $i++)
    <sitemap>
        <loc>{{ url('/sitemap-news-' . $i . '.xml') }}</loc>
    </sitemap>
    @endfor
    @for($i = 1; $i <= $categoriesTotalPages; $i++)
    <sitemap>
        <loc>{{ url('/sitemap-categories-' . $i . '.xml') }}</loc>
    </sitemap>
    @endfor
    @for($i = 1; $i <= $productsTotalPages; $i++)
    <sitemap>
        <loc>{{ url('/sitemap-products-' . $i . '.xml') }}</loc>
    </sitemap>
    @endfor
</sitemapindex>
