@extends(getTemplate() . '.panel.layouts.panel_layout')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/daterangepicker/daterangepicker.min.css">
    <link rel="stylesheet" href="/assets/default/vendors/swiper/swiper-bundle.min.css">
    <link rel="stylesheet" href="/assets/default/vendors/owl-carousel2/owl.carousel.min.css">
    <style>
        .container_form {
            margin-top: 20px;
            /* border: 1px solid #ddd; */
            /* Add border to the container */
            padding: 20px;
            /* Optional: Add padding for spacing */
            border-radius: 10px !important;
            box-shadow: 2px 5px 10px #ddd;
            margin: 60px auto;
        }

        .hidden-element {
            display: none;
        }

        .application {
            display: flex;
            flex-direction: column;
            align-content: stretch;
            justify-content: flex-start;
            align-items: center;
            flex-wrap: wrap;
        }

        .section1 .form-title {
            text-align: center !important;
            padding: 10px;
            color: #5F2B80;
        }

        a {
            color: #ED1088;
        }

        #formSubmit {
            background: #5F2B80 !important;
        }


        .form-main-title {
            font-family: 'Inter';
            font-style: normal;
            font-weight: 400;
            font-size: 32px;
            line-height: 39px;
            color: #5E0A83;
        }

        .form-title {


            font-family: 'IBM Plex Sans';
            font-style: normal;
            font-weight: 700;
            font-size: 32px;
            line-height: 42px;
            color: #000000;

        }

        input {
            text-align: right;
        }

        .main-section {
            background-color: #F6F7F8;
        }

        .main-container {
            border-width: 2px !important;
        }

        .secondary_education,
        .high_education,
        #education {
            display: none;
        }

        .hero {
            width: 100%;
            height: 80vh;
            /* background-color: #ED1088; */
            background-image: linear-gradient(90deg, #5E0A83 19%, #F70387 100%);
        }

        @media(max-width:768px) {
            .hero {
                height: 50vh;
            }

            footer img {
                width: 150px !important;
            }

            .img-cover {
                width: 100% !important;
            }
        }

        @media(max-width:576px) {
            .form-main-title {
                font-size: 25px;
            }


        }
    </style>
@endpush

@section('content')
    <div class="application container-fluid">
        <div class="col-12 col-lg-10 col-md-11 px-0">
            <div class="col-lg-12 col-md-12 px-0">
                <Section class="section1 main-section">
                    <h2 class="section-title">{{ trans('panel.admission_request') }}</h2>
                    <div class="container_form">
                        <form action="/apply" method="POST" id="myForm">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ $user->id }}">

                            {{-- application type ( main categories) --}}
                            <div class="form-group col-12 col-sm-6">
                                <label class="form-label">{{ trans('panel.program_type_selection') }}<span
                                        class="text-danger">*</span></label>
                                <select id="typeSelect" name="main_category_id" required
                                    class="form-control @error('main_category_id') is-invalid @enderror"
                                    onchange="handleApplicationForm()">
                                    <option selected hidden value="">
                                        {{ trans('panel.application_type_selection') }}
                                    </option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}"
                                            @if (old('main_category_id') == $category->id) selected @endif>{{ $category->title }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('main_category_id')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            {{-- sub categories --}}

                            <div class="form-group col-12 col-sm-6 d-none">
                                <label class="form-label">{{ trans('panel.academic_program_type_selection') }} <span
                                        class="text-danger">*</span></label>
                                <select id="subCategiresSelect" name="sub_category_id"
                                    class="form-control @error('sub_category_id') is-invalid @enderror"
                                    onchange="handleSubCategoryChange(event)">

                                    <option selected hidden value="">
                                        {{ trans('panel.specialization_selection') }}
                                    </option>

                                </select>

                                @error('sub_category_id')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>


                            {{-- programs --}}

                            <div class="form-group col-12 col-sm-6 d-none">
                                <label class="form-label">{{ trans('panel.study_program_selection') }}<span
                                        class="text-danger">*</span></label>
                                <select id="bundleSelect" name="bundle_id"
                                    class="form-control @error('bundle_id') is-invalid @enderror"
                                    onchange="handleBundleChange(event);CertificateSectionToggle(event)">
                                    <option selected hidden value="">
                                        ا{{ trans('panel.study_program_selection') }}
                                    </option>

                                </select>

                                @error('bundle_id')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            {{-- addition_section --}}
                            <div class="form-group col-12 d-none" id="addition_section">
                                <label>
                                    {{ trans('panel.double_major_option') }}
                                    <span class="text-danger">*</span></label>

                                <div class="row mr-5 mt-5">

                                    <div class="col-sm-4 col">
                                        <label for="">
                                            <input type="radio" id="want_addition" name="want_addition" value="1"
                                                onchange="bundleAddtionSelectToggle()"
                                                class=" @error('want_addition') is-invalid @enderror"
                                                {{ old('want_addition') === '1' ? 'checked' : '' }}>
                                            نعم
                                        </label>
                                    </div>


                                    <div class="col">
                                        <label for="">
                                            <input type="radio" id="doesn't_want_addition" name="want_addition"
                                                onchange="bundleAddtionSelectToggle()" value="0"
                                                class="@error('want_addition') is-invalid @enderror"
                                                {{ old('want_addition') === '0' ? 'checked' : '' }}>
                                            لا
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group col-sm-6 d-none">
                                <label class="form-label">
                                    {{ trans('panel.double_major_specification') }}
                                    <span class="text-danger">*</span></label>
                                <select id="additionBundleSelect" name="addition_bundle_id"
                                    class="form-control @error('addition_bundle_id') is-invalid @enderror" onchange="">
                                </select>

                                @error('addition_bundle_id')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            {{-- courses --}}
                            <div class="form-group col-12 col-sm-6 d-none">
                                <label class="form-label">{{ trans('panel.course_selection') }}<span
                                        class="text-danger">*</span></label>
                                <select id="webinarSelect" name="webinar_id"
                                    class="form-control @error('webinar_id') is-invalid @enderror"
                                    onchange="coursesToggle(event)">
                                    <option selected hidden value="">
                                        {{ trans('panel.course_in_academy_selection') }}
                                    </option>

                                </select>

                                @error('webinar_id')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            {{-- course endorsement --}}
                            <div class="col-12 d-none">
                                <input type="checkbox" id="course_endorsement" name="course_endorsement">
                                {{ trans('panel.experience_acknowledgment') }}
                                @error('course_endorsement')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                @enderror

                                <div class="mt-3">
                                    <input type="checkbox" id="course_endorsement2">
                                    {{ trans('panel.time_limit_acknowledgment') }}
                                </div>
                                <div class="mt-3">
                                    <input type="checkbox" id="course_endorsement3">
                                    {{ trans('panel.test_schedule') }}
                                </div>
                            </div>


                            <div class="d-none font-14 font-weight-bold mb-10 col-12" id="early_enroll"
                                style="color: #5F2B80;">
                                {{ trans('panel.registration_open_notice') }}
                            </div>

                            {{-- certificate --}}
                            <div class="form-group col-12  d-none" id="certificate_section">
                                <label>{{ trans('application_form.want_certificate') }} ؟ <span
                                        class="text-danger">*</span></label>
                                <span class="text-danger font-12 font-weight-bold" id="certificate_message"> </span>
                                @error('certificate')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                @enderror
                                <div class="row mr-5 mt-5">
                                    {{-- want certificate --}}
                                    <div class="col-sm-4 col">
                                        <label for="want_certificate">
                                            <input type="radio" id="want_certificate" name="certificate"
                                                value="1" onchange="showCertificateMessage()"
                                                class=" @error('certificate') is-invalid @enderror"
                                                {{ old('certificate', $student->certificate ?? null) === '1' ? 'checked' : '' }}>
                                            {{ trans('panel.payment_later_confirmation') }}
                                        </label>
                                    </div>

                                    {{-- does not want certificate --}}
                                    <div class="col">
                                        <label for="doesn't_want_certificate">
                                            <input type="radio" id="doesn't_want_certificate" name="certificate"
                                                onchange="showCertificateMessage()" value="0"
                                                class="@error('certificate') is-invalid @enderror"
                                                {{ old('certificate', $student->certificate ?? null) === '0' ? 'checked' : '' }}>
                                            لا
                                        </label>
                                    </div>
                                </div>
                            </div>

                            {{-- requirement_endorsement --}}
                            <div class="col-12 d-none">
                                <input type="checkbox" id="requirement_endorsement" name="requirement_endorsement">
                                أ
                                {{ trans('panel.admission_requirements_acknowledgment') }}
                                <a href="https://anasacademy.uk/admission/" target="_blank">
                                    {{ trans('panel.registration_requirements') }}
                                </a> {{ trans('panel.commitment_confirmation') }}

                                @error('requirement_endorsement')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            {{-- register_endorsement --}}
                            {{--
                                <div class="col-12 d-none mt-3">
                                    <input type="checkbox" id="register_endorsement"
                                    name="register_endorsement">

                                        أقر بأنني سألتزم بتسديد قيمة البرنامج المسجل به، في حال عدم التسديد فإن أكاديمية أنس
                                        للفنون البصرية تحتفظ بالحق في اتخاذ الإجراءات المناسبة التي قد تشمل إلغاء التسجيل أو فرض
                                        رسوم تأخير إضافية.

                                        @error('register_endorsement')
                                            <div class="invalid-feedback d-block">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                </div>

                            --}}

                            <div class="mt-30">
                                <input type="hidden" id="direct_register" name="direct_register" value="">
                                <button type="button" id="form_button" class="btn btn-primary d-none">{{trans('panel.registration')}} </button>

                                <button type="submit" class="btn btn-secondary mr-3" id="formSubmit">
                                    {{trans('panel.registration')}}
                                </button>
                            </div>
                        </form>
                    </div>

                </Section>
            </div>
        </div>
    </div>
@endsection

@push('scripts_bottom')
    <script src="/assets/default/vendors/daterangepicker/daterangepicker.min.js"></script>

    <script>
        var undefinedActiveSessionLang = '{{ trans('webinars.undefined_active_session') }}';
        var saveSuccessLang = '{{ trans('webinars.success_store') }}';
        var selectChapterLang = '{{ trans('update.select_chapter') }}';
    </script>

    <script src="/assets/default/js/panel/make_next_session.min.js"></script>
    <script>
        const categories = @json($categories);

        // Pass all old values to a global JavaScript variable
        const oldValues = @json(session()->getOldInput());
    </script>
    <script src="/assets/default/js/applicationForm.js"></script>

    <script>
        window.onload = function() {
            handleApplicationForm();
        }
    </script>
@endpush
