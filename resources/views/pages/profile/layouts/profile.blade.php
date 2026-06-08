@extends('layouts.app')

@section('content')
    <main class="main">

        <!-- Account Section -->
        <section id="account" class="account section">

            <div class="container" data-aos="fade-up" data-aos-delay="100">

                <!-- Mobile Menu Toggle -->
                <div class="mobile-menu d-lg-none mb-4">
                    <button class="mobile-menu-toggle" type="button" data-bs-toggle="collapse"
                            data-bs-target="#profileMenu">
                        <i class="bi bi-grid"></i>
                        <span>{{t('buttons.menu')}}</span>
                    </button>
                </div>

                <div class="row g-4">
                    @include('pages.profile.components.left_menu')

                    <!-- Content Area -->
                    <div class="col-lg-9">
                        <div class="content-area">
                            @yield('profile_content')
                        </div>
                    </div>
                </div>

            </div>

        </section>
        <!-- /Account Section -->
    </main>
@endsection
