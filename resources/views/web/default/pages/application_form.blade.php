@extends(getTemplate() . '.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/swiper/swiper-bundle.min.css">
    <link rel="stylesheet" href="/assets/default/vendors/owl-carousel2/owl.carousel.min.css">
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-WSVP27XBX1"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', 'G-WSVP27XBX1');
    </script>
    <style>
        .container_form {
            margin-top: 20px;
            /* border: 1px solid #ddd; */
            /* Add border to the container */
            padding: 20px;
            /* Optional: Add padding for spacing */
            border-radius: 16px !important;
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

        /* a {
                        color: #ED1088;
                    } */

        .form-main-title {
            font-family: "IBM Plex Sans Arabic" !important;
            font-style: normal;
            font-weight: 400;
            font-size: 22px;
            line-height: 39px;
            color: #5E0A83;
        }

        #formSubmit {
            background: #000000 !important;
        }

        #formSubmit:hover {
            background: #ffffff !important;
        }

        .form-title {
            font-family: "IBM Plex Sans Arabic" !important;
            font-style: normal;
            font-weight: 700;
            /* font-size: 36px; */
            line-height: 42px;
            color: #fff;
        }

        input {
            text-align: right;
        }

        .main-section {
            background-color: #F6F7F8;
            border-radius: 16px !important;
        }

        .main-container {
            border-width: 2px !important;
            border-radius: 16px !important;
        }

        .secondary_education,
        .high_education,
        #education {
            display: none;
        }

        .cs-btn {
            background-color: #000 !important;
            color: #fff !important;
            /* border: 1px solid #000 !important; */
            transition: all 0.3s ease;
        }

        .cs-btn:hover {
            background-color: #fff !important;
            border: 1px solid #000 !important;
            color: #000 !important;
            box-shadow: 0 3px 6px 0 #d9c1ff;
        }

        .hero {
            width: 100%;
            height: 50vh;
            /* background-color: #ED1088; */
            background-image: URL('https://support.anasacademy.uk/resources/views/images/StoriesBG.webp');
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
            display: flex;
            flex-direction: column;
            flex-wrap: nowrap;
            justify-content: center;
            align-items: stretch;
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
                font-size: 20px;
            }


        }
    </style>
@endpush

@php
    $siteGeneralSettings = getGeneralSettings();
@endphp
@php
    $registerMethod = getGeneralSettings('register_method') ?? 'mobile';
    $showOtherRegisterMethod = getFeaturesSettings('show_other_register_method') ?? false;
    $showCertificateAdditionalInRegister = getFeaturesSettings('show_certificate_additional_in_register') ?? false;
    $selectRolesDuringRegistration = getFeaturesSettings('select_the_role_during_registration') ?? null;
@endphp


