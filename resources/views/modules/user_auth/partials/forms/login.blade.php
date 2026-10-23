@include('modules.user_auth.partials.login_register_nav')
@include('partials.alerts')

<form method="post" action="{{ route('login.store') }}" class="form-stack">
    @csrf
    <div class="field">
        <label for="login-email">{{ t('auth.email') }}</label>
        <input type="text" name="email" @class(['input', 'is-invalid' => $errors->has('email')]) id="login-email" value="{{ old('email') }}" autocomplete="username" required>
        @error('email')<div class="field-error">{{ $message }}</div>@enderror
    </div>

    <div class="field">
        <label for="login-password">{{ t('auth.password') }}</label>
        <input type="password" name="password" @class(['input', 'is-invalid' => $errors->has('password')]) id="login-password" autocomplete="current-password" required>
        @error('password')<div class="field-error">{{ $message }}</div>@enderror
    </div>

    <div class="auth-row">
        <label class="check"><input type="checkbox" name="remember_me" value="1"> {{ t('auth.remember_me') }}</label>
        <a href="{{ save_route('auth.index') }}?password_recovery" class="link">{{ t('auth.forgot_password') }}</a>
    </div>

    <button type="submit" class="btn btn-dark btn-block">{{ t('buttons.login') }}</button>
</form>

@include('modules.user_auth.partials.social_buttons')
