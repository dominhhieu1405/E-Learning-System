@php
    $blocks = [];
    $rawContent = null;
    $contentStr = $content ?? '';
    
    if (!empty($contentStr)) {
        $decoded = json_decode($contentStr, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded) && isset($decoded[0]['type'])) {
            $blocks = $decoded;
        } else {
            $rawContent = $contentStr;
        }
    }
@endphp

@if(count($blocks) > 0)
    <div class="d-flex flex-column gap-4">
        @foreach($blocks as $block)
            @if($block['type'] === 'preview')
                @php
                    $url = $block['data'] ?? '';
                    if (($block['storage'] ?? '') === 'googledrive' && str_contains($url, 'drive.google.com')) {
                        $url = str_replace('/view', '/preview', $url);
                        $url = preg_replace('/\?usp=[a-zA-Z0-9_]+/', '', $url);
                    }
                @endphp
                <div class="ratio ratio-4x3 border rounded bg-light overflow-hidden shadow-sm">
                    <iframe src="{{ $url }}" allow="autoplay" loading="lazy"></iframe>
                </div>
            @elseif($block['type'] === 'button')
                @php
                    $btnData = $block['data'] ?? [];
                    $icon = $btnData['icon'] ?? 'link';
                    $text = $btnData['text'] ?? 'Mở liên kết';
                    $color = $btnData['color'] ?? 'var(--ot-primary)';
                    $url = $btnData['url'] ?? '#';
                @endphp
                <div class="text-center">
                    <a href="{{ $url }}" target="_blank" class="btn shadow-sm" style="background-color: {{ $color }}; color: #fff; padding: 12px 30px; font-weight: 600; border-radius: 50px; font-size: 1rem;">
                        <i class="fas fa-{{ $icon }} me-2"></i> {{ $text }}
                    </a>
                </div>
            @elseif($block['type'] === 'html' || $block['type'] === 'text')
                <div class="content-html">
                    {!! $block['data'] ?? '' !!}
                </div>
            @endif
        @endforeach
    </div>
@elseif($rawContent !== null)
    {!! $rawContent !!}
@else
    <p class="text-muted text-center italic">Chưa có nội dung chi tiết.</p>
@endif
