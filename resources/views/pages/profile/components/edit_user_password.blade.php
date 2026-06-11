<form class="settings-form" id="form-user-password" data-abcr="{{route('profile.update.user.password')}}" action="{{route('profile.update.user.password')}}" method="POST">
    @csrf
    <input type="hidden" name="abc_request" value="1">
    <input type="hidden" name="abc_form_selector" value="#form-user-password">
    <div class="row g-3">
        <div class="col-md-6">
            <label for="currentPassword" class="form-label">{{t('auth.current_password')}}</label>
            <input type="password" name="current_password" class="form-control" id="currentPassword"
                   required>
            <div class="text-danger small mt-1" data-error="current_password"></div>
        </div>
        <div class="col-md-6">
            <label for="newPassword" class="form-label">{{t('auth.new_password')}}</label>
            <input type="password" name="password" class="form-control" id="newPassword" required>
            <div class="text-danger small mt-1" data-error="password"></div>
        </div>
        <div class="col-md-6">
            <label for="confirmPassword" class="form-label">{{t('auth.password_confirmation')}}</label>
            <input type="password" name="password_confirmation" class="form-control" id="confirmPassword"
                   required>
            <div class="text-danger small mt-1" data-error="password_confirmation"></div>
        </div>
    </div>

    <div class="form-buttons">
        <button type="submit" class="btn-save">{{t('buttons.update_password')}}</button>
    </div>
</form>
