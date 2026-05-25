@include('pages.auth.tabs.login_register')
@include('blocks.alerts')

<form method="post" action="{{route('register.store')}}">
    @csrf
    <div class="row g-3">
        <div class="col-12">
            <div class="mb-4">
                <label for="login-register-reg-firstname" class="form-label">{{t('auth.name')}}</label>
                <input type="text" name="name" class="form-control" id="login-register-reg-firstname" value="{{ old('name') }}" required>
                @error('name')
                <div class="text-danger small mt-1">
                    {{ $message }}
                </div>
                @enderror
            </div>
        </div>

        <div class="col-12">
            <div class="mb-4">
                <label for="login-register-reg-email" class="form-label">{{t('auth.email')}}</label>
                <input type="email" name="email" class="form-control" id="login-register-reg-email" value="{{ old('email') }}" required>
                @error('email')
                <div class="text-danger small mt-1">
                    {{ $message }}
                </div>
                @enderror
            </div>
        </div>

        <div class="col-12">
            <div class="mb-4">
                <label for="login-register-reg-password" class="form-label">{{t('auth.password')}}</label>
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

        <div class="col-12">
            <div class="form-check">
                <input type="checkbox" name="terms" value="1" class="form-check-input" id="login-register-terms" required>
                <label class="form-check-label" for="login-register-terms">
                    {!! t('auth.terms') !!}
                </label>
                @error('terms')
                <div class="text-danger small mt-1">
                    {{ $message }}
                </div>
                @enderror
            </div>
        </div>

        <div class="col-12">
            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg">{{t('buttons.create_acc')}}</button>
            </div>
        </div>
    </div>
</form>
