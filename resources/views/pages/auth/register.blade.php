@extends('layouts.app')

@section('content')
    @include('blocks.page_title')

    <!-- Register Section -->
    <section id="login-register" class="login-register section">

        <div class="container" data-aos="fade-up" data-aos-delay="100">

            <div class="row justify-content-center">
                <div class="col-lg-5">
                    <div class="login-register-wraper">

                        <ul class="nav nav-tabs nav-tabs-bordered justify-content-center mb-4" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a href="{{route('login')}}" class="nav-link">
                                    <i class="bi bi-box-arrow-in-right me-1"></i>{{t('auth.login')}}
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active">
                                    <i class="bi bi-person-plus me-1"></i>{{t('auth.register')}}
                                </button>
                            </li>
                        </ul>

                        <form method="post" action="{{route('register.store')}}">
                            @csrf
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="mb-4">
                                        <label for="login-register-reg-firstname" class="form-label">{{t('auth.name')}}</label>
                                        <input type="text" name="name" class="form-control" id="login-register-reg-firstname" value="{{ old('name') }}" required>
                                        @error('name')
                                            <div class="text-danger small mt-1">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="mb-4">
                                        <label for="login-register-reg-email" class="form-label">{{t('auth.email')}}</label>
                                        <input type="email" name="email" class="form-control" id="login-register-reg-email" value="{{ old('email') }}" required>
                                        @error('email')
                                            <div class="text-danger small mt-1">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="mb-4">
                                        <label for="login-register-reg-password" class="form-label">{{t('auth.password')}}</label>
                                        <input type="password" name="password" class="form-control" id="login-register-reg-password" required>
                                        @error('password')
                                            <div class="text-danger small mt-1">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="mb-4">
                                        <label for="login-register-reg-confirm-password" class="form-label">{{t('auth.password_confirmation')}}</label>
                                        <input type="password" name="password_confirmation" class="form-control" id="login-register-reg-confirm-password" required>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-check">
                                        <input type="checkbox" name="terms" value="1" class="form-check-input" id="login-register-terms" required>
                                        <label class="form-check-label" for="login-register-terms">
                                            {!! t('auth.terms') !!}
                                        </label>
                                        @error('terms')
                                            <div class="text-danger small mt-1">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary btn-lg">{{t('buttons.create_acc')}}</button>
                                    </div>
                                </div>
                            </div>
                        </form>

                    </div>
                </div>
            </div>

        </div>

    </section>
    <!-- /Register Section -->
@endsection
