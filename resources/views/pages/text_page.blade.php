@extends('layouts.app')

@section('content')
    @include('blocks.page_title')

    <div class="container mt-4">

        @include('components.builder', ['content' => $page->content])

    </div>
@endsection
