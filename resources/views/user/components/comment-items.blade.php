@forelse($comments as $comment)
    <div class="comment-item d-flex gap-3 mb-4 pb-4 {{ !$loop->last ? 'border-bottom border-light-subtle' : '' }}"
        id="comment-{{ $comment->id }}">
        <img src="{{ $comment->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($comment->display_name ?? $comment->username) . '&background=random&color=fff' }}"
            class="rounded-circle shadow-sm" width="40" height="40" style="object-fit: cover;">
        <div class="flex-grow-1">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <h6 class="fw-bold mb-0 small text-wrap">{{ $comment->display_name ?? $comment->username }}</h6>
                <span class="text-muted fs-11"
                    title="{{ $comment->created_at }}">{{ \Services\Blade::timeAgo($comment->created_at) }}</span>
            </div>
            <div class="comment-text text-secondary small mb-2">
                {!! nl2br(e($comment->content)) !!}
            </div>
            <div class="comment-actions d-flex gap-3 align-items-center">
                <button class="btn btn-link btn-sm text-decoration-none p-0 fs-12 text-muted hover-primary"
                    onclick="showReplyForm({{ $comment->id }})">
                    <i class="fas fa-reply me-1"></i> Trả lời
                </button>
            </div>

            {{-- Reply Form (Hidden by default) --}}
            <div id="reply-form-{{ $comment->id }}" class="mt-3 d-none reply-form-container">
                @if(is_login())
                    <div class="d-flex gap-2">
                        <img src="{{ userget(false)->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode(userget(false)->display_name ?? userget(false)->username) . '&background=6366f1&color=fff' }}"
                            class="rounded-circle" width="30" height="30" style="object-fit: cover;">
                        <div class="flex-grow-1">
                            <div class="latex-toolbar d-flex flex-wrap gap-1 mb-2">
                                <button type="button" class="btn btn-sm btn-light border py-0 px-1" style="height:24px; min-width:24px; font-size:10px;" onclick="insertMath(this, '\\frac{a}{b}')" title="Phân số">$$\frac{a}{b}$$</button>
                                <button type="button" class="btn btn-sm btn-light border py-0 px-1" style="height:24px; min-width:24px; font-size:10px;" onclick="insertMath(this, '\\sqrt{x}')" title="Căn bậc 2">$$\sqrt{x}$$</button>
                                <button type="button" class="btn btn-sm btn-light border py-0 px-1" style="height:24px; min-width:24px; font-size:10px;" onclick="insertMath(this, 'x^{n}')" title="Mũ">$$x^{n}$$</button>
                                <button type="button" class="btn btn-sm btn-light border py-0 px-1" style="height:24px; min-width:24px; font-size:10px;" onclick="insertMath(this, '\\pi')" title="Số Pi">$$\pi$$</button>
                                <button type="button" class="btn btn-sm btn-light border py-0 px-1" style="height:24px; min-width:24px; font-size:10px;" onclick="insertMath(this, '\\Delta')" title="Delta">$$\Delta$$</button>
                                <button type="button" class="btn btn-sm btn-light border py-0 px-1" style="height:24px; min-width:24px; font-size:10px;" onclick="insertMath(this, '\\to')" title="Suy ra">$$\to$$</button>
                            </div>
                            <textarea class="form-control form-control-sm bg-light-subtle border-0 mb-2" rows="2"
                                placeholder="Trả lời bình luận này..."></textarea>
                            <div class="text-end">
                                <button class="btn btn-sm btn-link text-muted me-2"
                                    onclick="hideReplyForm({{ $comment->id }})">Hủy</button>
                                <button class="btn btn-ot-primary btn-sm px-3 rounded-pill"
                                    onclick="submitReply({{ $comment->id }})">Phản hồi</button>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="alert alert-light py-2 text-center mb-0 border-0">
                        <small class="text-muted">Bạn cần <a href="/login" class="fw-bold text-primary">đăng nhập</a> để phản
                            hồi</small>
                    </div>
                @endif
            </div>

            {{-- Replies List --}}
            @php $replies = \Models\Comment::getReplies($comment->id); @endphp
            <div class="replies-list-{{ $comment->id }} mt-3 ps-3 border-start border-2 border-light-subtle">
                @foreach($replies as $reply)
                    <div class="reply-item d-flex gap-2 mb-3">
                        <img src="{{ $reply->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($reply->display_name ?? $reply->username) . '&background=random&color=fff' }}"
                            class="rounded-circle" width="30" height="30" style="object-fit: cover;">
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <h6 class="fw-bold mb-0" style="font-size: 13px;">{{ $reply->display_name ?? $reply->username }}
                                </h6>
                                <span class="text-muted fs-10">{{ \Services\Blade::timeAgo($reply->created_at) }}</span>
                            </div>
                            <div class="reply-text text-secondary" style="font-size: 13px;">
                                {!! nl2br(e($reply->content)) !!}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@empty
    <div class="text-center py-5 text-muted opacity-50 no-comments-fallback">
        <i class="far fa-comment-dots fa-3x mb-3"></i>
        <p>Bắt đầu cuộc thảo luận bằng cách gửi bình luận!</p>
    </div>
@endforelse