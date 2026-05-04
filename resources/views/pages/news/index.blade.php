@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>News</h1>

        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="card p-3">
                    <h5>{{ fake()->realText(50) }}</h5>
                    <p>{{ fake()->realText(200) }}</p>

                    <a href="#" class="btn btn-primary">
                        Read more
                    </a>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card p-3">
                    <h5>{{ fake()->realText(50) }}</h5>
                    <p>{{ fake()->realText(200) }}</p>

                    <a href="#" class="btn btn-primary">
                        Read more
                    </a>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card p-3">
                    <h5>{{ fake()->realText(50) }}</h5>
                    <p>{{ fake()->realText(200) }}</p>

                    <a href="#" class="btn btn-primary">
                        Read more
                    </a>
                </div>
            </div>
        </div>

    </div>
@endsection
