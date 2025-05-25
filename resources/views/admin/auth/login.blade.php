@extends('admin.auth.auth_layout')

@section('content')
    @php
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
    .border-radius-lg {
        border-radius: 8px !important;
    }
    .btn-border-radius {
        border-radius: 20px !important;
        font-size: 16px !important;
    }
    .input-size {
        padding:0.75rem 1rem 0.75rem 1rem;
        height:60px !important;
        font-size:16px !important;

    }
    .input-flex {
        display: flex;
        flex-direction: row;
        justify-content: flex-start;
        align-items: center;
        /* background-color: #e8f0fe; */
    }
    .border-none {
        border: none;

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
    <div class="col-7 col-md-7 p-0 mb-0 mb-lg-5 mt-3 mt-md-auto mx-auto d-flex flex-column align-items-center ">
        <img src="{{ asset('store/1/Anasblacklogo.webp') }}" alt="logo" width="100%" >
        </div>
        <h4 class="text-dark font-weight-normal">{{ trans('admin/main.welcome') }} <span class="font-weight-bold">{{ $siteGeneralSettings['site_name'] ?? '' }}</span></h4>

        <p class="text-muted">{{ trans('auth.admin_tagline') }}</p>

        <form method="POST" action="{{ getAdminPanelUrl() }}/login" class="needs-validation" novalidate="">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
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

            <div class="form-group">
                <div class="d-block">
                    <label for="password" class="control-label">{{ trans('auth.password') }}</label>
                </div>
                <input id="password" type="password" class="form-control  @error('password')  is-invalid @enderror"
                       name="password" tabindex="2" required>
                @error('password')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>

            @if(!empty(getGeneralSecuritySettings('captcha_for_admin_login')))
                @include('admin.includes.captcha_input')
            @endif

            <div class="form-group">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" name="remember" class="custom-control-input" tabindex="3"
                           id="remember-me">
                    <label class="custom-control-label"
                           for="remember-me">{{ trans('auth.remember_me') }}</label>
                </div>
            </div>

            <div class="form-group">
            <button type="submit" class="btn cs-btn btn-lg btn-block" tabindex="4">
            {{ trans('auth.login') }}
                </button>
            </div>
        </form>

        <a href="{{ getAdminPanelUrl() }}/forget-password" class="text-secondary">{{ trans('auth.forget_your_password') }}</a>
    </div>
@endsection
