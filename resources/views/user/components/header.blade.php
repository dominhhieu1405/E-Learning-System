<header id="Siloiki-MenuTested" itemscope="itemscope" itemtype="https://schema.org/WPHeader" role="banner">
    <div class="Siloiki-MenuWorks-wrap">
        <div class="Siloiki-MenuWorks">
            <div class="container">
                <div class="SuperLogo-wrap">
                    <div class="header-section">
                        <div class="header-left">
                            <div class="SuperLogo section" id="SuperLogo">
                                <div class="widget Header">
                                    <a class="show-menu-space" href="javascript:;"></a>
                                    <a class="SuperLogo-img" href="{{url("home")}}">
                                        <img alt="Học 24/7" data-height="93" data-width="403" src="/assets/img/logo.png">
                                        <h1 id="title-header">Học 24/7</h1>
                                    </a>
                                </div>
                            </div>
                            <div class="Siloiki-FlexMenu section" id="Siloiki-FlexMenu">
                                <div class="widget LinkList show-menu">
                                    <ul id="Siloiki-FlexMenuList" role="menubar">
                                        <li itemprop="name"><a href="{{url("home")}}" itemprop="url">{{ __('common.home') }}</a></li>
                                        <li itemprop="name" class="sub-tab">
                                            <a href="#" itemprop="url">THPT</a>
                                            <ul class="sub-menu m-sub">
                                                @foreach(\Models\Web::subject(1) as $subject)
                                                    <li itemprop="name">
                                                        <a href="{{url("subject", ["id" => $subject->id])}}" itemprop="url">{{$subject->name}}</a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </li>
                                        <li itemprop="name" class="sub-tab">
                                            <a href="#" itemprop="url">{{ __('common.hsa') }}</a>
                                            <ul class="sub-menu m-sub">
                                                @foreach(\Models\Web::subject(2) as $subject)
                                                    <li itemprop="name">
                                                        <a href="{{url("subject", ["id" => $subject->id])}}" itemprop="url">{{$subject->name}}</a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </li>
                                        <li itemprop="name" class="sub-tab">
                                            <a href="#" itemprop="url">{{ __('common.tsa') }}</a>
                                            <ul class="sub-menu m-sub">
                                                @foreach(\Models\Web::subject(3) as $subject)
                                                    <li itemprop="name">
                                                        <a href="{{url("subject", ["id" => $subject->id])}}" itemprop="url">{{$subject->name}}</a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="header-right">
                            <div class="search-wrap">
                                <div class="mega-mode">
                                    <input class="dark-button dark-mode" id="dark" type="checkbox" aria-label="">
                                    <span class="dark-toggle"></span></div>
                                <a class="search-button-flex" href="javascript:return;" role="button" title="{{ __('common.search') }}"></a>
                            </div>
                        </div>
                        <div id="search-flex">
                            <div class="search-flex-container">
                                <form action="{{url("search")}}" class="search-form" role="search">
                                    <input autocomplete="off" class="search-input" name="q" placeholder="{{ __('common.search_placeholder') }}" spellcheck="false" type="search" value="" aria-label="">
                                </form>
                                <a class="search-flex-close search-hidden" href="javascript:return;" role="button" title="{{ __('common.search') }}"></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Slider Mobile Menu -->
<div id="menu-space">
    <div class="area-runs">
        <span class="hide-Siloiki-Menu"></span>
    </div>
    <div class="menu-space-flex">
        <div class="Siloiki-Menu" id="Siloiki-Menu">
            <ul id="Siloiki-FlexMenuList" role="menubar">
                <li itemprop="name"><a href="{{url("home")}}" itemprop="url">{{ __('common.home') }}</a></li>
                <li itemprop="name" class="sub-tab">
                    <a href="#" itemprop="url">THPT</a>
                    <ul class="sub-menu m-sub">
                        @foreach(\Models\Web::subject(1) as $subject)
                            <li itemprop="name">
                                <a href="{{url("subject", ["id" => $subject->id])}}" itemprop="url">{{$subject->name}}</a>
                            </li>
                        @endforeach
                    </ul>
                </li>
                <li itemprop="name" class="sub-tab">
                    <a href="#" itemprop="url">{{ __('common.hsa') }}</a>
                    <ul class="sub-menu m-sub">
                        @foreach(\Models\Web::subject(2) as $subject)
                            <li itemprop="name">
                                <a href="{{url("subject", ["id" => $subject->id])}}" itemprop="url">{{$subject->name}}</a>
                            </li>
                        @endforeach
                    </ul>
                </li>
                <li itemprop="name" class="sub-tab">
                    <a href="#" itemprop="url">{{ __('common.tsa') }}</a>
                    <ul class="sub-menu m-sub">
                        @foreach(\Models\Web::subject(3) as $subject)
                            <li itemprop="name">
                                <a href="{{url("subject", ["id" => $subject->id])}}" itemprop="url">{{$subject->name}}</a>
                            </li>
                        @endforeach
                    </ul>
                </li>
            </ul>
        </div>
        <div class="social-mobile">
            <ul class="social-footer social-bg-hover">
                <li class="facebook-f">
                    <a class="facebook-f" href="#" rel="noopener noreferrer" target="_blank"></a>
                </li>
                <li class="twitter">
                    <a class="twitter" href="#" rel="noopener noreferrer" target="_blank"></a>
                </li>
                <li class="youtube">
                    <a class="youtube" href="#" rel="noopener noreferrer" target="_blank"></a>
                </li>
                <li class="instagram">
                    <a class="instagram" href="#" rel="noopener noreferrer" target="_blank"></a>
                </li>
                <li class="reddit">
                    <a class="reddit" href="#" rel="noopener noreferrer" target="_blank"></a>
                </li>
            </ul>
        </div>
    </div>
</div>