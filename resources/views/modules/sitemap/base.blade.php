@php use App\Modules\Core\Support\UrlHelper; @endphp
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @foreach($pages as $page)
    <url>
        <loc>{{ url(UrlHelper::normalizeForLink($page->slug)) }}</loc>
        <lastmod>{{ $page->updated_at->toAtomString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    @endforeach
</urlset>
