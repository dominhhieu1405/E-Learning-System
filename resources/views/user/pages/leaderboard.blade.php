@extends('user.layout.layout')
@section('title', __('leaderboard.heading') . ' - ' . $exam->title)
@section('css')
<link rel="stylesheet" href="/assets/css/pages/leaderboard.css">
@endsection
@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="text-center mb-5 animate-fadeInUp">
                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-2 rounded-pill mb-3 fw-bold">
                    {{ __('leaderboard.badge') }}
                </span>
                <h2 class="fw-800 mb-2">{{ __('leaderboard.heading') }}</h2>
                <p class="text-muted lead">{{ $exam->title }}</p>
            </div>

            {{-- Top 3 Podium --}}
            @if($leaderboard && count($leaderboard) > 0)
            <div class="row g-4 mb-5 align-items-end justify-content-center">
                {{-- Rank 2 --}}
                @if(isset($leaderboard[1]))
                <div class="col-4 col-md-3 order-1 order-md-1 animate-fadeInUp" style="animation-delay: 0.1s">
                    <div class="ot-card p-3 text-center border-0 shadow-sm" style="background: linear-gradient(180deg, #f8fafc, #e2e8f0);">
                        <div class="mb-2 position-relative d-inline-block">
                            <span class="position-absolute translate-middle-x start-50 top-0 mt-n3" style="font-size: 1.5rem">🥈</span>
                            <div class="rounded-circle border border-2 border-white shadow-sm overflow-hidden mx-auto" style="width: 60px; height: 60px;">
                                <img src="{{ $leaderboard[1]->avatar ?: 'https://ui-avatars.com/api/?name='.urlencode($leaderboard[1]->display_name).'&background=64748b&color=fff' }}" class="img-fluid" style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                        </div>
                        <h6 class="fw-bold mb-1 text-truncate">{{ $leaderboard[1]->display_name }}</h6>
                        <div class="badge bg-primary rounded-pill px-3">{{ $leaderboard[1]->total_score }}đ</div>
                    </div>
                </div>
                @endif

                {{-- Rank 1 --}}
                <div class="col-5 col-md-4 order-0 order-md-2 animate-fadeInUp">
                    <div class="ot-card p-4 text-center border-0 shadow-lg" style="background: linear-gradient(180deg, #fffbeb, #fef3c7); transform: scale(1.1); z-index: 10;">
                        <div class="mb-2 position-relative d-inline-block">
{{--                            <span class="position-absolute translate-middle-x start-50 top-0 mt-n4" style="font-size: 2.5rem">👑</span>--}}
                            <div class="rounded-circle border border-4 border-warning shadow-sm overflow-hidden mx-auto" style="width: 80px; height: 80px;">
                                <img src="{{ $leaderboard[0]->avatar ?: 'https://ui-avatars.com/api/?name='.urlencode($leaderboard[0]->display_name).'&background=f59e0b&color=fff' }}" class="img-fluid" style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                        </div>
                        <h5 class="fw-800 mb-1 text-truncate">{{ $leaderboard[0]->display_name }}</h5>
                        <div class="badge bg-warning text-dark rounded-pill px-4 py-2 fs-6 mb-2">{{ $leaderboard[0]->total_score }}đ</div>
                        <p class="small text-muted mb-0"><i class="far fa-clock me-1"></i> {{ gmdate('H:i:s', $leaderboard[0]->time_spent) }}</p>
                    </div>
                </div>

                {{-- Rank 3 --}}
                @if(isset($leaderboard[2]))
                <div class="col-4 col-md-3 order-2 order-md-3 animate-fadeInUp" style="animation-delay: 0.2s">
                    <div class="ot-card p-3 text-center border-0 shadow-sm" style="background: linear-gradient(180deg, #fff7ed, #ffedd5);">
                        <div class="mb-2 position-relative d-inline-block">
                            <span class="position-absolute translate-middle-x start-50 top-0 mt-n3" style="font-size: 1.5rem">🥉</span>
                            <div class="rounded-circle border border-2 border-white shadow-sm overflow-hidden mx-auto" style="width: 60px; height: 60px;">
                                <img src="{{ $leaderboard[2]->avatar ?: 'https://ui-avatars.com/api/?name='.urlencode($leaderboard[2]->display_name).'&background=d97706&color=fff' }}" class="img-fluid" style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                        </div>
                        <h6 class="fw-bold mb-1 text-truncate">{{ $leaderboard[2]->display_name }}</h6>
                        <div class="badge bg-primary rounded-pill px-3">{{ $leaderboard[2]->total_score }}đ</div>
                    </div>
                </div>
                @endif
            </div>
            @endif

            <div class="ot-card border-0 shadow-sm animate-fadeInUp mb-4" style="animation-delay: 0.3s">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 custom-leaderboard-table">
                        <thead class="text-muted small text-uppercase fw-bold">
                            <tr>
                                <th width="60" class="ps-4">{{ __('leaderboard.rank') }}</th>
                                <th>{{ __('leaderboard.candidate') }}</th>
                                <th class="text-center">{{ __('leaderboard.score') }}</th>
                                <th class="text-center d-none d-md-table-cell">{{ __('leaderboard.time') }}</th>
                                <th class="text-center pe-4 d-none d-md-table-cell">{{ __('leaderboard.date') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($leaderboard && count($leaderboard) > 0)
                                @foreach($leaderboard as $i => $entry)
                                <tr class="{{ (is_login() && userget()->id == $entry->user_id) ? 'table-active' : '' }}">
                                    <td class="ps-4">
                                        @if($i == 0) <span class="badge bg-warning text-dark rounded-circle" style="width:24px; height:24px; padding: 5px 0;">1</span>
                                        @elseif($i == 1) <span class="badge bg-secondary text-white rounded-circle" style="width:24px; height:24px; padding: 5px 0;">2</span>
                                        @elseif($i == 2) <span class="badge bg-bronze text-white rounded-circle" style="width:24px; height:24px; padding: 5px 0;">3</span>
                                        @else <span class="fw-800 text-muted">#{{ $i + 1 }}</span> @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-light me-2 overflow-hidden border" style="width: 32px; height: 32px; flex-shrink: 0;">
                                                <img src="{{ $entry->avatar ?: 'https://ui-avatars.com/api/?name='.urlencode($entry->display_name).'&background=random' }}" style="width: 100%; height: 100%; object-fit: cover;">
                                            </div>
                                            <div class="text-truncate" style="max-width: 150px;">
                                                <div class="fw-bold small">{{ $entry->display_name ?? __('leaderboard.anonymous') }}</div>
                                                @if(is_login() && userget()->id == $entry->user_id)
                                                    <span class="text-primary fw-bold" style="font-size: 9px">{{ __('leaderboard.you') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center fw-bold text-primary fs-5">{{ $entry->total_score }}</td>
                                    <td class="text-center text-muted small d-none d-md-table-cell"><i class="far fa-clock me-1"></i> {{ $entry->time_spent ? gmdate('H:i:s', $entry->time_spent) : '—' }}</td>
                                    <td class="text-center text-muted small pe-4 d-none d-md-table-cell">{{ date('d/m/Y', strtotime($entry->created_at)) }}</td>
                                </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <div class="mb-2" style="font-size: 3rem">🏜️</div>
                                        <div>{{ __('leaderboard.empty') }}</div>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>



            <div class="text-center mt-5">
                <a href="/exams" class="btn btn-ot-outline px-4"><i class="fas fa-arrow-left me-2"></i> {{ __('leaderboard.back_to_exams') }}</a>
            </div>
        </div>
    </div>
</div>
@endsection
