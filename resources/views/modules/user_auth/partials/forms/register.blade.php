@include('modules.user_auth.partials.login_register_nav')
@include('partials.alerts')

<form method="post" action="{{ route('register.store') }}" class="form-stack">
    @csrf
    <div class="form-grid">
        <div class="field">
            <label for="reg-name">{{ t('profile.user_name') }}</label>
            <input type="text" name="name" @class(['input', 'is-invalid' => $errors->has('name')]) id="reg-name" value="{{ old('name') }}" autocomplete="given-name" required>
            @error('name')<div class="field-error">{{ $message }}</div>@enderror
        </div>
        <div class="field">
            <label for="reg-surname">{{ t('profile.user_surname') }}</label>
            <input type="text" name="surname" @class(['input', 'is-invalid' => $errors->has('surname')]) id="reg-surname" value="{{ old('surname') }}" autocomplete="family-name" required>
            @error('surname')<div class="field-error">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="field">
        <label for="reg-email">{{ t('profile.user_email') }}</label>
        <input type="email" name="email" @class(['input', 'is-invalid' => $errors->has('email')]) id="reg-email" value="{{ old('email') }}" autocomplete="email" required>
        @error('email')<div class="field-error">{{ $message }}</div>@enderror
    </div>

    <div class="field">
        <label for="reg-phone">{{ t('profile.user_phone') }}</label>
        <input type="tel" name="phone" @class(['input', 'is-invalid' => $errors->has('phone')]) id="reg-phone" value="{{ old('phone') }}" autocomplete="tel" required>
        @error('phone')<div class="field-error">{{ $message }}</div>@enderror
    </div>

    <div class="form-grid">
        <div class="field">
            <label for="reg-password">{{ t('auth.password') }}</label>
            <input type="password" name="password" @class(['input', 'is-invalid' => $errors->has('password')]) id="reg-password" autocomplete="new-password" required>
            @error('password')<div class="field-error">{{ $message }}</div>@enderror
        </div>
        <div class="field">
            <label for="reg-password-confirm">{{ t('auth.password_confirmation') }}</label>
            <input type="password" name="password_confirmation" class="input" id="reg-password-confirm" autocomplete="new-password" required>
        </div>
    </div>

    <div class="field">
        <label class="check">
            <input type="checkbox" name="terms" value="1" required>
            <span>{!! t('auth.terms') !!}</span>
        </label>
        @error('terms')<div class="field-error">{{ $message }}</div>@enderror
    </div>

    <button type="submit" class="btn btn-dark btn-block">{{ t('buttons.create_acc') }}</button>
</form>

@include('modules.user_auth.partials.social_buttons')
