<!-- Profile Menu -->
<div class="col-lg-3">
    <div class="profile-menu collapse d-lg-block" id="profileMenu">
        <!-- User Info -->
        <div class="user-info" data-aos="fade-right">
            <h4>{{$user->formatted_name}}</h4>
        </div>

        <!-- Navigation Menu -->
        <nav class="menu-nav">
            <ul class="nav flex-column" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#settings">
                        <i class="bi bi-gear"></i>
                        <span>{{t('profile.account_settings')}}</span>
                    </a>
                </li>
            </ul>

            <div class="menu-footer">
                <form id="logout-form" method="POST" action="{{ route('logout') }}">
                    @csrf
                </form>

                <a href="#" class="logout-link"
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>{{ t('buttons.logout') }}</span>
                </a>
            </div>
        </nav>
    </div>
</div>
