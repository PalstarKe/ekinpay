@php
$customizerHidden = 'customizer-hide';
use App\Models\Utility;
$logo = Utility::get_file('uploads/logo');
$settings = Utility::settings();
$company_logo = $settings['company_logo'] ?? '';
@endphp

@extends('layouts/layoutMaster')

@push('custom-scripts')
    @if ($settings['recaptcha_module'] == 'on')
        {!! NoCaptcha::renderJs() !!}
    @endif
@endpush

@section('title', __('Login'))

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/@form-validation/form-validation.scss'
])
@endsection
@php
    $pageConfigs = $pageConfigs ?? ['myLayout' => 'blank'];
@endphp

@section('page-style')
@vite([
  'resources/assets/vendor/scss/pages/page-auth.scss'
])
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/@form-validation/popular.js',
  'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
  'resources/assets/vendor/libs/@form-validation/auto-focus.js'
])
@endsection

@section('page-script')
@vite([
  'resources/assets/js/pages-auth.js'
])
@endsection

@section('content')
<div class="container-xxl">
  <div class="authentication-wrapper authentication-basic container-p-y">
    <div class="authentication-inner py-6">
      <!-- Login Card -->
      <div class="card">
        <div class="card-body">
          <!-- Logo -->
          <div class="app-brand justify-content-center mb-6">
            <a href="{{ url('/') }}" class="app-brand-link">
              @if($company_logo)
                <img src="{{ asset('uploads/logo/'.$company_logo) }}" alt="Logo" height="20">
              @else
                <span class="app-brand-logo demo">
                  @include('_partials.macros',['height'=>20,'withbg' => "fill: #fff;"])
                </span>
              @endif
              <span class="app-brand-text demo text-heading fw-bold">{{ config('variables.templateName') }}</span>
            </a>
          </div>
          <!-- /Logo -->
          <h4 class="mb-1">{{ __('Welcome to') }} {{ config('variables.templateName') }}! 👋</h4>

          <!-- Display Validation Errors -->
          @if ($errors->any())
            <div class="alert alert-danger">
              <ul class="mb-0">
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          <!-- Login Form -->
          {{ Form::open(['route' => 'login', 'method' => 'post', 'id' => 'loginForm', 'class' => 'needs-validation', 'novalidate']) }}
            <div class="mb-6">
              <label for="email" class="form-label">{{ __('Email or Username') }}</label>
              {{ Form::text('email', old('email'), ['class' => 'form-control', 'id' => 'email', 'placeholder' => __('Enter your email'), 'required' => 'required']) }}
            </div>
            <div class="mb-6 form-password-toggle">
              <label for="password" class="form-label">{{ __('Password') }}</label>
              <div class="input-group input-group-merge">
                {{ Form::password('password', ['class' => 'form-control', 'id' => 'password', 'placeholder' => __('Enter Your Password'), 'required' => 'required']) }}
                <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
              </div>
            </div>
            <div class="my-8">
              <div class="d-flex justify-content-between">
                <div class="form-check mb-0 ms-2">
                  <input class="form-check-input" type="checkbox" id="remember-me" name="remember">
                  <label class="form-check-label" for="remember-me">
                    {{ __('Remember Me') }}
                  </label>
                </div>
                <a href="{{ route('password.request') }}">
                  <p class="mb-0">{{ __('Forgot Password?') }}</p>
                </a>
              </div>
            </div>

            @if ($settings['recaptcha_module'] == 'on')
                @if (isset($settings['google_recaptcha_version']) && $settings['google_recaptcha_version'] == 'v2-checkbox')
                    <div class="form-group col-lg-12 col-md-12 mt-3">
                        {!! NoCaptcha::display() !!}
                        @error('g-recaptcha-response')
                            <span class="small text-danger" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                @else
                    <div class="form-group col-lg-12 col-md-12 mt-3">
                        <input type="hidden" id="g-recaptcha-response" name="g-recaptcha-response" class="form-control">
                        @error('g-recaptcha-response')
                            <span class="error small text-danger" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                @endif
            @endif
            <button class="btn btn-primary d-grid w-100 mb-6" type="submit">
            {{ __('Login') }}
          </button>
            {{--<div class="mb-6">
              {{ Form::submit(__('Login'), ['class' => 'btn btn-primary d-grid w-100 mb-6']) }}
            </div>--}}
          {{ Form::close() }}
          <!-- /Login Form -->

          {{--@if ($settings['enable_signup'] == 'on')
            <p class="my-4 text-center">
              {{ __("Don't have an account?") }}
              <a href="{{ route('register') }}" class="text-primary">{{ __('Register') }}</a>
            </p>
          @endif--}}
        </div>
      </div>
      <!-- /Login Card -->
    </div>
  </div>
</div>
@endsection

<script src="{{ asset('js/jquery.min.js') }}"></script>
@if (isset($settings['recaptcha_module']) && $settings['recaptcha_module'] == 'on')
    @if (isset($settings['google_recaptcha_version']) && $settings['google_recaptcha_version'] == 'v2-checkbox')
        {!! NoCaptcha::renderJs() !!}
    @else
        <script src="https://www.google.com/recaptcha/api.js?render={{ $settings['google_recaptcha_key'] }}"></script>
        <script>
            $(document).ready(function() {
                grecaptcha.ready(function() {
                    grecaptcha.execute('{{ $settings['google_recaptcha_key'] }}', { action: 'submit' })
                    .then(function(token) {
                        $('#g-recaptcha-response').val(token);
                    });
                });
            });
        </script>
    @endif
@endif
