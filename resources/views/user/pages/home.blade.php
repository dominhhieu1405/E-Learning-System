@extends('user.layout.layout')
@section('title', __('common.home'))
@section('content')

    {{-- Hero Section --}}
    <div class="ot-hero mb-0">
        <div class="container py-lg-5">
            <div class="row align-items-center">
                <div class="col-lg-7 animate-fadeInUp">
                    <div
                        class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill mb-3 fw-bold">
                        {{ __('home.hero_badge') }}
                    </div>
                    <h1 class="display-4 fw-800 mb-3" style="line-height: 1.1;">{{ __('home.hero_title') }} <span
                            class="text-warning">{{ __('home.hero_title_highlight') }}</span></h1>
                    <p class="lead mb-4 opacity-75">{{ __('home.hero_desc') }}</p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="/exams" class="btn btn-ot-primary btn-lg shadow-lg">
                            <i class="fas fa-rocket me-2"></i> {{ __('home.hero_btn_exam') }}
                        </a>
                        <a href="/courses" class="btn btn-outline-light btn-lg border-2">
                            <i class="fas fa-graduation-cap me-2"></i> {{ __('home.hero_btn_course') }}
                        </a>
                    </div>
                    <div class="mt-4 d-flex align-items-center gap-4 text-white-50">
                        <div><i class="fas fa-check-circle text-success me-1"></i> {{ __('home.hero_feature_1') }}</div>
                        <div><i class="fas fa-check-circle text-success me-1"></i> {{ __('home.hero_feature_2') }}</div>
                        <div><i class="fas fa-check-circle text-success me-1"></i> {{ __('home.hero_feature_3') }}</div>
                    </div>
                </div>
                <div class="col-lg-5 d-none d-lg-block text-end animate-fadeInUp" style="animation-delay: 0.2s">
                    <div class="hero-image-wrap position-relative">
                        <div style="font-size: 15rem; opacity: 0.15; position: absolute; top: -50px; right: 0;">📚</div>
                        <!-- <img src="https://zpi.cx/b/5KjfgT0Y.png-webp" class="img-fluid" alt="Hero Image" style="filter: drop-shadow(0 20px 40px rgba(0,0,0,0.3))"> -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats Section --}}
    <div class="bg-white border-bottom py-5 mb-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-6 col-md-3">
                    <div class="stat-card">
                        <h3>{{ __('home.stat_1_val') }}</h3>
                        <p>{{ __('home.stat_1_label') }}</p>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card">
                        <h3>{{ __('home.stat_2_val') }}</h3>
                        <p>{{ __('home.stat_2_label') }}</p>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card">
                        <h3>{{ __('home.stat_3_val') }}</h3>
                        <p>{{ __('home.stat_3_label') }}</p>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card">
                        <h3>{{ __('home.stat_4_val') }}</h3>
                        <p>{{ __('home.stat_4_label') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container py-4">
        {{-- Đề thi nổi bật --}}
        @if($exams && count($exams) > 0)
            <div class="section-header">
                <div>
                    <h2 class="mb-1">{{ __('home.latest_exams_title') }}</h2>
                    <p class="text-muted small">{{ __('home.latest_exams_desc') }}</p>
                </div>
                <a href="/exams" class="btn btn-sm btn-ot-outline">{{ __('home.view_all') }} <i class="fas fa-arrow-right fs-12"></i></a>
            </div>
            <div class="row g-4 mb-5">
                @foreach($exams as $exam)
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="exam-card {{ $exam->exam_type }} h-100 d-flex flex-column">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="exam-type-badge {{ $exam->exam_type }}">{{ strtoupper($exam->exam_type) }}</span>
                                @if($exam->is_random)
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2"><i
                                            class="fas fa-random"></i></span>
                                @endif
                            </div>
                            <h5 class="fw-bold mb-3 flex-grow-1">{{ $exam->title }}</h5>
                            <div class="exam-meta mb-4">
                                @if($exam->exam_type === 'thpt')
                                    <span><i class="far fa-clock"></i> {{ $exam->duration }} {{ __('home.minute') }}</span>
                                @else
                                    <span><i class="far fa-clock"></i>
                                        {{ $exam->duration_p1 + $exam->duration_p2 + $exam->duration_p3 }} {{ __('home.minute') }}</span>
                                @endif
                                <span><i class="far fa-question-circle"></i> {{ __('home.multiple_choice') }}</span>
                            </div>
                            <a href="/exam/{{ $exam->id }}" class="btn btn-ot-primary w-100">
                                {{ __('home.start_exam_now') }} <i class="fas fa-chevron-right ms-2 fs-12"></i>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Why Us section --}}
        <div class="py-5 mb-5 rounded-4 bg-light p-4 p-md-5">
            <div class="text-center mb-5">
                <h2 class="fw-bold">{{ __('home.why_us_title') }}</h2>
                <p class="text-muted">{{ __('home.why_us_desc') }}</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-box">
                        <div class="feature-icon"><i class="fas fa-database"></i></div>
                        <h5 class="fw-bold">{{ __('home.why_us_1_title') }}</h5>
                        <p class="text-muted small mb-0">{{ __('home.why_us_1_desc') }}</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-box">
                        <div class="feature-icon"><i class="fas fa-chart-line"></i></div>
                        <h5 class="fw-bold">{{ __('home.why_us_2_title') }}</h5>
                        <p class="text-muted small mb-0">{{ __('home.why_us_2_desc') }}</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-box">
                        <div class="feature-icon"><i class="fas fa-medal"></i></div>
                        <h5 class="fw-bold">{{ __('home.why_us_3_title') }}</h5>
                        <p class="text-muted small mb-0">{{ __('home.why_us_3_desc') }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Khóa học --}}
        @if($courses && count($courses) > 0)
            <div class="section-header">
                <div>
                    <h2 class="mb-1">{{ __('home.pro_courses_title') }}</h2>
                    <p class="text-muted small">{{ __('home.pro_courses_desc') }}</p>
                </div>
                <a href="/courses" class="btn btn-sm btn-ot-outline">{{ __('home.view_all') }} <i class="fas fa-arrow-right fs-12"></i></a>
            </div>
            <div class="row g-4 mb-5">
                @foreach($courses as $course)
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="ot-card h-100">
                            <div class="card-img-wrap position-relative ov-hidden">
                                @if($course->image)
                                    <img src="{{ $course->image }}" alt="{{ $course->name }}" class="card-img-top">
                                @else
                                    <div class="card-img-top d-flex align-items-center justify-content-center"
                                        style="background:linear-gradient(135deg,var(--ot-primary),var(--ot-secondary));color:#fff;">
                                        <i class="fas fa-book fa-3x" style="opacity:0.3;"></i>
                                    </div>
                                @endif
                                <div class="card-badge position-absolute top-0 start-0 m-3">
                                    <span class="badge bg-white text-dark shadow-sm px-2">{{ __('home.course_badge') }}</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <h6 class="card-title fw-bold mb-3">{{ $course->name }}</h6>
                                <div class="d-flex justify-content-between align-items-center mt-auto">
                                    <span class="small text-muted"><i class="far fa-user me-1"></i>
                                        {{ number_format($course->views ?? 0) }} {{ __('home.students') }}</span>
                                    <a href="/course/{{ $course->id }}" class="btn btn-sm btn-primary rounded-pill px-3">{{ __('home.study_now') }}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- How it works --}}
        <div class="py-5 mb-5 border-top border-bottom">
            <h2 class="fw-bold text-center mb-5">{{ __('home.how_it_works_title') }}</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="step-card">
                        <div class="step-number">1</div>
                        <h6 class="fw-bold">{{ __('home.step_1_title') }}</h6>
                        <p class="text-muted small">{{ __('home.step_1_desc') }}</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="step-card">
                        <div class="step-number">2</div>
                        <h6 class="fw-bold">{{ __('home.step_2_title') }}</h6>
                        <p class="text-muted small">{{ __('home.step_2_desc') }}</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="step-card">
                        <div class="step-number">3</div>
                        <h6 class="fw-bold">{{ __('home.step_3_title') }}</h6>
                        <p class="text-muted small">{{ __('home.step_3_desc') }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tài liệu --}}
        @if($documents && count($documents) > 0)
            <div class="section-header">
                <div>
                    <h2 class="mb-1">{{ __('home.free_docs_title') }}</h2>
                    <p class="text-muted small">{{ __('home.free_docs_desc') }}</p>
                </div>
                <a href="/documents" class="btn btn-sm btn-ot-outline">{{ __('home.view_all') }} <i class="fas fa-arrow-right fs-12"></i></a>
            </div>
            <div class="row g-4 mb-5">
                @foreach($documents as $doc)
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="ot-card h-100 d-flex flex-column shadow-sm border-0">
                            @if($doc->image)
                                <img src="{{ $doc->image }}" alt="{{ $doc->name }}" class="card-img-top">
                            @else
                                <div class="card-img-top d-flex align-items-center justify-content-center"
                                    style="background:linear-gradient(135deg,#f59e0b,#ef4444);color:#fff;">
                                    <i class="fas fa-file-pdf fa-3x" style="opacity:0.3;"></i>
                                </div>
                            @endif
                            <div class="card-body d-flex flex-column">
                                <h6 class="card-title fw-bold mb-3">{{ $doc->name }}</h6>
                                <a href="/document/{{ $doc->id }}" class="btn btn-sm btn-outline-danger w-100 mt-auto rounded-pill">
                                    {{ __('home.download_doc') }} <i class="fas fa-download ms-1 fs-12"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Call to Action --}}
        <div class="cta-gradient rounded-4 p-5 text-center text-white mb-5 shadow-lg"
            style="background: linear-gradient(135deg, var(--ot-primary), var(--ot-secondary));">
            <h2 class="fw-bold mb-3">{{ __('home.cta_title') }}</h2>
            <p class="mb-4 opacity-75 mx-auto" style="max-width: 600px;">{{ __('home.cta_desc') }}</p>
            <a href="/register" class="btn btn-light btn-lg px-5 fw-bold rounded-pill" style="color: var(--ot-primary)">{{ __('home.cta_btn') }}</a>
        </div>
    </div>
@endsection