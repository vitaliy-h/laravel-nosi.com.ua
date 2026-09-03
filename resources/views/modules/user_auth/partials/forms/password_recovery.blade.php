<ul class="nav nav-tabs nav-tabs-bordered justify-content-center mb-4" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active">
            <i class="bi bi-shield-lock-fill me-1"></i>{{t('auth.password_recovery')}}
        </button>
    </li>
</ul>
@include('partials.alerts')
<form method="post" action="{{route('password.recovery.store')}}">
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

    <div class="d-grid">
        <button type="submit" class="btn btn-primary btn-lg">{{t('buttons.request_password_recovery')}}</button>
    </div>
</form>
