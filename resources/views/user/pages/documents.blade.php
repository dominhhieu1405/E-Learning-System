@extends('user.layout.layout')
@section('title', __('documents.title'))
@section('content')
<div class="container py-4">
    <div class="section-header mb-4">
        <h2 class="mb-0">{{ __('documents.heading') }}</h2>
    </div>
    
    @if($data && count($data) > 0)
    <div class="row g-4">
        @foreach($data as $doc)
        <div class="col-12 col-md-6 col-lg-3">
            <div class="ot-card h-100">
                @if($doc->image)
                    <img src="{{ $doc->image }}" alt="{{ $doc->name }}" class="card-img-top" style="height: 180px; object-fit: cover;">
                @else
                    <div class="card-img-top d-flex align-items-center justify-content-center" style="height: 180px; background:linear-gradient(135deg,#f59e0b,#ef4444);color:#fff;">
                        <i class="fas fa-file-alt fa-3x" style="opacity:0.5;"></i>
                    </div>
                @endif
                <div class="card-body">
                    <h6 class="card-title fw-bold">{{ $doc->name }}</h6>
                    <p class="card-text small text-muted text-truncate">{{ $doc->description ?? '' }}</p>
                    <a href="/document/{{ $doc->id }}" class="stretched-link"></a>
                </div>
                <div class="card-footer bg-transparent border-0 pt-0 pb-3">
                    <div class="btn btn-sm btn-outline-primary w-100 rounded-pill fw-bold">
                        {{ __('documents.view_details') }} <i class="fas fa-arrow-right ms-1 fs-12"></i>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    
    @if(isset($paginate) && $paginate->total_page > 1)
    <div class="d-flex justify-content-center mt-5">
        <nav aria-label="Page navigation">
            <ul class="pagination pagination-sm">
                @for($i = 1; $i <= $paginate->total_page; $i++)
                <li class="page-item {{ $page == $i ? 'active' : '' }}">
                    <a class="page-link" href="/documents/{{ $i }}">{{ $i }}</a>
                </li>
                @endfor
            </ul>
        </nav>
    </div>
    @endif
    @else
    <div class="text-center py-5 text-muted">
        <i class="fas fa-box-open fa-3x mb-3 opacity-50"></i>
        <p>{{ __('documents.no_documents') }}</p>
    </div>
    @endif
</div>
@endsection