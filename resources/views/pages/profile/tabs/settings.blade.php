<!-- Settings Tab -->
<div class="tab-pane fade show active" id="settings">
    <div class="section-header" data-aos="fade-up">
        <h2>{{t('profile.account_settings')}}</h2>
    </div>

    <div class="settings-content">
        <!-- Personal Information -->
        <div class="settings-section" data-aos="fade-up">
            <h3>{{t('profile.personal_information')}}</h3>

            @include('blocks.alerts', [
                    'successKey' => 'user_data_success',
                    'errorKey' => 'user_data_error',
                ])

            <form class="settings-form" action="{{route('profile.update.user.data')}}" method="POST">
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
                        <input type="email" name="email" class="form-control" id="login-register-reg-email"
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


        <!-- Security Settings -->
        <div class="settings-section" data-aos="fade-up" data-aos-delay="200">
            <h3>{{t('profile.security')}}</h3>

            @include('blocks.alerts', [
                    'successKey' => 'password_change_success',
                    'errorKey' => 'password_change_error',
                ])

            <form class="settings-form" action="{{route('profile.update.user.password')}}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-12">
                        <label for="currentPassword" class="form-label">{{t('auth.current_password')}}</label>
                        <input type="password" name="current_password" class="form-control" id="currentPassword" required>
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
                        <input type="password" name="password_confirmation" class="form-control" id="confirmPassword" required>
                    </div>
                </div>

                <div class="form-buttons">
                    <button type="submit" class="btn-save">{{t('buttons.update_password')}}</button>
                </div>
            </form>
        </div>

        <!-- Delete Account -->
        <div class="settings-section danger-zone" data-aos="fade-up" data-aos-delay="300">
            <h3>{{ t('profile.delete_account') }}</h3>
            <div class="danger-zone-content">
                <p>{{ t('profile.delete_account_text') }}</p>
                <button
                    type="button"
                    class="btn-danger"
                    data-bs-toggle="modal"
                    data-bs-target="#deleteAccountModal"
                >
                    {{ t('buttons.delete_account') }}
                </button>

                @error('delete_email')
                <div class="text-danger small mt-1">
                    {{ $message }}
                </div>
                @enderror
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <form action="{{ route('profile.delete.user.account') }}" method="POST">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ t('profile.delete_account') }}
                    </h5>

                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <p class="mb-3">
                        {{ t('profile.delete_account_confirm_text') }}
                    </p>

                    <div>
                        <label class="form-label">
                            {{ t('auth.email') }}
                        </label>

                        <input
                            type="email"
                            name="delete_email"
                            class="form-control"
                            required
                        >
                    </div>

                </div>

                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                    >
                        {{ t('buttons.cancel') }}
                    </button>

                    <button
                        type="submit"
                        class="btn btn-danger"
                    >
                        {{ t('profile.delete_account') }}
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>
