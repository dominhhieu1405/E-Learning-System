@php(\Models\Web::updateStats())
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="{{ __('layout.meta_description') }}" />
    <meta name="keywords" content="onthi, thpt, hsa, tsa, đánh giá năng lực, ôn thi" />
    <meta name="author" content="OnThi.io.vn" />
    <link rel="icon" href="/favicon.ico" type="image/x-icon" />
    <title>@yield('title', 'OnThi.io.vn'){{ __('layout.title_suffix') }}</title>
    <meta name="theme-color" content="#1e1b4b" />

    {{-- Google Font --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Font Awesome --}}
    <link rel="stylesheet" href="/assets/css/fontawesome.min.css">
    {{-- Custom CSS --}}
    <link rel="stylesheet" href="/assets/css/main.css?v=3">
    <link rel="stylesheet" href="/assets/css/components/comment-box.css">
    <link rel="stylesheet" type="text/css" href="/assets/libs/toastr/build/toastr.min.css" />
    @yield('css')

    <script src="/assets/js/jquery.min.js"></script>
    {{-- MathJax --}}
    <script>
        window.MathJax = {
            tex: {
                inlineMath: [['$', '$'], ['\\(', '\\)']],
                displayMath: [['$$', '$$'], ['\\[', '\\]']],
                processEscapes: true
            },
            options: {
                enableMenu: false
            }
        };
    </script>
    <script src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js" async></script>
</head>

<body class="{{ (isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark') ? 'dark' : '' }}">

    {{-- Navbar --}}
    @include('user.components.navbar')

    {{-- Mobile Bottom Nav --}}
    <div class="d-md-none"
        style="position:fixed;bottom:0;left:0;right:0;z-index:1040;background:var(--ot-bg-card);border-top:1px solid var(--ot-border);padding:0.5rem 0;">
        <div class="d-flex justify-content-around text-center">
            <a href="/" class="nav-link py-1 {{ ($navbar ?? '') === 'home' ? 'active' : '' }}"><i
                    class="fas fa-home d-block"></i><small>{{ __('layout.nav_home') }}</small></a>
            <a href="/exams" class="nav-link py-1 {{ ($navbar ?? '') === 'exams' ? 'active' : '' }}"><i
                    class="fas fa-file-alt d-block"></i><small>{{ __('layout.nav_exams') }}</small></a>
            <a href="/courses" class="nav-link py-1 {{ ($navbar ?? '') === 'courses' ? 'active' : '' }}"><i
                    class="fas fa-book d-block"></i><small>{{ __('layout.nav_courses') }}</small></a>
            <a href="/documents" class="nav-link py-1 {{ ($navbar ?? '') === 'documents' ? 'active' : '' }}"><i
                    class="fas fa-file d-block"></i><small>{{ __('layout.nav_docs') }}</small></a>
            <a href="{{ is_login() ? '/user' : '/login' }}" class="nav-link py-1"><i
                    class="fas fa-user d-block"></i><small>{{ __('layout.nav_me') }}</small></a>
        </div>
    </div>

    {{-- Content --}}
    <main style="min-height:70vh;padding-bottom:80px;">
        @yield('content')
    </main>

    @include("user.components.footer")

    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/libs/toastr/build/toastr.min.js"></script>
    <script>
        // Theme toggle
        $('#btnThemeToggle').on('click', function () {
            $('body').toggleClass('dark');
            var isDark = $('body').hasClass('dark');
            document.cookie = 'theme=' + (isDark ? 'dark' : 'light') + ';path=/;max-age=31536000';
            $(this).find('i').toggleClass('fa-moon fa-sun');
        });
        if ($('body').hasClass('dark')) $('#btnThemeToggle i').removeClass('fa-moon').addClass('fa-sun');
    </script>
    @yield('js')
    @yield('javascript')
</body>

</html>