@extends(getTemplate() . '.auth.auth_layout')

@section('content')
    @php
        $registerMethod = getGeneralSettings('register_method') ?? 'mobile';
        $siteGeneralSettings = getGeneralSettings();
    @endphp

    <style>
        .content {
            overflow: auto;
            display: flex;
            min-height: 100vh;
            justify-content: center;
            align-content: center;
            align-items: center;
            flex-direction: column;
            flex-wrap: wrap;
        }
        .border-radius-lg {
            border-radius:8px !important;
        }
        .border-none {
        border: none;
    }
    .cs-btn{
        background-color:#000 !important;
        color:#fff !important;
        /* border: 1px solid #000 !important; */
        transition: all 0.3s ease;
    }
    .cs-btn:hover{
        background-color:#000 !important;
        /* border: 1px solid #000 !important; */
        /* color:#000 !important; */
        box-shadow: 0 3px 6px 0 #d9c1ff;
    }
        a, .registertext a {
        color:#31007f !important;
    }
    @media (max-width: 768px) {
        .content {
            min-height: 90vh;
        }
    }
    </style>

<div class="p-3 p-lg-5 m-md-3 border-radius-lg shadow border bg-white col-sm-7 col-md-8 col-lg-3">
        <div class="col-7 col-md-7 p-0 mb-4 mt-3 mt-md-auto mx-auto d-flex flex-column align-items-center">
            <img src="{{ asset('store/1/Anasblacklogo.webp') }}" alt="logo" width="90%" class="">
        </div>
        <div class="col-12">
            <div class="login-card">
                <h1 class="font-20 font-weight-bold text-center">
                    <!-- <svg width="34" height="29" viewBox="0 0 34 29"   fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M22 27C22 23.3181 17.5228 20.3333 12 20.3333C6.47715 20.3333 2 23.3181 2 27M32 12L25.3333 18.6667L22 15.3333M12 15.3333C8.3181 15.3333 5.33333 12.3486 5.33333 8.66667C5.33333 4.98477 8.3181 2 12 2C15.6819 2 18.6667 4.98477 18.6667 8.66667C18.6667 12.3486 15.6819 15.3333 12 15.3333Z"
                        stroke="#5E0A83" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                </svg> -->
                {{ trans('auth.forget_password') }}
            </h1>
                <form method="post" action="/forget-password" class="mt-35">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">

                    @if ($registerMethod == 'mobile')
                        <div class="d-flex align-items-center wizard-custom-radio mb-20">
                            <div class="wizard-custom-radio-item flex-grow-1">
                                <input type="radio" name="type" value="email" id="emailType" class=""
                                    {{ (empty(old('type')) or old('type') == 'email') ? 'checked' : '' }}>
                                <label class="font-12 cursor-pointer px-15 py-10"
                                    for="emailType">{{ trans('public.email') }}</label>
                            </div>

                            <div class="wizard-custom-radio-item flex-grow-1">
                                <input type="radio" name="type" value="mobile" id="mobileType" class=""
                                    {{ old('type') == 'mobile' ? 'checked' : '' }}>
                                <label class="font-12 cursor-pointer px-15 py-10"
                                    for="mobileType">{{ trans('public.mobile') }}</label>
                            </div>
                        </div>
                    @endif

                    <div class="js-email-fields form-group {{ old('type') == 'mobile' ? 'd-none' : '' }}">
                        <label class="input-label" for="email">{{ trans('public.email') }}:</label>
                        <input name="email" type="email" class="form-control @error('email') is-invalid @enderror"
                            id="email" value="{{ old('email') }}" aria-describedby="emailHelp">
                        @error('email')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    @if ($registerMethod == 'mobile')
                        <div class="js-mobile-fields {{ old('type') == 'mobile' ? '' : 'd-none' }}">
                            @include('web.default.auth.register_includes.mobile_field')
                        </div>
                    @endif

                    @if (!empty(getGeneralSecuritySettings('captcha_for_forgot_pass')))
                        @include('web.default.includes.captcha_input')
                    @endif


                    <button type="submit"
                    class="btn cs-btn btn-block mt-20">{{ trans('auth.reset_password') }}</button>
                </form>

                <div class="text-center mt-20">
                    <span
                        class="badge badge-circle-gray300 text-dark d-inline-flex align-items-center justify-content-center ">or</span>
                </div>

                <div class="text-center mt-20">
                    <span class="registertext">
                        <a href="/login" class="font-weight-bold">{{ trans('auth.login') }}</a>
                    </span>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('scripts_bottom')
    <script src="/assets/default/js/parts/forgot_password.min.js"></script>
@endpush
