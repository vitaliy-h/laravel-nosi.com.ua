@extends('layouts.app')

@section('content')
    <div class="container">
        @include('blocks.alerts')
        <h1>{{ $page->title }}</h1>

        <form method="POST" action="{{ route('feedback.save') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">{{t('feedback.name')}}</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">{{t('feedback.message')}}</label>
                <textarea name="message" class="form-control" required></textarea>
            </div>

            <button class="btn btn-success">{{t('buttons.send')}}</button>
        </form>
    </div>
@endsection
