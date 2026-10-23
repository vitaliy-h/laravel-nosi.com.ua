<nav class="tabs"><span class="is-active">{{ t('auth.password_recovery') }}</span></nav>
@include('partials.alerts')

<form method="post" action="{{ route('password.recovery.store') }}" class="form-stack">
    @csrf
    <div class="field">
        <label for="recovery-email">{{ t('auth.email') }}</label>
        <input type="email" name="email" @class(['input', 'is-invalid' => $errors->has('email')]) id="recovery-email" value="{{ old('email') }}" autocomplete="email" required>
        @error('email')<div class="field-error">{{ $message }}</div>@enderror
    </div>

    <button type="submit" class="btn btn-dark btn-block">{{ t('buttons.request_password_recovery') }}</button>
    <a href="{{ save_route('auth.index') }}?login" class="btn btn-line btn-block"><i class="bi bi-arrow-left"></i> {{ t('auth.login') }}</a>
</form>
