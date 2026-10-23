@extends('layouts.app')

@section('body-class', 'account-page')

@section('content')
    <div class="container page">
        <div class="account">
            @include('modules.profile.partials.left_menu')

            <div class="account-content">
                @yield('profile_content')
            </div>
        </div>
    </div>
@endsection
