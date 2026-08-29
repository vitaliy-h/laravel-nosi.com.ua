@extends('layouts.app')

@section('content')
    @include('blocks.page_title')

    <!-- Login Section -->
    <section id="login-register" class="login-register section">

        <div class="container" data-aos="fade-up" data-aos-delay="100">

            <div class="row justify-content-center">
                <div class="col-lg-5">
                    <div class="login-register-wraper">

                        @include($form_view)

                    </div>
                </div>
            </div>

        </div>

    </section>
    <!-- /Login Section -->
@endsection