@section('content')
    {{-- hero section --}}
    @include('web.default.includes.hero_section')



    <div class="application container">
        <div class="col-12 col-lg-10 col-md-11 px-0">
            <div class="col-lg-12 col-md-12 px-0">
                <Section class="section1 main-section">
                    <div class="container_form">
                        <!--Form Title-->

                        <p style="padding: 40px 0;font-size:18px;font-weight:600;line-height:1.5em">
                            {{ trans('application_form.form_heading_1') }}
                            <a href="https://anasacademy.uk/admission/"
                                style="color:#000000 !important; text-decoration:underline;" target="_blank">
                                {{ trans('application_form.click_here') }}
                            </a>
                        </p>

                        <form action="/apply" method="POST" id="myForm">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ $user->id ?? '' }}">

                            {{-- application type ( main categories) --}}
                            <div class="form-group col-12 col-sm-6">
                                <label class="form-label">{{ trans('application_form.program') }}<span
                                        class="text-danger">*</span></label>
                                <select id="typeSelect" name="main_category_id" required
                                    class="form-control @error('main_category_id') is-invalid @enderror"
                                    onchange="handleApplicationForm()">
                                    <option selected hidden value="">
                                        {{ trans('application_form.choose_type') }}

                                    </option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}"
                                            @if (old('main_category_id', !empty($main_category_id) ? $main_category_id : null) == $category->id) selected @endif>{{ $category->title }}
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
                                <label class="form-label"> {{ trans('application_form.program_type') }}<span
                                        class="text-danger">*</span></label>
                                <select id="subCategiresSelect" name="sub_category_id"
                                    class="form-control @error('sub_category_id') is-invalid @enderror"
                                    onchange="handleSubCategoryChange(event)">

                                    <option selected hidden value="">
                                        {{ trans('application_form.choose_major') }}
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
                                <label class="form-label">{{ trans('application_form.choose_program_type') }}<span
                                        class="text-danger">*</span></label>
                                <select id="bundleSelect" name="bundle_id"
                                    class="form-control @error('bundle_id') is-invalid @enderror"
                                    onchange="handleBundleChange(event);CertificateSectionToggle(event)">
                                    <option selected hidden value="">
                                        {{ trans('application_form.choose_program_type_2') }}</option>

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
                                    {{ trans('application_form.double_major') }}

                                    <span class="text-danger">*</span></label>

                                <div class="row mr-5 mt-5">

                                    <div class="col-sm-4 col">
                                        <label for="">
                                            <input type="radio" id="want_addition" name="want_addition" value="1"
                                                onchange="bundleAddtionSelectToggle()"
                                                class=" @error('want_addition') is-invalid @enderror"
                                                {{ old('want_addition', !empty($want_addition) ? $want_addition : null) === '1' ? 'checked' : '' }}>
                                            نعم
                                        </label>
                                    </div>


                                    <div class="col">
                                        <label for="">
                                            <input type="radio" id="doesn't_want_addition" name="want_addition"
                                                onchange="bundleAddtionSelectToggle()" value="0"
                                                class="@error('want_addition') is-invalid @enderror"
                                                {{ old('want_addition', !empty($want_addition) ? $want_addition : null) === '0' ? 'checked' : '' }}>
                                            {{ trans('application_form.no') }}
                                        </label>
                                    </div>
                                </div>
                            </div>

                            {{-- additions bundles --}}
                            <div class="form-group col-12 col-sm-6 d-none">
                                <label class="form-label">{{ trans('application_form.double_major_2') }}<span
                                        class="text-danger">*</span></label>
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
                                <label class="form-label">{{ trans('application_form.choose_course') }}<span
                                        class="text-danger">*</span></label>
                                <select id="webinarSelect" name="webinar_id"
                                    class="form-control @error('webinar_id') is-invalid @enderror"
                                    onchange="coursesToggle(event)">
                                    <option selected hidden value="">
                                        {{ trans('application_form.choose_course_2') }}
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
                                {{ trans('application_form.course_endorsement') }}
                                @error('course_endorsement')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                @enderror

                                <div class="mt-3">
                                    <input type="checkbox" id="course_endorsement2">
                                    {{ trans('application_form.course_endorsement_2') }}
                                </div>
                            </div>


                            <div class="d-none font-14 font-weight-bold mb-10 col-12" id="early_enroll"
                                style="color: #5F2B80;">
                                {{ trans('application_form.registeration_announcement') }}
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
                                            {{ trans('application_form.yes_pay_fee_later') }}
                                        </label>
                                    </div>

                                    {{-- does not want certificate --}}
                                    <div class="col">
                                        <label for="doesn't_want_certificate">
                                            <input type="radio" id="doesn't_want_certificate" name="certificate"
                                                onchange="showCertificateMessage()" value="0"
                                                class="@error('certificate') is-invalid @enderror"
                                                {{ old('certificate', $student->certificate ?? null) === '0' ? 'checked' : '' }}>
                                            {{ trans('application_form.no') }}
                                        </label>
                                    </div>
                                </div>
                            </div>

                            {{-- requirement_endorsement --}}
                            <div class="col-12 d-none pb-3">
                                <input type="checkbox" id="requirement_endorsement" name="requirement_endorsement">
                                {{ trans('application_form.read') }}
                                <a href="https://anasacademy.uk/admission/" target="_blank">
                                    {{ trans('application_form.registeration_requirment') }}
                                </a>
                                {{ trans('application_form.program_endorsement') }}
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



                            {{-- <h1 class=" mt-50 mb-25">بيانات المتدرب الأساسية</h1> --}}


                            {{-- personal details --}}
                            <section class="d-none">
                                <h2 class="form-main-title">{{ trans('application_form.personal_data') }}</h2>
                                <section
                                    class="main-container border border-2 border-secondary-subtle rounded p-3 mt-2 mb-25 row mx-0 ">
                                    {{-- arabic name --}}
                                    {{-- <div class="form-group col-12 col-sm-6">
                                        <label for="name">{{ trans('application_form.name') }}<span
                                                class="text-danger">*</span></label>
                                        <input type="text" id="name" name="ar_name"
                                            value="{{ old('ar_name', $student ? $student->ar_name : $user->full_name ?? '') }}"
                                            placeholder="ادخل الإسم باللغه العربية فقط" required
                                            class="form-control @error('ar_name') is-invalid @enderror">

                                        @error('ar_name')
                                            <div class="invalid-feedback d-block">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div> --}}

                                    {{-- english name --}}
                                    {{-- <div class="form-group col-12 col-sm-6">
                                        <label for="name_en">{{ trans('application_form.name_en') }}<span
                                                class="text-danger">*</span></label>
                                        <input type="text" id="name_en" name="en_name" 
                                            value="{{ old('en_name', $student ? $student->en_name : $user->en_name) }}"
                                            placeholder="ادخل الإسم باللغه الإنجليزيه فقط" required
                                            class="form-control @error('en_name') is-invalid @enderror">

                                        @error('en_name')
                                            <div class="invalid-feedback d-block">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div> --}}

                                    {{-- identifier number --}}
                                    {{-- <div class="form-group col-12 col-sm-6">
                                        <label for="identifier_num">رقم الهوية الوطنية أو جواز السفر <span
                                                class="text-danger">*</span></label>
                                        <input type="text" id="identifier_num" name="identifier_num"

                                            value="{{ old('identifier_num', $student ? $student->identifier_num : '') }}"
                                            placeholder="الرجاء إدخال الرقم كامًلا والمكون من 10 أرقام للهوية أو 6 أرقام للجواز"
                                            required class="form-control  @error('identifier_num') is-invalid @enderror">

                                        @error('identifier_num')
                                            <div class="invalid-feedback d-block">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div> --}}

                                    {{-- birthday --}}
                                    {{-- <div class="form-group col-12 col-sm-6">
                                        <label for="birthday">{{ trans('application_form.birthday') }}<span
                                                class="text-danger">*</span></label>
                                        <input type="date" id="birthday" name="birthdate" 
                                            value="{{ old('birthdate', $student ? $student->birthdate : '') }}" required
                                            class="form-control @error('birthdate') is-invalid @enderror"
                                            max="{{ \Carbon\Carbon::now()->subYear()->endOfYear()->toDateString() }}"
                                            >
                                        @error('birthdate')
                                            <div class="invalid-feedback d-block">
                                                {{ $message }}
                                            </div>
                                        @enderror

                                    </div> --}}


                                    {{-- nationality --}}
                                    {{-- <div class="form-group col-12 col-sm-6">
                                        <label for="nationality">{{ trans('application_form.nationality') }}<span
                                                class="text-danger">*</span></label>
                                        @php
                                            $nationalities = [
                                                'سعودي/ة',
                                                'اماراتي/ة',
                                                'اردني/ة',
                                                'بحريني/ة',
                                                'جزائري/ة',
                                                'عراقي/ة',
                                                'مغربي/ة',
                                                'يمني/ة',
                                                'سوداني/ة',
                                                'صومالي/ة',
                                                'كويتي/ة',
                                                'سوري/ة',
                                                'لبناني/ة',
                                                'مصري/ة',
                                                'تونسي/ة',
                                                'فلسطيني/ة',
                                                'جيبوتي/ة',
                                                'عماني/ة',
                                                'موريتاني/ة',
                                                'قطري/ة',
                                            ];
                                        @endphp
                                        <select id="nationality" name="nationality" required
                                            class="form-control  @error('nationality') is-invalid @enderror"
                                            onchange="toggleNationality()">
                                            <option value="" class="placeholder" disabled>
                                                اختر جنسيتك</option>
                                            @foreach ($nationalities as $nationality)
                                                <option value="{{ $nationality }}"
                                                    {{ old('nationality', $student->nationality ?? null) == $nationality ? 'selected' : '' }}>
                                                    {{ $nationality }}</option>
                                            @endforeach
                                            <option value="اخرى" id="anotherNationality"
                                                {{ old('nationality') != '' && !in_array(old('nationality'), $nationalities) ? 'selected' : '' }}>
                                                اخرى</option>
                                        </select>
                                        @error('nationality')
                                            <div class="invalid-feedback d-block">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div> --}}


                                    {{-- other nationality --}}
                                    {{-- <div class="form-group col-12 col-sm-6" id="other_nationality_section"
                                        style="display: none">
                                        <label for="nationality">ادخل الجنسية <span class="text-danger">*</span></label>
                                        <input type="text"
                                            class="form-control @error('other_nationality') is-invalid @enderror"
                                            id="other_nationality" name="other_nationality" placeholder="اكتب الجنسية"
                                           
                                            value="{{ old('other_nationality', $student ? $student->other_nationality : '') }}"
                                            onkeyup="setNationality()">

                                        @error('other_nationality')
                                            <div class="invalid-feedback d-block">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div> --}}

                                    {{-- country --}}
                                    {{-- <div class="form-group col-12 col-sm-6">
                                        <label for="country">{{ trans('application_form.country') }}<span
                                                class="text-danger">*</span></label>
                                        @php
                                            $countries = [
                                                'السعودية',
                                                'الامارات العربية المتحدة',
                                                'الاردن',
                                                'البحرين',
                                                'الجزائر',
                                                'العراق',
                                                'المغرب',
                                                'اليمن',
                                                'السودان',
                                                'الصومال',
                                                'الكويت',
                                                'جنوب السودان',
                                                'سوريا',
                                                'لبنان',
                                                'مصر',
                                                'تونس',
                                                'فلسطين',
                                                'جزرالقمر',
                                                'جيبوتي',
                                                'عمان',
                                                'موريتانيا',
                                            ];
                                        @endphp
                                        <select id="mySelect" name="country" required
                                            class="form-control @error('country') is-invalid @enderror"
                                            onchange="toggleHiddenInputs()">
                                            <option value="" class="placeholder" disabled="">اختر دولتك
                                            </option>
                                            @foreach ($countries as $country)
                                                <option value="{{ $country }}"
                                                    {{ old('country', $student->country ?? null) == $country ? 'selected' : '' }}>
                                                    {{ $country }}</option>
                                            @endforeach
                                            <option value="اخرى" id="anotherCountry"
                                                {{ old('country') != '' && !in_array(old('country'), $countries) ? 'selected' : '' }}>
                                                اخرى</option>

                                        </select>

                                        @error('country')
                                            <div class="invalid-feedback d-block">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div> --}}

                                    {{-- other country --}}
                                    {{-- <div class="form-group col-12 col-sm-6" id="anotherCountrySection"
                                        style="display: none">
                                        <label for="city" class="form-label">ادخل البلد<span
                                                class="text-danger">*</span></label>
                                        <input type="text" id="city" name="city"
                                            class="form-control  @error('city') is-invalid @enderror"
                                            placeholder="ادخل دولتك"
                                            value="{{ old('city', $student ? $student->city : '') }}"
                                            onkeyup="setCountry()">

                                        @error('city')
                                            <div class="invalid-feedback d-block">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div> --}}

                                    {{-- region --}}
                                    {{-- <div class="form-group col-12 col-sm-6" id="region" style="display: none">
                                        <label for="area" class="form-label">المنطقة<span
                                                class="text-danger">*</span></label>
                                        <input type="text" id="area" name="area"
                                            class="form-control  @error('area') is-invalid @enderror"
                                            placeholder="اكتب المنطقة"
                                            value="{{ old('area', $student ? $student->area : '') }}">

                                        @error('area')
                                            <div class="invalid-feedback d-block">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div> --}}

                                    {{-- city --}}
                                    {{-- <div class="form-group col-12 col-sm-6">
                                        <div id="cityContainer">
                                            <label for="town"
                                                id="cityLabel">{{ trans('application_form.city') }}<span
                                                    class="text-danger">*</span></label>
                                            <input type="text" id="town" name="town"
                                                placeholder="اكتب مدينه السكن الحاليه"
                                                value="{{ old('town', $student ? $student->town : '') }}" required
                                                class="form-control @error('town') is-invalid @enderror">
                                        </div>
                                        @error('town')
                                            <div class="invalid-feedback d-block">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div> --}}

                                    {{-- gender --}}
                                    {{-- <div class="form-group col-12 col-sm-6">
                                        <label for="gender">{{ trans('application_form.gender') }}<span
                                                class="text-danger">*</span></label>

                                        @error('gender')
                                            <div class="invalid-feedback d-inline">
                                                {{ $message }}
                                            </div>
                                        @enderror

                                        <div class="row mr-5 mt-5">
                                          
                                            <div class="col-sm-4 col">
                                                <label for="female">
                                                    <input type="radio" id="female" name="gender" value="female"
                                                        class=" @error('gender') is-invalid @enderror" required
                                                        {{ old('gender', $student->gender ?? null) == 'female' ? 'checked' : '' }}>
                                                    انثي
                                                </label>
                                            </div>

                                            <div class="col">
                                                <label for="male">
                                                    <input type="radio" id="male" name="gender" value="male"
                                                        class=" @error('gender') is-invalid @enderror" required
                                                        {{ old('gender', $student->gender ?? null) == 'male' ? 'checked' : '' }}>
                                                    ذكر
                                                </label>
                                            </div>
                                        </div>
                                    </div> --}}
                                </section>
                            </section>


                            {{-- about us --}}
                            <div class="form-group col-12">
                                <label>{{ trans('application_form.heard_about_us') }}<span
                                        class="text-danger">*</span></label>

                                @error('about_us')
                                    <div class="invalid-feedback d-inline">
                                        {{ $message }}
                                    </div>
                                @enderror


                                <br>

                                <label for="snapchat">
                                    <input type="radio" id="snapchat" name="about_us" required value="snapchat"
                                        class=" @error('about_us') is-invalid @enderror"
                                        {{ old('about_us', $student->about_us ?? null) == 'snapchat' ? 'checked' : '' }}>
                                    {{ trans('application_form.snapchat') }}
                                </label><br>
                                <label for="twitter">
                                    <input type="radio" id="twitter" name="about_us" required value="twitter"
                                        class=" @error('about_us') is-invalid @enderror"
                                        {{ old('about_us', $student->about_us ?? null) == 'twitter' ? 'checked' : '' }}>
                                    {{ trans('application_form.twitter') }}
                                </label><br>
                                <label for="friend">
                                    <input type="radio" id="friend" name="about_us" required value="friend"
                                        class="@error('about_us') is-invalid @enderror"
                                        {{ old('about_us', $student->about_us ?? null) == 'friend' ? 'checked' : '' }}>
                                    {{ trans('application_form.friend') }}
                                </label><br>

                                <div id="friendOptions"  style="margin-inline-start: 20px; display: none;">
                                    <label for="academy_family">
                                        <input type="radio" id="academy_family" name="about_us" value="academy_family"
                                            class="@error('about_us') is-invalid @enderror"
                                            {{ old('about_us', $student->about_us ?? null) == 'academy_family' ? 'checked' : '' }}>
                                       متدرب سابق
                                    </label><br>

                                    <label for="friend_other">
                                        <input type="radio" id="friend_other" name="about_us" value="friend"
                                            class="@error('about_us') is-invalid @enderror"
                                            {{ old('about_us', $student->about_us ?? null) == 'friend_other' ? 'checked' : '' }}>
                                       اخرى
                                    </label><br>
                                </div>

                                <label for="instagram">
                                    <input type="radio" id="instagram" name="about_us" required value="instagram"
                                        class=" @error('about_us') is-invalid @enderror"
                                        {{ old('about_us', $student->about_us ?? null) == 'instagram' ? 'checked' : '' }}>
                                    {{ trans('application_form.instagram') }}
                                </label><br>
                                <label for="facebook">
                                    <input type="radio" id="facebook" name="about_us" required value="facebook"
                                        class=" @error('about_us') is-invalid @enderror"
                                        {{ old('about_us', $student->about_us ?? null) == 'facebook' ? 'checked' : '' }}>
                                    {{ trans('application_form.facebook') }}
                                </label><br>


                                <label for="other">
                                    <input type="radio" id="other" name="about_us" required value="other"
                                        class=" @error('about_us') is-invalid @enderror"
                                        {{ old('about_us', $student->about_us ?? null) == 'other' ? 'checked' : '' }}>
                                    {{ trans('application_form.other') }}
                                </label><br>
                                <label id="academyFamilyLabel" style="display:none">ادخل كود المتدرب السابق<span
                                        class="text-danger">*</span></label>
                                <input type="text" id="academyFamilyStudentCode" name="friend_code"
                                    placeholder="ادخل كود المتدرب السابق"
                                    class="form-control @error('friend_code') is-invalid @enderror"
                                    style="display:none"><br>

                                {{-- <label id="academyFamilyEmailLabel"
                                    style="display:none">{{ trans('application_form.student_email') }} <span
                                        class="text-danger">*</span></label> --}}
                                {{-- <input type="email" id="academyFamilyStudentEmail" name="friend_email"
                                    placeholder="{{ trans('application_form.student_email') }}"
                                    class="form-control @error('friend_email') is-invalid @enderror"
                                    style="display:none"><br> --}}
                                <label id="otherLabel"style="display:none">{{ trans('application_form.source') }}<span
                                        class="text-danger">*</span></label>
                                <input type="text" id="otherInput" placeholder="" name="other_about_us"
                                    class="form-control @error('about_us') is-invalid @enderror"
                                    style="display:none"><br>


                                <label>
                                    <input type="checkbox" id="terms" name="terms" required
                                        class="@error('terms') is-invalid @enderror">

                                    {{ trans('application_form.agreement_contract') }}
                                    <a target="_blank"
                                        href="http://anasacademy.uk/wp-content/uploads/2025/05/عقد-اتفاقية-متدرب-NEW_.pdf">
                                        {{ trans('application_form.click_here_view') }}
                                    </a>

                                </label>

                                @error('terms')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <input type="hidden" id="direct_register" name="direct_register" value="">
                            <button type="button" id="form_button"
                                class="btn btn-primary d-none cs-btn">{{ trans('application_form.register') }} </button>

                            <button type="submit" class="btn cs-btn mr-3" id="formSubmit">
                                {{ trans('application_form.register') }}
                            </button>
                        </form>

                    </div>
                </Section>
            </div>


        </div>
    </div>
