{{-- Running line (admin → Home page → Running line), `$site_announces` from
     SiteChromeComposer. The phrases are rendered twice:
     the marquee scrolls by half its width and loops seamlessly. --}}
@if(!empty($site_announces) && $site_announces->isNotEmpty())
    <div class="announce">
        <div class="announce-track">
            @for($copy = 0; $copy < 2; $copy++)
                @foreach($site_announces as $announce)
                    @if($announce->url)
                        <a href="{{ $announce->url }}"><span>{{ $announce->title }}</span></a>
                    @else
                        <span>{{ $announce->title }}</span>
                    @endif
                @endforeach
            @endfor
        </div>
    </div>
@endif
