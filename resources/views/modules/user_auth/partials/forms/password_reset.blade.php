<nav class="tabs"><span class="is-active">{{ t('auth.password_recovery') }}</span></nav>
@include('partials.alerts')

<form method="post" action="{{ route('password.reset.store') }}" class="form-stack">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">

    <div class="field">
        <label for="reset-email">{{ t('auth.email') }}</label>
        <input type="email" name="email" @class(['input', 'is-invalid' => $errors->has('email')]) id="reset-email" value="{{ $email }}" autocomplete="email" required>
        @error('email')<div class="field-error">{{ $message }}</div>@enderror
    </div>

    <div class="field">
        <label for="reset-password">{{ t('auth.new_password') }}</label>
        <input type="password" name="password" @class(['input', 'is-invalid' => $errors->has('password')]) id="reset-password" autocomplete="new-password" required>
        @error('password')<div class="field-error">{{ $message }}</div>@enderror
    </div>

    <div class="field">
        <label for="reset-password-confirm">{{ t('auth.password_confirmation') }}</label>
        <input type="password" name="password_confirmation" class="input" id="reset-password-confirm" autocomplete="new-password" required>
    </div>

    <button type="submit" class="btn btn-dark btn-block">{{ t('buttons.save') }}</button>
</form>
