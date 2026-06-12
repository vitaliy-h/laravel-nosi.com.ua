<section class="content-text">

    @if(!empty($block['data']['title']))
        <h2>{{ $block['data']['title'] }}</h2>
    @endif

    {!! str($block['data']['content'])->sanitizeHtml() !!}

</section>
