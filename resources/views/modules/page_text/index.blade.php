@extends('layouts.app')

@section('body-class', 'text-page')

@section('content')
    @include('partials.page_title')

    <div class="container page">
        <div class="page-narrow">
            @include('partials.page_content')
        </div>
    </div>
@endsection
