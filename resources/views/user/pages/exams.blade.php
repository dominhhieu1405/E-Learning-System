@extends('user.layout.layout')
@section('title', __('exams.title'))
@section('content')
<div class="container py-4">
    <div class="section-header">
        <h2>{{ __('exams.heading') }}</h2>
    </div>

    {{-- Filter --}}
    <div class="d-flex gap-2 mb-4 flex-wrap">
        <a href="/exams" class="btn {{ !$type ? 'btn-ot-primary' : 'btn-ot-outline' }} btn-sm">{{ __('exams.all') }}</a>
        <a href="/exams?type=thpt" class="btn {{ $type === 'thpt' ? 'btn-ot-primary' : 'btn-ot-outline' }} btn-sm">THPT 2025</a>
        <a href="/exams?type=hsa" class="btn {{ $type === 'hsa' ? 'btn-ot-primary' : 'btn-ot-outline' }} btn-sm">HSA</a>
    </div>

    <div class="row g-4">
        @if($exams && count($exams) > 0)
            @foreach($exams as $exam)
            <div class="col-12 col-md-6 col-lg-4">
                <div class="exam-card {{ $exam->exam_type }}">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="exam-type-badge {{ $exam->exam_type }}">
                            {{ strtoupper($exam->exam_type) }}
                        </span>
                        @if($exam->is_random)
                            <span class="badge bg-warning text-dark"><i class="fas fa-random"></i> {{ __('exams.random') }}</span>
                        @endif
                    </div>
                    <h5 class="fw-bold mb-2">{{ $exam->title }}</h5>
                    <div class="exam-meta">
                        @if($exam->exam_type === 'thpt')
                            <span><i class="fas fa-clock"></i> {{ $exam->duration }} {{ __('exams.minutes') }}</span>
                        @else
                            <span><i class="fas fa-clock"></i> {{ $exam->duration_p1 + $exam->duration_p2 + $exam->duration_p3 }} {{ __('exams.minutes') }}</span>
                            <span><i class="fas fa-layer-group"></i> 3 {{ __('exams.parts') }}</span>
                        @endif
                    </div>
                    <div class="mt-3">
                        <a href="/exam/{{ $exam->id }}" class="btn btn-ot-primary btn-sm w-100">
                            <i class="fas fa-play"></i> {{ __('exams.start_exam') }}
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        @else
            <div class="col-12">
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                    <p>{{ __('exams.no_exams') }}</p>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
