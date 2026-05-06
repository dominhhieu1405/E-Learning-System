@extends('user.layout.layout')
@section('title', __('result.title_prefix') . $exam->title)
@section('css')
<link rel="stylesheet" href="/assets/css/exam.css?v=3">
<link rel="stylesheet" href="/assets/css/pages/result.css">
@endsection
@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            {{-- Score Card --}}
            <div class="score-card">
                <div class="mb-2"><i class="fas fa-trophy fa-2x" style="color:#f59e0b;"></i></div>
                <h5>{{ __('result.result_title') }}{{ $exam->title }}</h5>
                <div class="score-main">{{ $session->total_score }}</div>
                <div class="score-parts">
                    <div class="score-part">
                        <div class="label">{{ __('result.part') }} 1</div>
                        <div class="value">{{ $session->score_p1 }}</div>
                    </div>
                    <div class="score-part">
                        <div class="label">{{ __('result.part') }} 2</div>
                        <div class="value">{{ $session->score_p2 }}</div>
                    </div>
                    <div class="score-part">
                        <div class="label">{{ __('result.part') }} 3</div>
                        <div class="value">{{ $session->score_p3 }}</div>
                    </div>
                </div>
                <div class="mt-2" style="font-size:0.85rem;opacity:0.7;">
                    {{ __('result.time') }}: {{ gmdate('H:i:s', $session->time_spent ?: 0) }}
                </div>
            </div>

            <div class="d-flex gap-2 mb-4">
                <a href="/exams" class="btn btn-ot-outline"><i class="fas fa-arrow-left"></i> {{ __('result.back_to_list') }}</a>
                <a href="/leaderboard/{{ $exam->id }}" class="btn btn-ot-primary"><i class="fas fa-trophy"></i> {{ __('result.leaderboard') }}</a>
            </div>

            {{-- Review Questions --}}
            <h5 class="fw-bold mb-3">{{ __('result.review_title') }}</h5>
            
            @if(!$exam->show_answers)
                <div class="alert alert-info border-0 shadow-sm p-4">
                    <div class="d-flex align-items-center gap-3">
                        <i class="fas fa-lock fa-2x text-primary"></i>
                        <div>
                            <h6 class="fw-bold mb-1">{{ __('result.hidden_answers_title') }}</h6>
                            <p class="mb-0 small text-muted">{{ __('result.hidden_answers_desc') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @foreach($questions as $i => $q)
            @php
                $qId = (string)$q->id;
                $userAnswer = $answers[$qId] ?? null;
                $isCorrect = false;
                if($q->question_type === 'mc') {
                    $isCorrect = $userAnswer && strtoupper(trim($userAnswer)) === strtoupper(trim($q->correct_answer));
                } elseif($q->question_type === 'short') {
                    $isCorrect = $userAnswer && trim(mb_strtolower($userAnswer)) === trim(mb_strtolower($q->correct_answer));
                }
            @endphp
            <div class="review-question">
                <div class="q-header">
                    <span class="q-num">{{ __('result.question') }} {{ $i+1 }}</span>
                    @if($q->question_type === 'mc' || $q->question_type === 'short')
                        @if($userAnswer === null)
                            <span class="q-result unanswered">{{ __('result.unanswered') }}</span>
                        @elseif($isCorrect)
                            <span class="q-result correct"><i class="fas fa-check"></i> {{ __('result.correct') }}</span>
                        @else
                            <span class="q-result wrong"><i class="fas fa-times"></i> {{ __('result.wrong') }}</span>
                        @endif
                    @elseif($q->question_type === 'tf')
                        <span class="badge bg-warning text-dark">{{ __('result.tf_label') }}</span>
                    @endif
                </div>
                <div class="mb-2">{!! $q->content !!}</div>

                @if($q->question_type === 'mc')
                    @php $opts = is_string($q->options) ? json_decode($q->options, true) : ($q->options ?? []) @endphp
                    @foreach(['A','B','C','D'] as $li => $letter)
                        @if(isset($opts[$li]))
                        <div class="option-item {{ $userAnswer === $letter ? 'selected' : '' }} {{ $q->correct_answer === $letter ? 'correct' : '' }} {{ ($userAnswer === $letter && $q->correct_answer !== $letter) ? 'incorrect' : '' }}" style="cursor:default">
                            <span class="option-label">{{ $letter }}.</span>
                            <span class="option-text">{{ $opts[$li] }}</span>
                        </div>
                        @endif
                    @endforeach
                @elseif($q->question_type === 'tf')
                    @php $tfItems_q = $tfItems[$q->id] ?? []; $tfAnswer = $userAnswer ?? []; @endphp
                    <table class="tf-table" style="width:100%;">
                        <thead><tr><th></th><th>{{ __('result.your_choice') }}</th><th>{{ __('result.answer') }}</th></tr></thead>
                        <tbody>
                        @foreach($tfItems_q as $item)
                            @php
                                $k = (string)$item->order;
                                $userVal = isset($tfAnswer[$k]) ? ($tfAnswer[$k] ? 'Đ' : 'S') : '—';
                                $correctVal = $item->is_correct ? 'Đ' : 'S';
                                $match = isset($tfAnswer[$k]) && ((bool)$tfAnswer[$k] === (bool)$item->is_correct);
                            @endphp
                            <tr>
                                <td>{{ chr(96+$item->order) }}) {{ $item->content }}</td>
                                <td class="text-center"><span class="{{ $match ? 'text-success' : 'text-danger' }} fw-bold">{{ $userVal }}</span></td>
                                <td class="text-center fw-bold">{{ $correctVal }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @elseif($q->question_type === 'short')
                    <div class="d-flex gap-3 align-items-center flex-wrap mt-2">
                        <div>{{ __('result.you') }}: <strong class="{{ $isCorrect ? 'text-success' : 'text-danger' }}">{{ $userAnswer ?: __('result.empty') }}</strong></div>
                        <div>{{ __('result.answer') }}: <strong class="text-success">{{ $q->correct_answer }}</strong></div>
                    </div>
                @endif

                @if($q->explanation)
                <div class="mt-2 p-2 bg-light rounded" style="font-size:0.875rem;">
                    <strong><i class="fas fa-lightbulb text-warning"></i> {{ __('result.explanation') }}:</strong> {!! $q->explanation !!}
                </div>
                @endif
            </div>
            @endforeach

        </div>
    </div>
</div>
@endsection

@section('javascript_plugins')
<script>
    $(document).ready(function() {
        if(window.MathJax && window.MathJax.typesetPromise) {
            window.MathJax.typesetPromise();
        }
    });
</script>
@endsection
