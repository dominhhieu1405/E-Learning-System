@extends('user.layout.layout')
@section('title', $title)
@section('content')

<div class="container py-4">
    <div class="section-header mb-4">
        <h2 class="mb-0">{{ $title }}</h2>
    </div>
    
    <ul class="nav nav-pills mb-4" id="listTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-pill px-4" id="courses-tab" data-bs-toggle="pill" data-bs-target="#courses" type="button" role="tab" aria-controls="courses" aria-selected="true">
                {{ __('common.courses') }}
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill px-4 ms-2" id="documents-tab" data-bs-toggle="pill" data-bs-target="#documents" type="button" role="tab" aria-controls="documents" aria-selected="false">
                {{ __('common.documents') }}
            </button>
        </li>
    </ul>
    
    <div class="tab-content" id="listTabContent">
        {{-- Courses Tab --}}
        <div class="tab-pane fade show active" id="courses" role="tabpanel" aria-labelledby="courses-tab">
            @if($courses && count($courses) > 0)
            <div class="row g-4">
                @foreach($courses as $course)
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="ot-card h-100">
                        @if($course->image)
                            <img src="{{ $course->image }}" alt="{{ $course->name }}" class="card-img-top" style="height: 180px; object-fit: cover;">
                        @else
                            <div class="card-img-top d-flex align-items-center justify-content-center" style="height: 180px; background:linear-gradient(135deg,var(--ot-primary),var(--ot-secondary));color:#fff;">
                                <i class="fas fa-book fa-3x" style="opacity:0.5;"></i>
                            </div>
                        @endif
                        <div class="card-body">
                            <h6 class="card-title fw-bold">{{ $course->name }}</h6>
                            <p class="card-text small text-muted text-truncate">{{ $course->description ?? '' }}</p>
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
            
            @if(isset($courses_paginate) && $courses_paginate->total_page > 1)
            <div class="d-flex justify-content-center mt-5">
                <nav aria-label="Page navigation">
                    <ul class="pagination pagination-sm">
                        @for($i = 1; $i <= $courses_paginate->total_page; $i++)
                        <li class="page-item {{ $page == $i ? 'active' : '' }}">
                            <a class="page-link" href="/{{ $type }}/{{ $data->id }}/{{ $i }}">{{ $i }}</a>
                        </li>
                        @endfor
                    </ul>
                </nav>
            </div>
            @endif
            @else
            <div class="text-center py-5 text-muted">
                <i class="fas fa-box-open fa-3x mb-3 opacity-50"></i>
                <p>{{ __('common.no_courses') }}</p>
            </div>
            @endif
        </div>
        
        {{-- Documents Tab --}}
        <div class="tab-pane fade" id="documents" role="tabpanel" aria-labelledby="documents-tab">
            @if($documents && count($documents) > 0)
            <div class="row g-4">
                @foreach($documents as $doc)
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="ot-card h-100">
                        @if($doc->image)
                            <img src="{{ $doc->image }}" alt="{{ $doc->name }}" class="card-img-top" style="height: 180px; object-fit: cover;">
                        @else
                            <div class="card-img-top d-flex align-items-center justify-content-center" style="height: 180px; background:linear-gradient(135deg,#f59e0b,#ef4444);color:#fff;">
                                <i class="fas fa-file-alt fa-3x" style="opacity:0.5;"></i>
                            </div>
                        @endif
                        <div class="card-body">
                            <h6 class="card-title fw-bold">{{ $doc->name }}</h6>
                            <p class="card-text small text-muted text-truncate">{{ $doc->description ?? '' }}</p>
                            <a href="/document/{{ $doc->id }}" class="stretched-link"></a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            
            @if(isset($documents_paginate) && $documents_paginate->total_page > 1)
            <div class="d-flex justify-content-center mt-5">
                <nav aria-label="Page navigation">
                    <ul class="pagination pagination-sm">
                        @for($i = 1; $i <= $documents_paginate->total_page; $i++)
                        <li class="page-item {{ $page == $i ? 'active' : '' }}">
                            <a class="page-link" href="/{{ $type }}/{{ $data->id }}/{{ $i }}">{{ $i }}</a>
                        </li>
                        @endfor
                    </ul>
                </nav>
            </div>
            @endif
            @else
            <div class="text-center py-5 text-muted">
                <i class="fas fa-box-open fa-3x mb-3 opacity-50"></i>
                <p>{{ __('common.no_documents') }}</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection