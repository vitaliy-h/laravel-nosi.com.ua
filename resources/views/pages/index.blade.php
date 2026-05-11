@extends('layouts.app')

@section('content')
    <div class="container">

        @include('blocks.hero', [
            'title' => t('index.hero_title'),
            'subtitle' => t('index.hero_subtitle')
        ])

        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card p-3">
                    <h5>{!! t('index.first_block_title') !!}</h5>
                    <p>{!! t('index.first_block_subtitle') !!}</p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card p-3">
                    <h5>{!! t('index.second_block_title') !!}</h5>
                    <p>{!! t('index.second_block_subtitle') !!}</p>
                </div>
            </div>
        </div>

    </div>
@endsection
