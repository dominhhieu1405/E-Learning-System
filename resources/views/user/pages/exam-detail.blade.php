@extends('user.layout.layout')
@section('title', $title)
@section('css')
<link rel="stylesheet" href="/assets/css/pages/exam-detail.css">
@endsection
@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/" class="text-decoration-none">{{ __('common.home') }}</a></li>
            <li class="breadcrumb-item"><a href="/exams" class="text-decoration-none">{{ __('common.exams') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $exam->title }}</li>
        </ol>
    </nav>

    <div class="row g-4">
        {{-- Main Info --}}
        <div class="col-lg-8">
            <div class="ot-card p-4 mb-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle mb-2">{{ strtoupper($exam->exam_type) }}</span>
                        <h2 class="fw-bold mb-0 text-gradient-primary">{{ $exam->title }}</h2>
                    </div>
                    @if($exam->is_random)
                        <span class="badge bg-warning text-white rounded-pill px-3 py-2 shadow-sm">
                            <i class="fas fa-random me-1"></i> {{ __('exams.random_exam') }}
                        </span>
                    @endif
                </div>

                @if(isset($_GET['error']))
                    <div class="alert alert-danger border-0 shadow-sm mb-4 d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle me-3 fa-lg"></i>
                        <div>{{ urldecode($_GET['error']) }}</div>
                    </div>
                @endif

                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-3">
                        <div class="p-3 bg-light rounded text-center h-100">
                            <div class="text-muted small mb-1">{{ __('exams.duration_label') }}</div>
                            <div class="fw-bold fs-5">
                                @if($exam->exam_type === 'thpt')
                                    {{ $exam->duration }}'
                                @else
                                    {{ $exam->duration_p1 + $exam->duration_p2 + $exam->duration_p3 }}'
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 bg-light rounded text-center h-100">
                            <div class="text-muted small mb-1">{{ __('exams.questions_count') }}</div>
                            <div class="fw-bold fs-5">{{ $question_count }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 bg-light rounded text-center h-100">
                            <div class="text-muted small mb-1">{{ __('exams.attempts_count') }}</div>
                            <div class="fw-bold fs-5">{{ $exam->attempt_limit > 0 ? $exam->attempt_limit : '∞' }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 bg-light rounded text-center h-100">
                            <div class="text-muted small mb-1">{{ __('exams.format_label') }}</div>
                            <div class="fw-bold fs-5">Online</div>
                        </div>
                    </div>
                </div>

                <div class="exam-description content-body mb-4">
                    <h5 class="fw-bold mb-3 border-start border-4 border-primary ps-3">{{ __('exams.instruction_title') }}</h5>
                    {!! $exam->description ?: '<p class="text-muted italic">'.__('exams.no_description').'</p>' !!}
                    <ul class="mt-3 text-muted small">
                        <li>{{ __('exams.tip_1') }}</li>
                        <li>{{ __('exams.tip_2') }}</li>
                        <li>{{ __('exams.tip_3') }}</li>
                    </ul>
                </div>

                <div class="d-grid gap-2 d-md-flex">
                    <a href="/exam/{{ $exam->id }}/start" class="btn btn-ot-primary btn-lg px-5 shadow-lg">
                        <i class="fas fa-play me-2"></i> {{ __('exams.start_exam_now') }}
                    </a>
                    <a href="/leaderboard/{{ $exam->id }}" class="btn btn-ot-outline btn-lg px-4">
                        <i class="fas fa-medal me-2"></i> {{ __('exams.leaderboard') }}
                    </a>
                </div>
            </div>

            {{-- Comment Section Placeholder --}}
            <div id="commentSection" class="mt-4">
                 @include('user.components.comment-box', ['type' => 'exam', 'target_id' => $exam->id])
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            {{-- Top Performers Preview --}}
            <div class="ot-card p-4 mb-4">
                <h5 class="fw-bold mb-3 d-flex align-items-center">
                    <i class="fas fa-trophy text-warning me-2"></i> {{ __('exams.honor') }}
                </h5>
                @if(count($leaderboard) > 0)
                    <div class="list-group list-group-flush">
                        @foreach($leaderboard as $index => $top)
                            <div class="list-group-item px-0 border-0 d-flex justify-content-between align-items-center bg-transparent">
                                <div class="d-flex align-items-center">
                                    <span class="badge {{ $index == 0 ? 'bg-warning' : ($index == 1 ? 'bg-secondary' : ($index == 2 ? 'bg-bronze' : 'bg-light text-dark')) }} rounded-circle me-3" style="width: 24px; height: 24px; line-height: 14px;">{{ $index + 1 }}</span>
                                    <span class="small fw-600">{{ $top->display_name }}</span>
                                </div>
                                <span class="text-primary fw-bold">{{ round($top->total_score, 2) }}</span>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-3 border-top pt-3 text-center">
                        <a href="/leaderboard/{{ $exam->id }}" class="text-decoration-none small">{{ __('exams.view_all') }} <i class="fas fa-external-link-alt ms-1"></i></a>
                    </div>
                @else
                    <div class="text-center py-3 text-muted small italic">{{ __('exams.no_ranking') }}</div>
                @endif
            </div>

            {{-- Tips Card --}}
            <div class="ot-card p-4 bg-primary text-white border-0 shadow-lg">
                <h5 class="fw-bold mb-3"><i class="fas fa-lightbulb me-2"></i> {{ __('exams.tips_title') }}</h5>
                <p class="small opacity-75 mb-0">{{ __('exams.tips_desc') }}</p>
            </div>
        </div>
    </div>
</div>


@endsection
