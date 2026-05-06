<nav class="ot-navbar">
    <div class="container">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-4">
                <a href="/" class="navbar-brand me-0">OnThi.io.vn</a>

                {{-- Desktop Menu --}}
                <div class="d-none d-lg-flex align-items-center gap-1 ms-3">
                    <a href="/" class="nav-link {{ ($navbar ?? '') === 'home' ? 'active' : '' }}">
                        {{ __('common.home') }}
                    </a>

                    <div class="dropdown">
                        <a href="#" class="nav-link dropdown-toggle {{ ($navbar ?? '') === 'exams' ? 'active' : '' }}"
                            data-bs-toggle="dropdown">
                            {{ __('common.exams') }}
                        </a>
                        <ul class="dropdown-menu shadow-lg border-0 mt-2">
                            <li><a class="dropdown-item fw-bold text-primary py-2" href="/exams">{{ __('common.all_exams') }}</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li class="dropdown-header text-uppercase fs-11 fw-800 opacity-50">{{ __('common.by_exam') }}</li>
                            <li><a class="dropdown-item py-2" href="/type/1">{{ __('common.thpt') }}</a></li>
                            <li><a class="dropdown-item py-2" href="/type/2">{{ __('common.hsa') }}</a></li>
                            <li><a class="dropdown-item py-2" href="/type/3">{{ __('common.tsa') }}</a></li>
                        </ul>
                    </div>

                    <div class="dropdown">
                        <a href="#" class="nav-link dropdown-toggle {{ ($navbar ?? '') === 'courses' ? 'active' : '' }}"
                            data-bs-toggle="dropdown">
                            {{ __('common.courses') }}
                        </a>
                        <div class="dropdown-menu shadow-lg border-0 mt-2 p-3" style="min-width: 350px;">
                            <div class="row g-2">
                                <div class="col-12">
                                    <h6 class="dropdown-header text-primary px-0 mb-2">{{ __('common.subjects_title') }}</h6>
                                </div>
                                @php $allSubjects = \Models\Web::allSubject(); @endphp
                                @foreach($allSubjects as $subject)
                                    <div class="col-md-6">
                                        <a href="/subject/{{$subject->id}}"
                                            class="dropdown-item rounded py-1 px-2 mb-1 small">
                                            <i class="fas fa-book-open me-2 opacity-50"></i> {{$subject->name}}
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                            <div class="border-top mt-2 pt-2 text-center">
                                <a href="/courses" class="btn btn-sm btn-link text-decoration-none">{{ __('common.view_all_courses') }}
                                    <i class="fas fa-arrow-right fs-10"></i></a>
                            </div>
                        </div>
                    </div>

                    <a href="/documents" class="nav-link {{ ($navbar ?? '') === 'documents' ? 'active' : '' }}">
                        {{ __('common.documents') }}
                    </a>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2">
                {{-- Search Box --}}
                <form action="/search" class="d-none d-xl-flex position-relative me-2" style="width: 200px;">
                    <input type="text" name="q"
                        class="form-control form-control-sm rounded-pill ps-3 pe-4 bg-light shadow-none border-0"
                        placeholder="{{ __('common.search_placeholder') }}" style="height: 38px;">
                    <button type="submit" class="btn btn-sm position-absolute end-0 top-0 mt-1 me-1 text-muted"><i
                            class="fas fa-search"></i></button>
                </form>

                <div class="dropdown">
                    <button class="btn btn-sm btn-light border rounded-circle" data-bs-toggle="dropdown"
                        title="{{ __('common.lang_toggle') }}" style="width: 38px; height: 38px;">
                        <i class="fas fa-globe"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2">
                        @php $languages = \Models\Language::allActive(); @endphp
                        @foreach($languages as $lang)
                            <li>
                                <a class="dropdown-item py-2 d-flex align-items-center justify-content-between {{ ($_SESSION['locale'] ?? 'vi') == $lang['code'] ? 'active' : '' }}"
                                    href="/change-language/{{ $lang['code'] }}">
                                    {{ $lang['name'] }}
                                    @if(($_SESSION['locale'] ?? 'vi') == $lang['code'])
                                        <i class="fas fa-check fs-10 ms-2"></i>
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <button class="btn btn-sm btn-light border rounded-circle" id="btnThemeToggle"
                    title="{{ __('common.theme_toggle') }}" style="width: 38px; height: 38px;">
                    <i class="fas fa-moon"></i>
                </button>

                @if(is_login())
                    <div class="dropdown">
                        <button
                            class="btn btn-sm d-flex align-items-center p-1 pe-lg-3 rounded-pill border-0 shadow-none bg-light"
                            data-bs-toggle="dropdown" style="height: 38px;">
                            <img src="{{ userget(false)->avatar ?? ('https://ui-avatars.com/api/?name=' . urlencode(userget(false)->display_name ?? userget(false)->username) . '&background=6366f1&color=fff') }}"
                                class="rounded-circle shadow-sm" width="30" height="30"
                                style="object-fit: cover; border: 2px solid #fff">
                            <span
                                class="d-none d-lg-inline ms-2 text-dark fw-600">{{ userget(false)->display_name ?? userget(false)->username }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2">
                            <li><a class="dropdown-item py-2" href="/user"><i
                                        class="fas fa-user-circle me-2 opacity-50"></i> {{ __('common.profile') }}</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item py-2 text-danger" href="/logout"><i
                                        class="fas fa-sign-out-alt me-2 opacity-50"></i> {{ __('common.logout') }}</a></li>
                        </ul>
                    </div>
                @else
                    <a href="/login" class="btn btn-ot-primary px-4 d-none d-md-inline-block"
                        style="height: 38px; line-height: 24px;">{{ __('common.login') }}</a>
                    <a href="/login" class="btn btn-sm btn-ot-primary d-md-none"><i class="fas fa-sign-in-alt"></i></a>
                @endif
            </div>
        </div>
    </div>
</nav>