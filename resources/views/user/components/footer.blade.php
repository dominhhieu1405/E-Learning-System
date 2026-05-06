
{{-- Footer --}}
<footer class="ot-footer d-none d-md-block bg-white border-top py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <a href="/" class="navbar-brand fw-bold fs-4 text-primary mb-3 d-block">OnThi.io.vn</a>
                <p class="text-muted small mb-4">{{ __('layout.footer_desc') }}</p>
                <div class="d-flex gap-3">
                    <a href="#" class="btn btn-sm btn-light rounded-circle" style="width:36px;height:36px;"><i
                                class="fab fa-facebook-f"></i></a>
                    <a href="#" class="btn btn-sm btn-light rounded-circle" style="width:36px;height:36px;"><i
                                class="fab fa-youtube"></i></a>
                    <a href="#" class="btn btn-sm btn-light rounded-circle" style="width:36px;height:36px;"><i
                                class="fab fa-tiktok"></i></a>
                </div>
            </div>
            <div class="col-6 col-lg-2">
                <h6 class="fw-bold mb-3">{{ __('layout.footer_about_title') }}</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="/" class="text-decoration-none text-muted">{{ __('layout.footer_home') }}</a></li>
                    <li class="mb-2"><a href="/terms" class="text-decoration-none text-muted">{{ __('layout.footer_terms') }}</a></li>
                    <li class="mb-2"><a href="/faqs" class="text-decoration-none text-muted">{{ __('layout.footer_faqs') }}</a></li>
                    <li class="mb-2"><a href="#" class="text-decoration-none text-muted">{{ __('layout.footer_contact_ads') }}</a></li>
                </ul>
            </div>
            <div class="col-6 col-lg-2">
                <h6 class="fw-bold mb-3">{{ __('layout.footer_resource_title') }}</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="/exams" class="text-decoration-none text-muted">{{ __('layout.footer_latest_exams') }}</a>
                    </li>
                    <li class="mb-2"><a href="/courses" class="text-decoration-none text-muted">{{ __('layout.footer_hot_courses') }}</a>
                    </li>
                    <li class="mb-2"><a href="/documents" class="text-decoration-none text-muted">{{ __('layout.footer_free_docs') }}</a></li>
                    <li class="mb-2"><a href="#" class="text-decoration-none text-muted">{{ __('layout.footer_blog') }}</a></li>
                </ul>
            </div>
            <div class="col-lg-4">
                <h6 class="fw-bold mb-3">{{ __('layout.footer_connect_title') }}</h6>
                <p class="text-muted small mb-3">{{ __('layout.footer_connect_desc') }}</p>
                <form class="input-group input-group-sm mb-3">
                    <input type="email" class="form-control" placeholder="{{ __('layout.footer_email_placeholder') }}">
                    <button class="btn btn-primary" type="button">{{ __('layout.footer_subscribe') }}</button>
                </form>

            </div>
        </div>
        <div class="border-top mt-5 pt-4 text-center">
            <p class="text-muted small mb-0">&copy; {{ date('Y') }} OnThi.io.vn{{ __('layout.footer_copyright') }}</p>
        </div>
    </div>
</footer>