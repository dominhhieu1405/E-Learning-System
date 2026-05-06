@extends('user.layout.layout')

@section('title', __('faqs.title'))

@section('css')
<link rel="stylesheet" href="/assets/css/pages/faqs.css">
@endsection

@section('content')
<div class="faq-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="faq-card">
                    <h1 class="text-center mb-2 fw-bold">{{ __('faqs.heading') }}</h1>
                    <p class="text-center text-muted mb-5">{{ __('faqs.subtitle') }}</p>

                    <div class="accordion" id="faqAccordion">
                        {{-- 1 --}}
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    {{ __('faqs.q1') }}
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    {{ __('faqs.a1') }}
                                </div>
                            </div>
                        </div>

                        {{-- 2 --}}
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    {{ __('faqs.q2') }}
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    {!! __('faqs.a2') !!}
                                </div>
                            </div>
                        </div>

                        {{-- 3 --}}
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    {{ __('faqs.q3') }}
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    {{ __('faqs.a3') }}
                                </div>
                            </div>
                        </div>

                        {{-- 4 --}}
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                    {{ __('faqs.q4') }}
                                </button>
                            </h2>
                            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    {{ __('faqs.a4') }}
                                </div>
                            </div>
                        </div>

                        {{-- 5 --}}
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                    {{ __('faqs.q5') }}
                                </button>
                            </h2>
                            <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    {{ __('faqs.a5') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
