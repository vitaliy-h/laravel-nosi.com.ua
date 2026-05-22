@extends('layouts.app')

@section('content')
    @include('blocks.page_title')

    <!-- Login Section -->
    <section id="login-register" class="login-register section">

        <div class="container" data-aos="fade-up" data-aos-delay="100">

            <div class="row justify-content-center">
                <div class="col-lg-5">
                    <div class="login-register-wraper">

                        <ul class="nav nav-tabs nav-tabs-bordered justify-content-center mb-4" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active">
                                    <i class="bi bi-box-arrow-in-right me-1"></i>{{t('auth.login')}}
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a href="{{route('register')}}" class="nav-link">
                                    <i class="bi bi-person-plus me-1"></i>{{t('auth.register')}}
                                </a>
                            </li>
                        </ul>

                        <form method="post" action="{{route('login.store')}}">
                            @csrf
                            <div class="mb-4">
                                <label for="login-register-login-email" class="form-label">{{t('auth.email')}}</label>
                                <input type="email" name="email" class="form-control" id="login-register-login-email"  value="{{ old('email') }}" required>
                                @error('email')
                                <div class="text-danger small mt-1">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="login-register-login-password" class="form-label">{{t('auth.password')}}</label>
                                <input type="password" name="password" class="form-control" id="login-register-login-password" required>
                                @error('password')
                                <div class="text-danger small mt-1">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="form-check">
                                    <input type="checkbox" name="remember_me" class="form-check-input" id="login-register-remember-me" value="1">
                                    <label class="form-check-label" for="login-register-remember-me">{{t('auth.remember_me')}}</label>
                                </div>
                                <a href="{{route('password.recovery')}}" class="forgot-password">{{t('auth.forgot_password')}}</a>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">{{t('buttons.login')}}</button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>

        </div>

    </section>
    <!-- /Login Section -->
@endsection
