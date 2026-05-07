@extends('layouts.app')

@section('content')
    <div class="container">

        @include('blocks.hero', [
            'title' => 'Welcome',
            'subtitle' => 'Starter project on Laravel'
        ])

        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card p-3">
                    <h5>Fast</h5>
                    <p>Laravel + Filament starter</p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card p-3">
                    <h5>Flexible</h5>
                    <p>Modular architecture</p>
                </div>
            </div>
        </div>

    </div>
@endsection
