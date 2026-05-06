@extends('user.layout.layout')
@section('title', $data->name)
@section('content')

<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/" class="text-decoration-none">{{ __('common.home') }}</a></li>
            <li class="breadcrumb-item"><a href="/documents" class="text-decoration-none">{{ __('common.documents') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $data->name }}</li>
        </ol>
    </nav>
    <div class="row g-4">
        <div class="col-lg-8 mx-auto">
            <div class="ot-card p-4">
                <h2 class="fw-bold mb-3">{{ $data->name }}</h2>
                <div class="mb-4 text-muted">
                    <span class="me-3"><i class="fas fa-eye me-1"></i> {{ number_format($data->views ?? 0) }} {{ __('documents.views') }}</span>
                </div>
                
                @if($data->image)
                <img src="{{ $data->image }}" alt="{{ $data->name }}" class="img-fluid rounded mb-4" style="width: 100%; max-height: 400px; object-fit: cover;">
                @endif
                
                @if(!empty($data->file))
                <div class="bg-light p-3 rounded mb-4 d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-file-pdf text-danger fs-3 me-2"></i>
                        <span class="fw-semibold">{{ __('documents.attachment') }}</span>
                    </div>
                    <a href="{{ $data->file }}" target="_blank" class="btn btn-ot-primary btn-sm">
                        <i class="fas fa-download me-1"></i> {{ __('documents.download') }}
                    </a>
                </div>
                @endif
                
                <h5 class="fw-bold border-bottom pb-2 mb-3">{{ __('documents.content_title') }}</h5>
                <div class="document-content content-body">
                    @include('user.components.content-blocks', ['content' => $data->content ?? ''])
                </div>
                
                <div class="mt-4 pt-3 border-top">
                    <a href="/documents" class="btn btn-ot-outline"><i class="fas fa-arrow-left"></i> {{ __('documents.back_to_list') }}</a>
                </div>
            </div>

            <div class="mt-4">
                @include('user.components.comment-box', ['type' => 'document', 'target_id' => $data->id])
            </div>
        </div>
    </div>
</div>
@endsection