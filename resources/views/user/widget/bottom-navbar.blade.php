<!-- panel-space start -->
<section class="panel-space"></section>
<!-- panel-space end -->

<!-- bottom navbar start -->
<div class="navbar-menu">
    <ul>
        <li class="@if($navbar != 'shopping' && $navbar != 'profile') active @endif">
            <a href="{{url('home')}}">
                <div class="icon">
                    <img class="unactive" src="/assets/img/svg/home.svg" alt="{{ __('common.home') }}"/>
                    <img class="active" src="/assets/img/svg/home-fill.svg" alt="{{ __('common.home') }}"/>
                </div>
            </a>
        </li>
        <li class="@if($navbar == 'shopping') active @endif">
            <a href="{{url('shopping')}}">
                <div class="icon">
                    <img class="unactive" src="/assets/img/svg/bag.svg" alt="{{ __('common.shopping') }}"/>
                    <img class="active" src="/assets/img/svg/bag-fill.svg" alt="{{ __('common.shopping') }}"/>
                </div>
            </a>
        </li>
        <li class="@if($navbar == 'profile') active @endif">
            <a href="{{url('profile')}}">
                <div class="icon">
                    <img class="unactive" src="/assets/img/svg/profile.svg" alt="{{ __('common.account') }}"/>
                    <img class="active" src="/assets/img/svg/profile-fill.svg" alt="{{ __('common.account') }}"/>
                </div>
            </a>
        </li>
    </ul>
</div>
<!-- bottom navbar end -->