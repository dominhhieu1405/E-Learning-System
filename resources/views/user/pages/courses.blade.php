@extends('user.layout.layout')
@section('title', __('courses.title'))
@section('css')
<link rel="stylesheet" href="/assets/css/pages/courses.css">
@endsection
@section('content')
<div class="container py-5">
    <div class="text-center mb-5 animate-fadeInUp">
        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill mb-3 fw-bold">
            {{ __('courses.badge') }}
        </span>
        <h2 class="fw-800 mb-2">{{ __('courses.heading') }}</h2>
        <p class="text-muted">{{ __('courses.subheading') }}</p>
    </div>
    
    @if($data && count($data) > 0)
    <div class="row g-4">
        @foreach($data as $course)
        <div class="col-12 col-md-6 col-lg-3 animate-fadeInUp">
            <div class="ot-card h-100 border-0 shadow-sm course-card overflow-hidden">
                <div class="position-relative">
                    @if($course->image)
                        <img src="{{ $course->image }}" alt="{{ $course->name }}" class="card-img-top" style="height: 180px; object-fit: cover;">
                    @else
                        <div class="card-img-top d-flex align-items-center justify-content-center" style="height: 180px; background:linear-gradient(135deg,var(--ot-primary),var(--ot-secondary));color:#fff;">
                            <i class="fas fa-book fa-3x" style="opacity:0.5;"></i>
                        </div>
                    @endif
                    <div class="course-overlay"></div>
                    <span class="badge bg-white text-primary position-absolute top-0 end-0 m-3 shadow-sm px-2 py-1 rounded-pill small fw-bold">
                        {{ __('courses.new') }}
                    </span>
                </div>
                <div class="card-body p-4">
                    <h5 class="card-title fw-bold mb-2 text-dark-emphasis">{{ $course->name }}</h5>
                    <p class="card-text small text-muted mb-4 line-clamp-2" style="height: 40px;">{{ $course->description ?? __('courses.no_description') }}</p>
                    
                    <div class="d-grid">
                        <a href="/course/{{ $course->id }}" class="btn btn-ot-primary py-2 rounded-3 fw-bold d-flex align-items-center justify-content-center gap-2">
                            <span>{{ __('courses.view_details') }}</span>
                            <i class="fas fa-arrow-right fs-12"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    
    @if(isset($paginate) && $paginate->total_page > 1)
    <div class="d-flex justify-content-center mt-5">
        <nav aria-label="Page navigation">
            <ul class="pagination pagination-md shadow-sm rounded-pill overflow-hidden">
                @for($i = 1; $i <= $paginate->total_page; $i++)
                <li class="page-item {{ $page == $i ? 'active' : '' }}">
                    <a class="page-link px-4 border-0" href="/courses/{{ $i }}">{{ $i }}</a>
                </li>
                @endfor
            </ul>
        </nav>
    </div>
    @endif
    @else
    <div class="text-center py-5">
        <div class="mb-4">
            <i class="fas fa-box-open fa-4x text-muted opacity-25"></i>
        </div>
        <h5 class="text-muted">{{ __('courses.no_courses') }}</h5>
        <p class="text-muted small">{{ __('courses.back_later') }}</p>
    </div>
    @endif
</div>


@endsection