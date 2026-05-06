<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
    <title>{{ $exam->title }} — OnThi.io.vn</title>
    <link rel="icon" href="/favicon.ico"/>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/fontawesome.min.css">
    <link rel="stylesheet" href="/assets/css/main.css?v=3">
    <link rel="stylesheet" href="/assets/css/exam.css?v=3">
    <script src="/assets/js/jquery.min.js"></script>
    {{-- MathJax --}}
    <script>
        window.MathJax = {
            tex: {
                inlineMath: [['$', '$'], ['\\(', '\\)']],
                displayMath: [['$$', '$$'], ['\\[', '\\]']],
                processEscapes: true
            },
            options: { enableMenu: false }
        };
    </script>
    <script src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js" async></script>
    <script src="/assets/libs/toastr/build/toastr.min.js"></script>
</head>
<body class="{{ (isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark') ? 'dark' : '' }}">

<div id="examLoadingOverlay" style="
    position: fixed; inset: 0; z-index: 9999;
    display: flex; align-items: center; justify-content: center;
    background: var(--bg-body, #f4f6fb);
">
    <div class="text-center">
        <div class="spinner-border text-primary mb-3" style="width:3rem;height:3rem;" role="status"></div>
        <p class="fw-semibold text-muted">{{ __('exam_taking.loading') }}</p>
    </div>
</div>

<div class="exam-container">
    {{-- Header --}}
    <div class="exam-header">
        <div class="d-flex align-items-center gap-3">
            <span class="exam-title">{{ $exam->title }}</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <div class="exam-timer">
                <i class="fas fa-clock"></i>
                <span id="timerDisplay">00:00</span>
            </div>
            {{-- Mobile nav toggle --}}
            <button class="btn btn-sm btn-light d-md-none" id="btnNavToggle">
                <i class="fas fa-th"></i>
            </button>
        </div>
    </div>

    {{-- Body --}}
    <div class="exam-body">
        {{-- Question --}}
        <div class="exam-question-panel" id="questionContent">
            {{-- Rendered by JS --}}
        </div>

        {{-- Navigator (PC only) --}}
        <div class="exam-navigator d-none d-md-block d-flex flex-column">
            <h6 class="fw-bold mb-3">{{ __('exam_taking.nav_list') }}</h6>
            <div class="flex-grow-1 overflow-auto">
                <div class="nav-grid" id="navGrid"></div>
                <div class="mt-3" id="legendPC">
                    <div class="d-flex align-items-center gap-1 mb-1" style="font-size:0.75rem;">
                        <div class="nav-btn answered" style="width:16px;height:16px;font-size:0;"></div>
                        <span>{{ __('exam_taking.answered') }}</span>
                    </div>
                </div>
            </div>
            
            <div class="mt-auto pt-3 border-top" id="navigatorActionPC">
                <button class="btn btn-warning w-100 py-2 fw-bold mb-2" id="btnLockPartPC" style="display:none;">
                    <i class="fas fa-lock"></i> {{ __('exam_taking.lock_part') }} <span class="current-part-num"></span>
                </button>
                <button class="btn btn-danger w-100 py-2 fw-bold" id="btnSubmitPC" style="display:none;">
                    <i class="fas fa-paper-plane"></i> {{ __('exam_taking.submit') }}
                </button>
            </div>
        </div>
    </div>

    {{-- Footer --}}
    <div class="exam-footer">
        <button class="btn btn-ot-outline btn-sm" id="btnPrev"><i class="fas fa-arrow-left"></i> {{ __('exam_taking.prev') }}</button>
        
        <span class="exam-part-footer badge bg-info" id="partLabel" style="position: absolute; left: 50%; transform: translateX(-50%);">
            {{ __('exam_taking.part') }} {{ $currentPart }}/3
        </span>
        
        <div class="d-flex gap-2">
            <button class="btn btn-ot-outline btn-sm" id="btnNext">{{ __('exam_taking.next') }} <i class="fas fa-arrow-right"></i></button>
        </div>
    </div>
</div>

{{-- Mobile Offcanvas Navigator --}}
<div class="offcanvas offcanvas-end offcanvas-nav" tabindex="-1" id="navOffcanvas">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title">{{ __('exam_taking.nav_list') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column">
        <div class="flex-grow-1">
            <div class="nav-grid" id="navGridMobile"></div>
        </div>
        <div class="mt-auto pt-3 border-top" id="navigatorActionMobile">
            <button class="btn btn-warning w-100 py-3 fw-bold mb-2" id="btnLockPartMobile" style="display:none;">
                <i class="fas fa-lock"></i> {{ __('exam_taking.lock_part') }} <span class="current-part-num"></span>
            </button>
            <button class="btn btn-danger w-100 py-3 fw-bold" id="btnSubmitMobile" style="display:none;">
                <i class="fas fa-paper-plane"></i> {{ __('exam_taking.submit_exam') }}
            </button>
        </div>
    </div>
</div>

{{-- Confirmation Modal --}}
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-body p-4 text-center">
                <div class="mb-3">
                    <i class="fas fa-question-circle fa-3x text-warning"></i>
                </div>
                <h5 class="fw-bold mb-2" id="confirmTitle">{{ __('exam_taking.confirm_title') }}</h5>
                <p class="text-muted small mb-4" id="confirmMessage">{{ __('exam_taking.confirm_message') }}</p>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-light flex-grow-1" data-bs-dismiss="modal">{{ __('exam_taking.cancel') }}</button>
                    <button type="button" class="btn btn-ot-primary flex-grow-1" id="btnConfirmAction">{{ __('exam_taking.agree') }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/exam.js?v=3"></script>
<script>
(function () {
    var SESSION_KEY  = '{{ $session->session_key }}';
    var EXAM_TYPE    = '{{ $exam->exam_type }}';
    var CURRENT_PART = {{ $currentPart }};
    var REMAINING    = {{ $remainingTime }};
    var SAVED        = @json($savedAnswers);

    // Hiện loading overlay
    var overlay = document.getElementById('examLoadingOverlay');

    $.ajax({
        url: '/api/exam/questions',
        method: 'GET',
        data: { session_key: SESSION_KEY },
        dataType: 'json',
        success: function (res) {
            if (!res.status) {
                showLoadError(res.message || '{{ __('exam_taking.error_loading') }}');
                return;
            }

            var questionsData = res.questions || [];
            var tfItemsData   = res.tf_items  || {};

            // Gắn tf_items vào câu hỏi
            questionsData.forEach(function (q) {
                if (q.question_type === 'tf' && tfItemsData[q.id]) {
                    q.tf_items = tfItemsData[q.id];
                }
            });

            // Ẩn overlay rồi khởi động ứng dụng thi
            if (overlay) overlay.style.display = 'none';

            ExamApp.init({
                sessionKey   : SESSION_KEY,
                examType     : EXAM_TYPE,
                currentPart  : CURRENT_PART,
                questions    : questionsData,
                remainingTime: REMAINING,
                savedAnswers : SAVED,
            });
        },
        error: function (xhr) {
            var msg = '{{ __('exam_taking.error_connection') }}';
            try { msg = JSON.parse(xhr.responseText).message || msg; } catch (e) {}
            showLoadError(msg);
        }
    });

    function showLoadError(msg) {
        if (overlay) {
            overlay.innerHTML =
                '<div class="text-center p-5">' +
                '<i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>' +
                '<p class="fw-bold">' + msg + '</p>' +
                '<a href="/exams" class="btn btn-ot-primary mt-2">{{ __('exam_taking.back_to_list') }}</a>' +
                '</div>';
        }
    }
})();
</script>
</body>
</html>
