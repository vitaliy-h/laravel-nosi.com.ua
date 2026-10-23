@extends('layouts.app')

@section('body-class', 'auth-page')

@section('content')
    @include('partials.page_title')

    <div class="container page">
        <div class="auth">
            <div class="panel-white">
                @include($form_view)
            </div>
        </div>
    </div>
@endsection
