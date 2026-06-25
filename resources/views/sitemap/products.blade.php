<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @foreach($items as $item)
    @if($item->category)
    <url>
        <loc>{{ module_url('shop', [$item->category->slug, $item->slug_with_id]) }}</loc>
        <lastmod>{{ $item->updated_at->toAtomString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.5</priority>
    </url>
    @endif
    @endforeach
</urlset>
