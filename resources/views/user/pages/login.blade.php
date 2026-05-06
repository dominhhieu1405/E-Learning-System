<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>{{ __('login.title') }}</title>
    <link rel="icon" href="/favicon.ico"/>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/fontawesome.min.css">
    <link rel="stylesheet" href="/assets/css/main.css?v=3">
    <script src="/assets/js/jquery.min.js"></script>
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-logo">
            <h2>OnThi.io.vn</h2>
            <p class="text-muted">{{ __('login.subtitle') }}</p>
        </div>

        {{-- Google Login --}}
        <a href="/auth/google" class="btn-google">
            <svg width="20" height="20" viewBox="0 0 24 24"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
            {{ __('login.google_login') }}
        </a>

        <div class="auth-divider"><span>{{ __('login.or') }}</span></div>

        {{-- Login Form --}}
        <form id="loginForm">
            <div class="mb-3">
                <label class="form-label fw-semibold">{{ __('login.username_label') }}</label>
                <input type="text" name="username" class="form-control" placeholder="{{ __('login.username_placeholder') }}" required autocomplete="username">
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">{{ __('login.password_label') }}</label>
                <input type="password" name="password" class="form-control" placeholder="{{ __('login.password_placeholder') }}" required autocomplete="current-password">
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" name="remember" value="true" class="form-check-input" id="remember" checked>
                <label class="form-check-label" for="remember">{{ __('login.remember_me') }}</label>
            </div>

            <div id="loginError" class="alert alert-danger d-none mb-3"></div>

            <button type="submit" class="btn btn-ot-primary w-100" id="btnLogin">
                <i class="fas fa-sign-in-alt"></i> {{ __('login.login_button') }}
            </button>
        </form>

        <div class="text-center mt-3">
            <span class="text-muted">{{ __('login.no_account') }}</span>
            <a href="/register" class="fw-semibold">{{ __('login.register_now') }}</a>
        </div>
    </div>
</div>

<script src="/assets/js/auth.js?v=4"></script>
</body>
</html>