@endsection
@push('scripts_bottom')
    <script src="/assets/default/vendors/swiper/swiper-bundle.min.js"></script>
    <script src="/assets/default/vendors/owl-carousel2/owl.carousel.min.js"></script>
    <script src="/assets/default/vendors/parallax/parallax.min.js"></script>
    <script src="/assets/default/js/parts/home.min.js"></script>
    <script>
        const categories = @json($categories);

        // Pass all old values to a global JavaScript variable
        const oldValues = @json(session()->getOldInput());

        if (!(oldValues.main_category_id)) {
            oldValues.main_category_id = @json($main_category_id ?? null)
        }
        if (!(oldValues.sub_category_id)) {
            oldValues.sub_category_id = @json($sub_category_id ?? null)
        }
        if (!(oldValues.webinar_id)) {
            oldValues.webinar_id = @json($webinar_id ?? null)
        }
        if (!(oldValues.bundle_id)) {
            oldValues.bundle_id = @json($bundle_id ?? null)
        }
        if (!(oldValues.addition_bundle_id)) {
            oldValues.addition_bundle_id = @json($addition_bundle_id ?? null);
        }
    </script>
    @php
        $locale = app()->getLocale();

    @endphp
    <script>
        var locale = @json($locale);
    </script>
    <script src="/assets/default/js/applicationForm.js"></script>
    <script>
        window.onload = function() {
            handleApplicationForm();

            var otherLabel = document.getElementById("otherLabel");
            var otherInput = document.getElementById("otherInput");

            var academyFamilyLabel = document.getElementById("academyFamilyLabel");
            var academyFamilyStudentCode = document.getElementById("academyFamilyStudentCode");
            var academyFamilyEmailLabel = document.getElementById("academyFamilyEmailLabel");
            var academyFamilyStudentEmail = document.getElementById("academyFamilyStudentEmail");

            var friendOptions = document.getElementById("friendOptions");
            var radioButtons = document.querySelectorAll('input[name="about_us"]');

            function handleRadioChange() {
                const selected = document.querySelector('input[name="about_us"]:checked');
                const selectedId = selected?.id;

                // Show nested options if friend is selected
                if (["friend", "academy_family", "friend_other"].includes(selectedId)) {
                    friendOptions.style.display = "block";
                } else {
                    friendOptions.style.display = "none";
                }
                // Show/hide "other" input field (text input)
                if (selectedId === "other") {
                    otherLabel.style.display = "block";
                    otherInput.style.display = "block";
                    otherInput.setAttribute("required", "required");
                    selected.value = otherInput.value;
                } else {
                    otherLabel.style.display = "none";
                    otherInput.style.display = "none";
                    otherInput.removeAttribute("required");
                }

                // Show/hide academy family inputs
                if (selectedId === "academy_family") {
                    academyFamilyLabel.style.display = "block";
                    academyFamilyStudentCode.style.display = "block";
                    academyFamilyEmailLabel.style.display = "block";
                    academyFamilyStudentEmail.style.display = "block";
                    academyFamilyStudentCode.setAttribute("required", "required");
                    academyFamilyStudentEmail.setAttribute("required", "required");
                } else {
                    academyFamilyLabel.style.display = "none";
                    academyFamilyStudentCode.style.display = "none";
                    academyFamilyEmailLabel.style.display = "none";
                    academyFamilyStudentEmail.style.display = "none";
                    academyFamilyStudentCode.removeAttribute("required");
                    academyFamilyStudentEmail.removeAttribute("required");
                }
            }

            radioButtons.forEach(function(radio) {
                radio.addEventListener("change", handleRadioChange);
            });

            otherInput.addEventListener("change", function() {
                let radioButton = document.getElementById("other");
                radioButton.value = otherInput.value;
            });

            // Initial run (in case pre-filled)
            handleRadioChange();
        };
    </script>
@endpush
