<div id="form-user-password">
@include('partials.alerts', [
        'successKey' => 'password_change_success',
        'errorKey' => 'password_change_error',
    ])

<form class="settings-form" data-abcr="{{route('profile.update.user.password')}}" action="{{route('profile.update.user.password')}}" method="POST">
    @csrf
    <div class="row g-3">
        <div class="col-md-12">
            <label for="currentPassword" class="form-label">{{t('auth.current_password')}}</label>
            <input type="password" name="current_password" class="form-control" id="currentPassword"
                   required>
            @error('current_password')
                <div class="text-danger small mt-1">
                    {{ $message }}
                </div>
            @enderror
        </div>
        <div class="col-md-6">
            <label for="newPassword" class="form-label">{{t('auth.new_password')}}</label>
            <input type="password" name="password" class="form-control" id="newPassword" required>
            @error('password')
                <div class="text-danger small mt-1">
                    {{ $message }}
                </div>
            @enderror
        </div>
        <div class="col-md-6">
            <label for="confirmPassword" class="form-label">{{t('auth.password_confirmation')}}</label>
            <input type="password" name="password_confirmation" class="form-control" id="confirmPassword"
                   required>
            @error('password_confirmation')
                <div class="text-danger small mt-1">
                    {{ $message }}
                </div>
            @enderror
        </div>
    </div>

    <div class="form-buttons">
        <button type="submit" class="btn-save">{{t('buttons.update_password')}}</button>
    </div>
</form>
</div>
