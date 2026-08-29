@extends('layouts.app')

@section('body-class', 'text-page')

@section('content')
    @include('blocks.page_title')

    <div class="container mt-4">

        @include('blocks.page_content')

    </div>
@endsection
