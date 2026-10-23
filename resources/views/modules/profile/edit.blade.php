@extends('modules.profile.layouts.profile')

@section('profile_content')
    <div class="account-head">
        <h2>{{ t('profile.account_settings') }}</h2>
    </div>

    <div class="settings">
        <section class="panel-white">
            <h3>{{ t('profile.personal_information') }}</h3>
            @include('modules.profile.partials.edit_user_data')
        </section>

        <section class="panel-white">
            <h3>{{ t('profile.security') }}</h3>
            @include('modules.profile.partials.edit_user_password')
        </section>

        @if(!empty(config('social.providers')))
            <section class="panel-white">
                <h3>{{ t('social.section_title') }}</h3>
                <p class="muted" style="margin: -8px 0 18px; font-size: 14px;">{{ t('social.section_subtitle') }}</p>

                @if($errors->has('provider'))
                    <div class="alert alert-error">{{ $errors->first('provider') }}</div>
                @endif
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <div class="social-list">
                    @foreach(config('social.providers') as $provider)
                        @php($connected = $user->social_profiles[$provider] ?? null)
                        <div class="social-item">
                            <div>
                                <b><i class="bi bi-{{ $provider }}"></i> {{ t('social.' . $provider) }}</b>
                                @if($connected)
                                    <small>{{ t('social.connected', ['identifier' => $connected->identifier]) }}</small>
                                @endif
                            </div>
                            @if($connected)
                                <form action="{{ route('profile.social.disconnect', $provider) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-line btn-sm">{{ t('social.disconnect') }}</button>
                                </form>
                            @else
                                <a href="{{ route('social.redirect', $provider) }}" class="btn btn-dark btn-sm">{{ t('social.connect') }}</a>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="panel-white danger-zone">
            <h3>{{ t('profile.delete_account') }}</h3>
            <p class="muted" style="margin: -8px 0 18px;">{{ t('profile.delete_account_text') }}</p>
            <button type="button" class="btn btn-danger btn-sm" data-dialog-open="delete-account">{{ t('buttons.delete_account') }}</button>
            @error('delete_email')<div class="field-error" style="margin-top: 10px;">{{ $message }}</div>@enderror
        </section>
    </div>

    <dialog class="modal" id="delete-account">
        <form action="{{ route('profile.delete.user.account') }}" method="POST">
            @csrf
            <h3>{{ t('profile.delete_account') }}</h3>
            <p>{{ t('profile.delete_account_confirm_text') }}</p>

            <div class="field">
                <label for="delete-email">{{ t('auth.email') }}</label>
                <input type="email" name="delete_email" id="delete-email" class="input" required>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn btn-line btn-sm" data-dialog-close>{{ t('buttons.cancel') }}</button>
                <button type="submit" class="btn btn-danger btn-sm">{{ t('profile.delete_account') }}</button>
            </div>
        </form>
    </dialog>
@endsection
