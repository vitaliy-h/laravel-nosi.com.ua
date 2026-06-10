@extends('layouts.app')

@section('content')
    @include('blocks.page_title')

    <div class="container mt-4">

        <div>
            {!! $page->text !!}

            @foreach($page->content as $block)
                @includeIf('blocks.' . $block['type'], [
                    'data' => $block['data']
                ])
            @endforeach
        </div>
    </div>
@endsection
