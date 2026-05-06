<!-- header start -->
<header class="section-t-space">
    <div class="custom-container">
        <div class="header">
            <div class="head-content">
                <button class="sidebar-btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasLeft">
                    <span class="ti ti-menu-2"></span>

                </button>
                <div class="header-info">
                    <img class="img-fluid profile-pic" src="{{userget()->avatar_url}}" alt="profile" style="border-radius: 50%"/>
                    <div>
                        <h4 class="light-text">{{ __('common.hello') }}</h4>
                        <h2 class="theme-color">{{userget()->name}}</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
<!-- header end -->