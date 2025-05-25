@extends('admin.auth.auth_layout')

@section('content')
    @php
        $siteGeneralSettings = getGeneralSettings();
    @endphp
    <style>
        .cs-btn{
        background-color:#000 !important;
        color:#fff !important;
        /* border: 1px solid #000 !important; */
        transition: all 0.3s ease;
    }
    .cs-btn:hover{
        /* background-color:#fff !important; */
        /* border: 1px solid #000 !important; */
        /* color:#000 !important; */
        box-shadow: 0 3px 6px 0 #d9c1ff;
    }
        .text-secondary {
            color: #31007f !important;
        }
        @media (max-width: 768px) {
        .content {
            min-height: 90vh;
        }
    }
    </style>

    <div class="p-4">
    <div class="col-7 col-md-7 p-0 mb-5 mt-3 mt-md-auto mx-auto d-flex flex-column align-items-center">
        <img src="{{ asset('store/1/Anasblacklogo.webp') }}" alt="logo" width="100%" class="">
    </div>
        <h4 class="text-dark font-weight-normal">{{ trans('auth.forget_password') }}</h4>

        <p class="text-muted">{{ trans('update.we_will_send_a_link_to_reset_your_password') }}</p>

        <form method="POST" action="{{ getAdminPanelUrl() }}/forget-password">
            {{ csrf_field() }}

            <div class="form-group">
                <label for="email">{{ trans('auth.email') }}</label>
                <input id="email" type="email" value="{{ old('email') }}" class="form-control  @error('email')  is-invalid @enderror"
                       name="email" tabindex="1"
                       required autofocus>
                @error('email')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>

            @if(!empty(getGeneralSecuritySettings('captcha_for_admin_forgot_pass')))
                @include('admin.includes.captcha_input')
            @endif

            <button type="submit" class="btn cs-btn btn-block mt-20">{{ trans('auth.reset_password') }}</button>
        </form>

        <div class="text-center mt-3">
        <span class=" d-inline-flex align-items-center justify-content-center text-dark">or</span>
        </div>

        <div class="text-center mt-20">
            <span class="text-secondary">
                <a href="{{ getAdminPanelUrl() }}/login" class="font-weight-bold text-secondary">{{ trans('auth.login') }}</a>
            </span>
        </div>
    </div>
@endsection
