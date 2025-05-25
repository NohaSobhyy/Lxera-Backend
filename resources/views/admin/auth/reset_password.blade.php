@extends('admin.auth.auth_layout')

@section('content')
    @php
        $siteGeneralSettings = getGeneralSettings();
    @endphp
    <style>
        .content {
            overflow: auto;
            display: flex;
            min-height: 91vh;
            justify-content: center;
            align-content: center;
            align-items: center;
            flex-direction: column;
            flex-wrap: wrap;
        }
        .border-radius-lg {
            border-radius: 4px !important;
        }
        .text-secondary {
            color: #31007f !important;
        }
    </style>
    <div class="p-4">
    <div class="col-7 col-md-7 p-0 mb-5 mt-3 mt-md-auto">
        <img src="{{ $siteGeneralSettings['logo'] ?? '' }}" alt="logo" width="100%" class="">
</div>
        <h4 class="text-dark font-weight-normal">{{ trans('auth.reset_password') }}</h4>

        <form method="POST" action="{{ getAdminPanelUrl() }}/reset-password">
            {{ csrf_field() }}

            <input hidden name="token" placeholder="token" value="{{ $token }}">

            <div class="form-group">
                <label class="input-label" for="email">{{ trans('auth.email') }}:</label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" id="email"
                       value="{{ request()->get('email') }}" aria-describedby="emailHelp">
                @error('email')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>
            <div class="form-group">
                <label class="input-label" for="password">{{ trans('auth.password') }}:</label>
                <input name="password" type="password"
                       class="form-control @error('password') is-invalid @enderror" id="password"
                       aria-describedby="passwordHelp">
                @error('password')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>

            <div class="form-group">
                <label class="input-label" for="confirm_password">{{ trans('auth.retype_password') }}:</label>
                <input name="password_confirmation" type="password"
                       class="form-control @error('password_confirmation') is-invalid @enderror" id="confirm_password"
                       aria-describedby="confirmPasswordHelp">
                @error('password_confirmation')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary btn-block mt-20">{{ trans('auth.reset_password') }}</button>
        </form>
    </div>
@endsection
