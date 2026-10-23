{{-- Re-rendered by ProfileController after an abc-response submit, keep the wrapper id. --}}
<div id="form-user-password">
    @include('partials.alerts', [
        'successKey' => 'password_change_success',
        'errorKey' => 'password_change_error',
    ])

    <form data-abcr="{{ route('profile.update.user.password') }}" action="{{ route('profile.update.user.password') }}" method="POST">
        @csrf
        <div class="form-grid">
            <div class="field full">
                <label for="currentPassword">{{ t('auth.current_password') }}</label>
                <input type="password" name="current_password" @class(['input', 'is-invalid' => $errors->has('current_password')]) id="currentPassword" autocomplete="current-password" required>
                @error('current_password')<div class="field-error">{{ $message }}</div>@enderror
            </div>
            <div class="field">
                <label for="newPassword">{{ t('auth.new_password') }}</label>
                <input type="password" name="password" @class(['input', 'is-invalid' => $errors->has('password')]) id="newPassword" autocomplete="new-password" required>
                @error('password')<div class="field-error">{{ $message }}</div>@enderror
            </div>
            <div class="field">
                <label for="confirmPassword">{{ t('auth.password_confirmation') }}</label>
                <input type="password" name="password_confirmation" @class(['input', 'is-invalid' => $errors->has('password_confirmation')]) id="confirmPassword" autocomplete="new-password" required>
                @error('password_confirmation')<div class="field-error">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-dark">{{ t('buttons.update_password') }}</button>
        </div>
    </form>
</div>
