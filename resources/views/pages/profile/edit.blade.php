@extends('pages.profile.layouts.profile')

@section('profile_content')
    <div class="section-header" data-aos="fade-up">
        <h2>{{t('profile.account_settings')}}</h2>
    </div>

    <div class="settings-content">
        <!-- Personal Information -->
        <div class="settings-section" data-aos="fade-up">
            <h3>{{t('profile.personal_information')}}</h3>

            @include('pages.profile.components.edit_user_data')

        </div>

        <!-- Security Settings -->
        <div class="settings-section" data-aos="fade-up" data-aos-delay="200">
            <h3>{{t('profile.security')}}</h3>

            @include('pages.profile.components.edit_user_password')
        </div>

        <!-- Social Accounts -->
        <div class="settings-section" data-aos="fade-up" data-aos-delay="250">
            <h3>{{ t('social.section_title') }}</h3>
            <p style="margin: -8px 0 20px; font-size: 14px; color: color-mix(in srgb, var(--default-color), transparent 40%);">
                {{ t('social.section_subtitle') }}
            </p>

            @if ($errors->has('provider'))
                <div class="alert alert-danger mb-3">{{ $errors->first('provider') }}</div>
            @endif

            @if (session('success'))
                <div class="alert alert-success mb-3">{{ session('success') }}</div>
            @endif

            <div class="preferences-list">
                @foreach (config('social.providers') as $provider)
                    @php $connected = $user->social_profiles[$provider] ?? null; @endphp
                    <div class="preference-item">
                        <div class="preference-info">
                            <h4>{{ t('social.' . $provider) }}</h4>
                            <p>
                                @if ($connected)
                                    {{ t('social.connected', ['identifier' => $connected->identifier]) }}
                                @endif
                            </p>
                        </div>
                        @if ($connected)
                            <form action="{{ route('profile.social.disconnect', $provider) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn-social-disconnect">
                                    {{ t('social.disconnect') }}
                                </button>
                            </form>
                        @else
                            <a href="{{ route('social.redirect', $provider) }}" class="btn-social-connect">
                                {{ t('social.connect') }}
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
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
@endsection
