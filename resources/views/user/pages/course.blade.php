@extends('user.layout.layout')
@section('title', $data->name)
@section('content')
@php
    $units = \Models\Web::courseUnit($data->id) ?? [];
@endphp

<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="ot-card p-4 mb-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/" class="text-decoration-none">{{ __('common.home') }}</a></li>
                        <li class="breadcrumb-item"><a href="/courses" class="text-decoration-none">{{ __('common.courses') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $data->name }}</li>
                    </ol>
                </nav>
                <h2 class="fw-bold fs-3 mb-3">{{ $data->name }}</h2>
                <div class="mb-4 text-muted">
                    <span class="me-3"><i class="fas fa-eye me-1"></i> {{ number_format($data->views ?? 0) }} {{ __('course.views') }}</span>
                </div>
                
                @if($data->image)
                <img src="{{ $data->image }}" alt="{{ $data->name }}" class="img-fluid rounded mb-4 w-100" style="max-height: 400px; object-fit: cover;">
                @endif
                
                <h5 class="fw-bold border-bottom pb-2 mb-3">{{ __('course.intro') }}</h5>
                <div class="mb-4 content-body">
                    {!! $data->description ?? '' !!}
                </div>
                
                <h5 class="fw-bold border-bottom pb-2 mb-3">{{ __('course.content_title') }}</h5>
                @if(count($units) > 0)
                <div class="accordion" id="courseAccordion">
                    @foreach($units as $index => $unit)
                        @php
                            $lessons = \Models\Web::courseUnitLesson($unit->id) ?? [];
                        @endphp
                        <div class="accordion-item mb-2 border rounded">
                            <h2 class="accordion-header" id="heading{{ $unit->id }}">
                                <button class="accordion-button {{ $index == 0 ? '' : 'collapsed' }} fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $unit->id }}" aria-expanded="{{ $index == 0 ? 'true' : 'false' }}" aria-controls="collapse{{ $unit->id }}">
                                    {{ __('course.part') }} {{ $index + 1 }}: {{ $unit->name }} 
                                    <span class="badge bg-secondary ms-2">{{ count($lessons) }} {{ __('course.lessons_count') }}</span>
                                </button>
                            </h2>
                            <div id="collapse{{ $unit->id }}" class="accordion-collapse collapse {{ $index == 0 ? 'show' : '' }}" aria-labelledby="heading{{ $unit->id }}" data-bs-parent="#courseAccordion">
                                <div class="accordion-body p-0">
                                    <div class="list-group list-group-flush">
                                        @forelse($lessons as $lesson)
                                        <a href="/lesson/{{ $lesson->id }}" class="list-group-item list-group-item-action py-3">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-play-circle text-primary fs-5 me-3"></i>
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-0 fw-semibold">{{ $lesson->name }}</h6>
                                                    @if($lesson->description)
                                                    <small class="text-muted">{{ $lesson->description }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </a>
                                        @empty
                                        <div class="p-3 text-muted text-center small">{{ __('course.no_lessons') }}</div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                @else
                <div class="alert alert-info py-2">{{ __('course.updating') }}</div>
                @endif
                
                <div class="mt-5">
                    @include('user.components.comment-box', ['type' => 'course', 'target_id' => $data->id])
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="position-sticky" style="top: 80px;">
                <div class="ot-card p-4">
                    <h5 class="fw-bold mb-4 border-bottom pb-2">{{ __('course.info_title') }}</h5>
                    <ul class="list-unstyled mb-4">
                        <li class="mb-3 d-flex align-items-center">
                            <i class="fas fa-layer-group text-primary me-3 fs-5" style="width:24px;"></i>
                            <div>
                                <div class="small text-muted">{{ __('course.level') }}</div>
                                <div class="fw-bold">{{ $data->class_name ?? 'Lớp 12' }}</div>
                            </div>
                        </li>
                        <li class="mb-3 d-flex align-items-center">
                            <i class="fas fa-book-open text-success me-3 fs-5" style="width:24px;"></i>
                            <div>
                                <div class="small text-muted">{{ __('course.subject') }}</div>
                                <div class="fw-bold">{{ $data->subject_name ?? 'Toán học' }}</div>
                            </div>
                        </li>
                        <li class="mb-3 d-flex align-items-center">
                            <i class="fas fa-clock text-warning me-3 fs-5" style="width:24px;"></i>
                            <div>
                                <div class="small text-muted">{{ __('course.duration') }}</div>
                                <div class="fw-bold">{{ __('course.lifetime') }}</div>
                            </div>
                        </li>
                        <li class="mb-3 d-flex align-items-center">
                            <i class="fas fa-certificate text-danger me-3 fs-5" style="width:24px;"></i>
                            <div>
                                <div class="small text-muted">{{ __('course.certificate') }}</div>
                                <div class="fw-bold">{{ __('course.has_certificate') }}</div>
                            </div>
                        </li>
                    </ul>
                    
                    <div class="d-grid gap-2">
                        @if(count($units) > 0)
                            @php 
                                $firstUnit = $units[0];
                                $lessons = \Models\Web::courseUnitLesson($firstUnit->id) ?? [];
                                $firstLesson = $lessons[0] ?? null; 
                            @endphp
                            @if($firstLesson)
                                <a href="/lesson/{{ $firstLesson->id }}" class="btn btn-ot-primary btn-lg shadow-sm">
                                    {{ __('course.start_study') }}
                                </a>
                            @endif
                        @else
                            <button class="btn btn-secondary disabled btn-lg">{{ __('course.coming_soon') }}</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection