<ul class="nav nav-tabs nav-tabs-bordered justify-content-center mb-4" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active">
            <i class="bi bi-shield-lock-fill me-1"></i>{{t('auth.password_recovery')}}
        </button>
    </li>
</ul>
@include('blocks.alerts')
<form method="post" action="{{route('password.reset.store')}}">
    @csrf
    <input
        type="hidden"
        name="token"
        value="{{ $token }}"
    >
    <div class="mb-4">
        <label for="login-register-login-email" class="form-label">{{t('auth.email')}}</label>
        <input type="email" name="email" class="form-control" id="login-register-login-email"  value="{{ $email }}" required>
        @error('email')
        <div class="text-danger small mt-1">
            {{ $message }}
        </div>
        @enderror
    </div>
    <div class="col-12">
        <div class="mb-4">
            <label for="login-register-reg-password" class="form-label">{{t('auth.new_password')}}</label>
            <input type="password" name="password" class="form-control" id="login-register-reg-password" required>
            @error('password')
            <div class="text-danger small mt-1">
                {{ $message }}
            </div>
            @enderror
        </div>
    </div>

    <div class="col-12">
        <div class="mb-4">
            <label for="login-register-reg-confirm-password" class="form-label">{{t('auth.password_confirmation')}}</label>
            <input type="password" name="password_confirmation" class="form-control" id="login-register-reg-confirm-password" required>
        </div>
    </div>

    <div class="d-grid">
        <button type="submit" class="btn btn-primary btn-lg">{{t('buttons.request_password_recovery')}}</button>
    </div>
</form>
