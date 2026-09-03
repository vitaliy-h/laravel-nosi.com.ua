@extends('layouts.app')

@section('body-class', 'contact-page')

@section('content')
    @include('partials.page_title')

    <div class="container mt-4">
        @include('partials.alerts')

        <div class="row g-4">
            <div class="col-lg-6">
                @include('partials.page_content')
            </div>

            <div class="col-lg-6">
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
                        <label class="form-label">{{t('feedback.phone')}}</label>
                        <input type="tel" name="phone" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{t('feedback.message')}}</label>
                        <textarea name="message" class="form-control" required></textarea>
                    </div>

                    <button class="btn btn-success">{{t('buttons.send')}}</button>
                </form>
            </div>
        </div>
    </div>
@endsection
