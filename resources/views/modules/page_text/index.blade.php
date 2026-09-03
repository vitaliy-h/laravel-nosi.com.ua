@extends('layouts.app')

@section('body-class', 'text-page')

@section('content')
    @include('partials.page_title')

    <div class="container mt-4">

        @include('partials.page_content')

    </div>
@endsection
