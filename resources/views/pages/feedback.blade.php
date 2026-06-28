@extends('layouts.app')

@section('body-class', 'contact-page')

@section('content')
    @include('blocks.page_title')

    <div class="container mt-4">
        @include('blocks.alerts')

        <form method="POST" action="{{ route('feedback.store') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">{{t('feedback.name')}}</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">{{t('feedback.email')}}</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">{{t('feedback.message')}}</label>
                <textarea name="message" class="form-control" required></textarea>
            </div>

            <button class="btn btn-success">{{t('buttons.send')}}</button>
        </form>
    </div>
@endsection
