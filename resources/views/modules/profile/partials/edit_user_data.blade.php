{{-- Re-rendered by ProfileController after an abc-response submit, keep the wrapper id. --}}
<div id="form-user-data">
    @include('partials.alerts', [
        'successKey' => 'user_data_success',
        'errorKey' => 'user_data_error',
    ])

    <form data-abcr="{{ route('profile.update.user.data') }}" action="{{ route('profile.update.user.data') }}" method="POST">
        @csrf
        <div class="form-grid">
            <div class="field">
                <label for="profile-name">{{ t('profile.user_name') }}</label>
                <input type="text" name="name" @class(['input', 'is-invalid' => $errors->has('name')]) id="profile-name" value="{{ old('name', $user->name) }}" autocomplete="given-name" required>
                @error('name')<div class="field-error">{{ $message }}</div>@enderror
            </div>
            <div class="field">
                <label for="profile-surname">{{ t('profile.user_surname') }}</label>
                <input type="text" name="surname" @class(['input', 'is-invalid' => $errors->has('surname')]) id="profile-surname" value="{{ old('surname', $user->surname) }}" autocomplete="family-name" required>
                @error('surname')<div class="field-error">{{ $message }}</div>@enderror
            </div>
            <div class="field">
                <label for="profile-email">{{ t('profile.user_email') }}</label>
                <input type="text" name="email" @class(['input', 'is-invalid' => $errors->has('email')]) id="profile-email" value="{{ old('email', $user->email) }}" autocomplete="email" required>
                @error('email')<div class="field-error">{{ $message }}</div>@enderror
            </div>
            <div class="field">
                <label for="profile-phone">{{ t('profile.user_phone') }}</label>
                <input type="tel" name="phone" @class(['input', 'is-invalid' => $errors->has('phone')]) id="profile-phone" value="{{ old('phone', $user->phone) }}" autocomplete="tel" required>
                @error('phone')<div class="field-error">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-dark">{{ t('buttons.save_changes') }}</button>
        </div>
    </form>
</div>
