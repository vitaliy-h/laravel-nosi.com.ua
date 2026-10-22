{{-- Running line (admin → Home page → Running line). The phrases are rendered twice:
     the marquee scrolls by half its width and loops seamlessly. --}}
@if(!empty($announces) && $announces->isNotEmpty())
    <div class="announce">
        <div class="announce-track">
            @for($copy = 0; $copy < 2; $copy++)
                @foreach($announces as $announce)
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
