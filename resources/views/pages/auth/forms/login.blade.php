@include('pages.auth.tabs.login_register')
@include('blocks.alerts')

<form method="post" action="{{route('login.store')}}">
    @csrf
    <div class="mb-4">
        <label for="login-register-login-email" class="form-label">{{t('auth.email')}}</label>
        <input type="email" name="email" class="form-control" id="login-register-login-email"  value="{{ old('email') }}" required>
        @error('email')
        <div class="text-danger small mt-1">
            {{ $message }}
        </div>
        @enderror
    </div>

    <div class="mb-4">
        <label for="login-register-login-password" class="form-label">{{t('auth.password')}}</label>
        <input type="password" name="password" class="form-control" id="login-register-login-password" required>
        @error('password')
        <div class="text-danger small mt-1">
            {{ $message }}
        </div>
        @enderror
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="form-check">
            <input type="checkbox" name="remember_me" class="form-check-input" id="login-register-remember-me" value="1">
            <label class="form-check-label" for="login-register-remember-me">{{t('auth.remember_me')}}</label>
        </div>
        <a href="{{module_url(\App\Modules\Core\Enums\PageModule::AUTH,[],['password_recovery'=>null])}}" class="forgot-password">{{t('auth.forgot_password')}}</a>
    </div>

    <div class="d-grid">
        <button type="submit" class="btn btn-primary btn-lg">{{t('buttons.login')}}</button>
    </div>
</form>
