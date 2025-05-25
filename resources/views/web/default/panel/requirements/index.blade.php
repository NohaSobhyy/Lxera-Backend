@extends(getTemplate() . '.panel.layouts.panel_layout')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/select2/select2.min.css">
    <link rel="stylesheet" href="/assets/default/vendors/daterangepicker/daterangepicker.min.css">
@endpush

<style>
    .installment-card {
        background-color: #FBFBFB !important;
    }

    .discount {
        min-height: 17px;
    }
</style>

@section('content')

    @include('web.default.panel.requirements.requirements_includes.progress')

    <section class="row mt-80 mx-0 justify-content-center">
        @if (count($studentBundles) > 0)
            @php
                $count = 0;
            @endphp
            @foreach ($studentBundles as $studentBundle)
                @php
                    $count++;
                @endphp
                <section
                    class="bg-white position-relative col-xl-9 col-12 justify-content-center align-items-center rounded-sm mb-80 py-35 px-0">
                    <h2 class="mb-25 col-12">
                        {{ clean($studentBundle->bundle->title, 't') }}</h2>
                    @if (empty($studentBundle->studentRequirement))
                     @if ($studentBundle->status == 'pending')
                        <div class="w-100 text-center">
                            <p class="alert alert-info text-center mx-30">
                                {{trans('panel.seat_reservation_under_review')}}
                            </p>
                        </div>
                     @elseif ($studentBundle->status == 'fee_rejected')
                        <div class="w-100 text-center">
                            <p class="alert alert-danger text-center text-white mx-30">
                                {{trans('panel.seat_reservation_rejected')}}
                            </p>
                            <a href="/panel/financial/offline-payments"
                                class="btn btn-success p-5 mt-20 bg-secondary">
                                {{trans('panel.go_to_follow_request')}}
                            </a>
                        </div>
                        {{-- @elseif ($studentBundle->bundle->early_enroll)
                            <div class="w-100 text-center">
                                <p class="alert alert-info text-center mx-30">
                                    يرجى ملاحظة أن التسجيل الرسمي سيبدأ يوم 30 يوليو.
                                    <br> بمجرد فتح التسجيل، ستتمكن من استكمال رفع المتطلبات اللازمة وإتمام إجراءات التسجيل.
                                </p>
                            </div> --}}
                        @else
                            <div class="w-100 text-center">
                                <p class="alert alert-info text-center mx-30">
                                    {{trans('panel.admission_requirements_not_uploaded')}}
                                </p>
                                <a href="/panel/bundles/{{ $studentBundle->id }}/requirements"
                                    class="btn btn-success p-5 mt-20 bg-secondary">
                                    {{trans('panel.go_to_upload_admission_files')}}
                                </a>
                            </div>
                        @endif
                    @else
                        @if ($studentBundle->studentRequirement->status == 'pending')
                            <div class="w-100 text-center">
                                <p class="alert alert-info text-center mx-30">
                                    {{trans('panel.admission_requirements_uploaded_wait_review')}}
                                </p>
                            </div>
                        @elseif ($studentBundle->studentRequirement->status == 'approved')
                        <div class="w-100 text-center">
                            <p class="alert alert-success text-center mx-30">
                                {{trans('panel.admission_requirements_uploaded_approved')}}
                            </p>
                        </div>
                        @elseif ($studentBundle->studentRequirement->status == 'rejected')
                            <div class="w-100 text-center">
                                <p class="alert alert-danger text-center text-white mx-30">
                                    {{trans('panel.admission_files_rejected_check_email')}}
                                </p>
                                <a href="/panel/bundles/{{ $studentBundle->id }}/requirements"
                                    class="btn btn-primary p-5 mt-20">
                                    {{trans('panel.go_to_reupload_files')}}
                                </a>
                            </div>
                        @endif
                    @endif
                </section>
            @endforeach
        @else
            <section class="w-100 text-center">
                <p class="alert alert-info text-center mx-30">
                    {{trans('panel.no_diploma_registered')}}
                </p>
                <a href="{{ auth()->user()->student ? '/panel/newEnrollment' : '/apply' }}"
                    class="btn bg-secondary text-white p-5 mt-20">{{trans('panel.register_here')}}</a>
            </section>
        @endif

    </section>

@endsection

@push('scripts_bottom')
    <script src="/assets/vendors/cropit/jquery.cropit.js"></script>
    <script src="/assets/default/js/parts/img_cropit.min.js"></script>
    <script src="/assets/default/vendors/select2/select2.min.js"></script>

    <script>
        var editEducationLang = '{{ trans('site.edit_education') }}';
        var editExperienceLang = '{{ trans('site.edit_experience') }}';
        var saveSuccessLang = '{{ trans('webinars.success_store') }}';
        var saveErrorLang = '{{ trans('site.store_error_try_again') }}';
        var notAccessToLang = '{{ trans('public.not_access_to_this_content') }}';
    </script>

    <script src="/assets/default/js/panel/user_setting.min.js"></script>
@endpush
