@extends('user.layout.layout')
@section('title', $data->name)
@section('content')
@php
    $units = \Models\Web::courseUnit($course->id) ?? [];
@endphp

<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/" class="text-decoration-none">{{ __('common.home') }}</a></li>
            <li class="breadcrumb-item"><a href="/courses" class="text-decoration-none">{{ __('common.courses') }}</a></li>
            <li class="breadcrumb-item"><a href="/course/{{ $course->id }}" class="text-decoration-none">{{ $course->name }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $data->name }}</li>
        </ol>
    </nav>
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="ot-card p-4">
                <h3 class="fw-bold mb-3">{{ $data->name }}</h3>
                
                @php 
                    $allParts = [];
                    // Add legacy single video if present
                    if (!empty($data->video)) {
                        $allParts[] = [
                            'text' => __('lesson.part') . ' 1',
                            'url' => $data->video
                        ];
                    }
                    
                    // Add additional videos from JSON
                    $extraVideos = json_decode($data->videos ?? '[]', true);
                    foreach($extraVideos as $vid) {
                        // Avoid duplication if the URL is already in the list
                        $exists = false;
                        foreach($allParts as $part) {
                            if ($part['url'] === $vid['url']) $exists = true;
                        }
                        if (!$exists) {
                            $allParts[] = [
                                'text' => $vid['text'] ?? (__('lesson.part') . ' ' . (count($allParts) + 1)),
                                'url' => $vid['url']
                            ];
                        }
                    }
                    
                    $firstPart = $allParts[0] ?? null;
                @endphp

                <div class="video-player-section mb-4">
                    @if($firstPart)
                        <div class="ratio ratio-16x9 mb-3 bg-black rounded overflow-hidden shadow">
                            <iframe id="mainVideoPlayer" src="" title="Video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                        </div>

                        {{-- Only show part buttons if there is more than 1 part --}}
                        @if(count($allParts) > 1)
                            <div class="video-parts-nav d-flex flex-wrap gap-2 mb-3">
                                @foreach($allParts as $index => $part)
                                    <button class="btn btn-sm btn-ot-outline video-part-btn {{ $index == 0 ? 'active' : '' }}" 
                                            data-url="{{ $part['url'] }}" 
                                            onclick="changeVideo(this)">
                                        <i class="fas fa-play-circle me-1"></i> {{ $part['text'] }}
                                    </button>
                                @endforeach
                            </div>
                        @else
                            {{-- Hidden anchor for the single video to be picked up by JS --}}
                            <div class="video-part-btn d-none" data-url="{{ $firstPart['url'] }}"></div>
                        @endif
                    @endif
                </div>
                
                <div class="lesson-content content-body mb-4">
                    {!! $data->description ?? '' !!}
                </div>

                @php 
                    $files = json_decode($data->files ?? '[]', true);
                @endphp

                @if(!empty($files))
                <h5 class="fw-bold border-bottom pb-2 mb-3 mt-4">{{ __('lesson.attachment_title') }}</h5>
                <div class="list-group">
                    @foreach($files as $f)
                        <a href="{{ $f['url'] }}" target="_blank" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3">
                            <span><i class="fas fa-file-download text-primary me-2"></i> {{ $f['text'] ?: __('lesson.download_file') }}</span>
                            <span class="badge bg-primary rounded-pill"><i class="fas fa-download"></i></span>
                        </a>
                    @endforeach
                </div>
                @endif
            </div>
            
            <div class="d-flex justify-content-between mt-4 p-3 bg-light rounded shadow-sm">
                @if($prev)
                    <a href="/lesson/{{ $prev->id }}" class="btn btn-ot-outline btn-sm">
                        <i class="fas fa-chevron-left me-1"></i> {{ __('lesson.prev_lesson') }}
                    </a>
                @else
                    <div></div>
                @endif
                
                <a href="/course/{{ $course->id }}" class="btn btn-link text-decoration-none text-muted small px-0">
                    <i class="fas fa-list me-1"></i> {{ __('lesson.toc') }}
                </a>

                @if($next)
                    <a href="/lesson/{{ $next->id }}" class="btn btn-ot-primary btn-sm">
                        {{ __('lesson.next_lesson') }} <i class="fas fa-chevron-right ms-1"></i>
                    </a>
                @else
                    <button class="btn btn-success btn-sm disabled"><i class="fas fa-check-circle me-1"></i> {{ __('lesson.finish_course') }}</button>
                @endif
            </div>
        </div>
        
        {{-- Playlist section --}}
        <div class="col-lg-4">
            <div class="ot-card p-0">
                <div class="p-3 bg-light border-bottom border-top-0 border-end-0 border-start-0 rounded-top">
                    <h5 class="fw-bold mb-0">{{ __('lesson.course_content') }}</h5>
                </div>
                <div class="accordion accordion-flush" id="courseAccordion">
                    @foreach($units as $index => $unit)
                        @php
                            $lessons = \Models\Web::courseUnitLesson($unit->id) ?? [];
                            $isActiveUnit = false;
                            foreach($lessons as $l) {
                                if($l->id == $data->id) $isActiveUnit = true;
                            }
                        @endphp
                        <div class="accordion-item {{ $index == 0 ? 'rounded-top' : '' }}">
                            <h2 class="accordion-header" id="heading{{ $unit->id }}">
                                <button class="accordion-button {{ $isActiveUnit ? '' : 'collapsed' }} bg-white fw-bold py-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $unit->id }}" aria-expanded="{{ $isActiveUnit ? 'true' : 'false' }}">
                                    {{ $unit->name }}
                                </button>
                            </h2>
                            <div id="collapse{{ $unit->id }}" class="accordion-collapse collapse {{ $isActiveUnit ? 'show' : '' }}" data-bs-parent="#courseAccordion">
                                <div class="accordion-body p-0">
                                    <div class="list-group list-group-flush">
                                        @foreach($lessons as $lesson)
                                        <a href="/lesson/{{ $lesson->id }}" class="list-group-item list-group-item-action py-2 px-4 {{ $lesson->id == $data->id ? 'active bg-primary text-white' : '' }}">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-play{{ $lesson->id == $data->id ? '' : '-circle' }} me-2 {{ $lesson->id == $data->id ? 'text-white' : 'text-primary' }}"></i>
                                                <span class="small">{{ $lesson->name }}</span>
                                            </div>
                                        </a>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    /**
     * Converts various YouTube URL formats to an embed URL
     */
    function formatVideoUrl(url) {
        if (!url) return '';
        let videoId = '';
        if (url.includes('youtube.com/watch?v=')) {
            videoId = url.split('v=')[1].split('&')[0];
        } else if (url.includes('youtu.be/')) {
            videoId = url.split('youtu.be/')[1].split('?')[0];
        } else if (url.includes('youtube.com/embed/')) {
            return url;
        }
        
        if (videoId) {
            return `https://www.youtube.com/embed/${videoId}?rel=0&modestbranding=1`;
        }
        
        // Handle Google Drive
        if (url.includes('drive.google.com')) {
            return url.replace('/view', '/preview');
        }
        
        return url;
    }

    /**
     * Changes the iframe source and updates button states
     */
    function changeVideo(btn, autoPlay = true) {
        const url = btn.getAttribute('data-url');
        const iframe = document.getElementById('mainVideoPlayer');
        if (!iframe) return;

        // Update active class on buttons
        document.querySelectorAll('.video-part-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        // Set iframe source
        let finalUrl = formatVideoUrl(url);
        if (autoPlay && !finalUrl.includes('autoplay=1')) {
            finalUrl += (finalUrl.includes('?') ? '&' : '?') + 'autoplay=1';
        }
        iframe.src = finalUrl;
        
        // Scroll slightly to player if it's a manual click
        if (autoPlay) {
            iframe.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    // Initialize the first video on page load
    window.addEventListener('DOMContentLoaded', () => {
        const firstBtn = document.querySelector('.video-part-btn');
        if (firstBtn) {
            changeVideo(firstBtn, false);
        }
    });
</script>
@endsection