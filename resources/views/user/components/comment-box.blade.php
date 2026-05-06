@php
    $comment_count = \Models\Comment::countComments($type, $target_id);
@endphp

<div class="comment-box ot-card p-4 shadow-sm border-0" id="commentBoxContainer">
    <h5 class="fw-bold mb-4 d-flex justify-content-between align-items-center">
        <span><i class="fas fa-comments text-primary me-2"></i> {{ __('comments.title') }}</span>
        <span class="badge bg-primary-subtle text-primary fw-600 px-3 py-2 rounded-pill fs-12"
            id="commentTotalBadge">{{ $comment_count }} {{ __('comments.comments_count') }}</span>
    </h5>

    {{-- Main Post Form --}}
    @if(is_login())
        <div class="d-flex gap-3 mb-5 border-bottom pb-4">
            <img src="{{ userget(false)->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode(userget(false)->display_name ?? userget(false)->username) . '&background=6366f1&color=fff' }}"
                class="rounded-circle shadow-sm" width="45" height="45" style="object-fit: cover;">
            <div class="flex-grow-1">
                <form id="mainCommentForm">
                    <input type="hidden" name="type" value="{{ $type }}">
                    <input type="hidden" name="target_id" value="{{ $target_id }}">
                    <div class="latex-toolbar d-flex flex-wrap gap-1 mb-2">
                        <button type="button" class="btn btn-sm btn-light border py-0 px-2 fs-12" onclick="insertMath(this, '\\frac{a}{b}')" title="{{ __('comments.fractions') }}">$$\frac{a}{b}$$</button>
                        <button type="button" class="btn btn-sm btn-light border py-0 px-2 fs-12" onclick="insertMath(this, '\\sqrt{x}')" title="{{ __('comments.sqrt') }}">$$\sqrt{x}$$</button>
                        <button type="button" class="btn btn-sm btn-light border py-0 px-2 fs-12" onclick="insertMath(this, 'x^{n}')" title="{{ __('comments.exponent') }}">$$x^{n}$$</button>
                        <button type="button" class="btn btn-sm btn-light border py-0 px-2 fs-12" onclick="insertMath(this, 'x_{i}')" title="{{ __('comments.subscript') }}">$$x_{i}$$</button>
                        <button type="button" class="btn btn-sm btn-light border py-0 px-2 fs-12" onclick="insertMath(this, '\\pi')" title="{{ __('comments.pi') }}">$$\pi$$</button>
                        <button type="button" class="btn btn-sm btn-light border py-0 px-2 fs-12" onclick="insertMath(this, '\\alpha')" title="{{ __('comments.alpha') }}">$$\alpha$$</button>
                        <button type="button" class="btn btn-sm btn-light border py-0 px-2 fs-12" onclick="insertMath(this, '\\Delta')" title="{{ __('comments.delta') }}">$$\Delta$$</button>
                        <button type="button" class="btn btn-sm btn-light border py-0 px-2 fs-12" onclick="insertMath(this, '\\le')" title="{{ __('comments.le') }}">$$\le$$</button>
                        <button type="button" class="btn btn-sm btn-light border py-0 px-2 fs-12" onclick="insertMath(this, '\\ge')" title="{{ __('comments.ge') }}">$$\ge$$</button>
                        <button type="button" class="btn btn-sm btn-light border py-0 px-2 fs-12" onclick="insertMath(this, '\\neq')" title="{{ __('comments.neq') }}">$$\neq$$</button>
                        <button type="button" class="btn btn-sm btn-light border py-0 px-2 fs-12" onclick="insertMath(this, '\\int')" title="{{ __('comments.integral') }}">$$\int$$</button>
                        <button type="button" class="btn btn-sm btn-light border py-0 px-2 fs-12" onclick="insertMath(this, '\\sum')" title="{{ __('comments.sum') }}">$$\sum$$</button>
                        <button type="button" class="btn btn-sm btn-light border py-0 px-2 fs-12" onclick="insertMath(this, '\\to')" title="{{ __('comments.arrow') }}">$$\to$$</button>
                    </div>
                    <textarea name="content" class="form-control bg-light-subtle border-0 shadow-none py-3 px-3 mb-2"
                        rows="3" placeholder="{{ __('comments.form_placeholder') }}"></textarea>
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted fs-11"><i class="fas fa-magic me-1"></i> {{ __('comments.latex_tip') }}</small>
                        <button type="submit" class="btn btn-ot-primary px-4 rounded-pill">{{ __('comments.send_btn') }}</button>
                    </div>
                </form>
            </div>
        </div>
    @else
        <div class="alert alert-primary-subtle border-0 rounded-4 text-center py-4 mb-5 shadow-sm">
            <p class="mb-2 text-dark small fw-500">{{ __('comments.login_required') }}</p>
            <a href="/login" class="btn btn-sm btn-ot-primary px-4 rounded-pill">{{ __('comments.login_now') }}</a>
        </div>
    @endif

    {{-- Comments List Container --}}
    <div id="commentListWrapper">
        <div class="text-center py-4" id="commentLoading">
            <div class="spinner-border text-primary spinner-border-sm" role="status"></div>
            <span class="ms-2 small text-muted">{{ __('comments.loading') }}</span>
        </div>
        <div id="commentList"></div>

        {{-- Pagination --}}
        <div id="commentPagination" class="mt-4 d-flex justify-content-center"></div>
    </div>
