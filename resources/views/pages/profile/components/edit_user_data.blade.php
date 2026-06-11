<div id="form-user-data">
@include('blocks.alerts', [
        'successKey' => 'user_data_success',
        'errorKey' => 'user_data_error',
    ])

<form class="settings-form" data-abcr="{{route('profile.update.user.data')}}" action="{{route('profile.update.user.data')}}" method="POST">
    @csrf
    <div class="row g-3">
        <div class="col-md-6">
            <label for="login-register-reg-firstname" class="form-label">{{t('auth.name')}}</label>
            <input type="text" name="name" class="form-control" id="login-register-reg-firstname"
                   value="{{ old('name',$user->name) }}" required>
            @error('name')
                <div class="text-danger small mt-1">
                    {{ $message }}
                </div>
            @enderror
        </div>
        <div class="col-md-6">
            <label for="login-register-reg-email" class="form-label">{{t('auth.email')}}</label>
            <input type="text" name="email" class="form-control" id="login-register-reg-email"
                   value="{{ old('email',$user->email) }}" required>
            @error('email')
                <div class="text-danger small mt-1">
                    {{ $message }}
                </div>
            @enderror
        </div>
    </div>

    <div class="form-buttons">
        <button type="submit" class="btn-save">{{t('buttons.save_changes')}}</button>
    </div>
</form>
</div>
