<section class="content-text">

    @if(!empty($data['title']))
        <h2>{{ $data['title'] }}</h2>
    @endif

    {!! str($data['content'])->sanitizeHtml() !!}

</section>