</div>



<script>
    let currentCommentPage = 1;
    const commentType = '{{ $type }}';
    const commentTargetId = {{ $target_id }};

    function insertMath(btn, latex) {
        const parent = $(btn).closest('.flex-grow-1');
        const textarea = parent.find('textarea')[0];
        const textToInsert = `$${latex}$`;
        
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const value = textarea.value;
        
        textarea.value = value.substring(0, start) + textToInsert + value.substring(end);
        textarea.selectionStart = textarea.selectionEnd = start + textToInsert.length;
        textarea.focus();
    }

    function loadComments(page = 1) {
        $('#commentLoading').show();
        currentCommentPage = page;

        $.ajax({
            url: '/api/comment/list',
            type: 'GET',
            data: {
                type: commentType,
                target_id: commentTargetId,
                page: page
            },
            success: function (res) {
                $('#commentLoading').hide();
                if (res.status) {
                    $('#commentList').html(res.html);
                    renderPagination(res.paginate);

                    // Render LaTeX
                    if (window.MathJax && window.MathJax.typesetPromise) {
                        window.MathJax.typesetPromise(['#commentList', '.latex-toolbar']);
                    }
                }
            }
        });
    }

    function renderPagination(p) {
        let html = '';
        if (p.total_page > 1) {
            html += '<nav><ul class="pagination pagination-sm mb-0">';
            for (let i = 1; i <= p.total_page; i++) {
                html += `<li class="page-item ${i === p.current_page ? 'active' : ''}">
                    <a class="page-link shadow-none" href="javascript:void(0)" onclick="loadComments(${i})">${i}</a>
                </li>`;
            }
            html += '</ul></nav>';
        }
        $('#commentPagination').html(html);
    }

    $(document).ready(function () {
        loadComments(1);

        if (window.MathJax && window.MathJax.typesetPromise) {
            window.MathJax.typesetPromise(['.latex-toolbar']);
        }

        $('#mainCommentForm').on('submit', function (e) {
            e.preventDefault();
            const content = $(this).find('textarea').val().trim();
            if (!content) return;

            const btn = $(this).find('button');
            const form = $(this);
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

            $.ajax({
                url: '/api/comment/add',
                type: 'POST',
                data: form.serialize(),
                success: function (res) {
                    if (res.status) {
                        form.find('textarea').val('');
                        loadComments(1); // Reload to page 1 to see new comment
                        toastr.success('{{ __('comments.success_msg') }}');
                    } else {
                        toastr.error(res.message);
                    }
                    btn.prop('disabled', false).text('{{ __('comments.send_btn') }}');
                }
            });
        });
    });

    function showReplyForm(id) {
        $('.reply-form-container').addClass('d-none'); // Hide others
        $(`#reply-form-${id}`).removeClass('d-none');
        $(`#reply-form-${id} textarea`).focus();
    }

    function hideReplyForm(id) {
        $(`#reply-form-${id}`).addClass('d-none');
    }

    function submitReply(parentId) {
        const container = $(`#reply-form-${parentId}`);
        const content = container.find('textarea').val().trim();
        if (!content) return;

        const btn = container.find('.btn-ot-primary');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

        $.ajax({
            url: '/api/comment/add',
            type: 'POST',
            data: {
                type: commentType,
                target_id: commentTargetId,
                parent_id: parentId,
                content: content
            },
            success: function (res) {
                if (res.status) {
                    container.find('textarea').val('');
                    container.addClass('d-none');
                    loadComments(currentCommentPage);
                    toastr.success('{{ __('comments.reply_success_msg') }}');
                } else {
                    toastr.error(res.message);
                }
                btn.prop('disabled', false).text('{{ __('comments.reply_btn') }}');
            }
        });
    }
</script>