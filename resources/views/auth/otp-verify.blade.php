@php
$customizerHidden = 'customizer-hide';
@endphp
@php
    $pageConfigs = $pageConfigs ?? ['myLayout' => 'blank'];
@endphp
@extends('layouts/layoutMaster')

@section('title', 'Two Steps Verifications Basic - Pages')

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/@form-validation/form-validation.scss'
])
@endsection

@section('page-style')
@vite([
  'resources/assets/vendor/scss/pages/page-auth.scss'
])
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/cleavejs/cleave.js',
  'resources/assets/vendor/libs/@form-validation/popular.js',
  'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
  'resources/assets/vendor/libs/@form-validation/auto-focus.js'
])
@endsection

@section('page-script')
@vite([
  'resources/assets/js/pages-auth.js',
  'resources/assets/js/pages-auth-two-steps.js'
])
@endsection

@section('content')
<div class="authentication-wrapper authentication-basic px-6">
  <div class="authentication-inner py-6">
    <!-- Two Steps Verification -->
    <div class="card">
      <div class="card-body">
        <!-- Logo -->
        <div class="app-brand justify-content-center mb-6">
          <a href="" class="app-brand-link">
            <span class="app-brand-logo demo">
              @include('_partials.macros',['height'=>20,'withbg' => "fill: #fff;"])
            </span>
            <span class="app-brand-text demo text-heading fw-bold">
              {{ config('variables.templateName') }}
            </span>
          </a>
        </div>
        <!-- /Logo -->
        <h4 class="mb-1">Two Step Verification 💬</h4>
        <p class="text-start mb-6">
          We sent a verification code to your mobile. Enter the code from the mobile in the field below.
          <span class="fw-medium d-block mt-1 text-heading">******1234</span>
        </p>

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

        <p class="mb-0">Type your 6 digit security code</p>
        <form id="twoStepsForm" action="{{ route('otp.verify.submit') }}" method="POST">
          @csrf
          <div class="mb-6">
            <div class="auth-input-wrapper d-flex align-items-center justify-content-between numeral-mask-wrapper">
              <!-- Six separate inputs for each OTP digit -->
              <input type="tel" class="form-control auth-input h-px-50 text-center numeral-mask mx-sm-1 my-2" maxlength="1" autofocus>
              <input type="tel" class="form-control auth-input h-px-50 text-center numeral-mask mx-sm-1 my-2" maxlength="1">
              <input type="tel" class="form-control auth-input h-px-50 text-center numeral-mask mx-sm-1 my-2" maxlength="1">
              <input type="tel" class="form-control auth-input h-px-50 text-center numeral-mask mx-sm-1 my-2" maxlength="1">
              <input type="tel" class="form-control auth-input h-px-50 text-center numeral-mask mx-sm-1 my-2" maxlength="1">
              <input type="tel" class="form-control auth-input h-px-50 text-center numeral-mask mx-sm-1 my-2" maxlength="1">
            </div>
            <!-- Hidden field to store the complete OTP code -->
            <input type="hidden" name="otp" />
          </div>
          <button class="btn btn-primary d-grid w-100 mb-6" type="submit">
            Verify my account
          </button>
          <div class="text-center">
            Didn't get the code?
            <a href="javascript:void(0);">
              Resend
            </a>
          </div>
        </form>
      </div>
    </div>
    <!-- /Two Steps Verification -->
  </div>
</div>
@endsection
