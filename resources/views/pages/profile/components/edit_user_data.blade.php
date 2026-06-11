<form class="settings-form" id="form-user-data" data-abcr="{{route('profile.update.user.data')}}" action="{{route('profile.update.user.data')}}" method="POST">
    @csrf
    <input type="hidden" name="abc_request" value="1">
    <input type="hidden" name="abc_form_selector" value="#form-user-data">
    <div class="row g-3">
        <div class="col-md-6">
            <label for="login-register-reg-firstname" class="form-label">{{t('auth.name')}}</label>
            <input type="text" name="name" class="form-control" id="login-register-reg-firstname"
                   value="{{ old('name',$user->name) }}" required>
            <div class="text-danger small mt-1" data-error="name"></div>
        </div>
        <div class="col-md-6">
            <label for="login-register-reg-email" class="form-label">{{t('auth.email')}}</label>
            <input type="text" name="email" class="form-control" id="login-register-reg-email"
                   value="{{ old('email',$user->email) }}" required>
            <div class="text-danger small mt-1" data-error="email"></div>
        </div>
    </div>

    <div class="form-buttons">
        <button type="submit" class="btn-save">{{t('buttons.save_changes')}}</button>
    </div>
</form>
