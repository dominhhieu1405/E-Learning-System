@extends('user.layout.layout')

@section('title', __('terms.title'))

@section('css')
<link rel="stylesheet" href="/assets/css/pages/terms.css">
@endsection

@section('content')
<div class="terms-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="terms-card">
                    <h1 class="text-center mb-4 fw-bold">{{ __('terms.heading') }}</h1>
                    <div class="terms-content">
                        <p class="lead">{{ __('terms.welcome') }}</p>
                        
                        <h2>{{ __('terms.sections.s1_title') }}</h2>
                        <p>{{ __('terms.sections.s1_content') }}</p>

                        <h2>{{ __('terms.sections.s2_title') }}</h2>
                        <p>{{ __('terms.sections.s2_content') }}</p>

                        <h2>{{ __('terms.sections.s3_title') }}</h2>
                        <ul>
                            <li>{{ __('terms.sections.s3_list.l1') }}</li>
                            <li>{{ __('terms.sections.s3_list.l2') }}</li>
                            <li>{{ __('terms.sections.s3_list.l3') }}</li>
                        </ul>

                        <h2>{{ __('terms.sections.s4_title') }}</h2>
                        <p>{{ __('terms.sections.s4_content') }}</p>

                        <h2>{{ __('terms.sections.s5_title') }}</h2>
                        <p>{{ __('terms.sections.s5_content') }}</p>

                        <h2>{{ __('terms.sections.s6_title') }}</h2>
                        <p>{{ __('terms.sections.s6_content') }}</p>

                        <div class="mt-5 text-center text-muted small">
                            {{ __('terms.last_updated') }}{{ date('d/m/Y') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
