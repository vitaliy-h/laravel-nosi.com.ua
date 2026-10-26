@extends('layouts.app')

@section('body-class', 'contact-page')

@section('content')
    @include('partials.page_title')

    <div class="container page">
        <div class="two-col">
            <div>
                @include('partials.page_content')

                <ul class="pd-perks" style="margin-top: 24px;">
                    @foreach(['phone' => 'bi-telephone', 'email' => 'bi-envelope', 'hours' => 'bi-clock', 'address' => 'bi-geo-alt'] as $key => $icon)
                        @if(t("contacts.{$key}") !== '')
                            <li><i class="bi {{ $icon }}"></i> {{ t("contacts.{$key}") }}</li>
                        @endif
                    @endforeach
                </ul>
            </div>

            <div class="panel-white">
                @include('partials.alerts')

                <form method="POST" action="{{ route('feedback.store') }}" class="form-stack">
                    @csrf
                    <div class="form-grid">
                        <div class="field">
                            <label for="feedback-name">{{ __('site.feedback.name') }}</label>
                            <input type="text" name="name" id="feedback-name" @class(['input', 'is-invalid' => $errors->has('name')]) value="{{ old('name') }}" autocomplete="name" required>
                            @error('name')<div class="field-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="field">
                            <label for="feedback-phone">{{ __('site.feedback.phone') }}</label>
                            <input type="tel" name="phone" id="feedback-phone" @class(['input', 'is-invalid' => $errors->has('phone')]) value="{{ old('phone') }}" autocomplete="tel">
                            @error('phone')<div class="field-error">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="field">
                        <label for="feedback-email">{{ __('site.feedback.email') }}</label>
                        <input type="email" name="email" id="feedback-email" @class(['input', 'is-invalid' => $errors->has('email')]) value="{{ old('email') }}" autocomplete="email" required>
                        @error('email')<div class="field-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="field">
                        <label for="feedback-message">{{ __('site.feedback.message') }}</label>
                        <textarea name="message" id="feedback-message" @class(['input', 'is-invalid' => $errors->has('message')]) rows="5" required>{{ old('message') }}</textarea>
                        @error('message')<div class="field-error">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="btn btn-dark">{{ t('buttons.send') }}</button>
                </form>
            </div>
        </div>
    </div>
@endsection
