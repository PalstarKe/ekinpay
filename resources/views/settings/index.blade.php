@extends('layouts/layoutMaster')
@section('page-title')
    {{ __('Settings') }}
@endsection


<!-- Page Scripts -->
@section('page-script')
@vite([
  'resources/assets/js/forms-selects.js',
  'resources/assets/js/forms-tagify.js',
  'resources/assets/js/forms-typeahead.js'
])
@endsection
@php
    $lang = \App\Models\Utility::getValByName('default_language');
    // $logo=asset(Storage::url('uploads/logo/'));
    $logo = \App\Models\Utility::get_file('uploads/logo');

    $logo_light = \App\Models\Utility::getValByName('logo_light');
    $logo_dark = \App\Models\Utility::getValByName('logo_dark');
    $company_favicon = \App\Models\Utility::getValByName('company_favicon');
    $setting = \App\Models\Utility::colorset();
    $color = !empty($setting['color']) ? $setting['color'] : 'theme-3';
    $flag = !empty($setting['color_flag']) ? $setting['color_flag'] : '';
    $SITE_RTL = isset($setting['SITE_RTL']) ? $setting['SITE_RTL'] : 'off';
    $meta_image = \App\Models\Utility::get_file('uploads/meta/');
    $google_recaptcha_version = ['v2-checkbox' => __('v2'), 'v3' => __('v3')];
@endphp

{{-- Storage setting --}}
@php
    $file_type = config('files_types');
    $setting = App\Models\Utility::settings();

    $local_storage_validation = $setting['local_storage_validation'];
    $local_storage_validations = explode(',', $local_storage_validation);

    $s3_storage_validation = $setting['s3_storage_validation'];
    $s3_storage_validations = explode(',', $s3_storage_validation);

    $wasabi_storage_validation = $setting['wasabi_storage_validation'];
    $wasabi_storage_validations = explode(',', $wasabi_storage_validation);

@endphp
<style>


</style>

<script>
    // document.addEventListener("DOMContentLoaded", function() {
    //     const body = document.querySelector("body");
    //     const input = document.getElementById("colorPicker");
    //     const colorCode = document.getElementById("colorCode");
    //     const button = document.getElementById("changeColorButton");

    //     setColor();
    //     input.addEventListener("input", setColor);

    //     function setColor() {
    //         // body.style.backgroundColor = input.value;
    //         colorCode.innerHTML = input.value;
    //     }
    // });
</script>
{{-- end Storage setting --}}
@push('css-page')
    @if ($color == 'theme-3')
        <style>
            .btn-check:checked+.btn-outline-primary,
            .btn-check:active+.btn-outline-primary,
            .btn-outline-primary:active,
            .btn-outline-primary.active,
            .btn-outline-primary.dropdown-toggle.show {
                color: #ffffff;
                background-color: #6fd943 !important;
                border-color: #6fd943 !important;
            }

            .btn-outline-primary:hover {
                color: #ffffff;
                background-color: #6fd943 !important;
                border-color: #6fd943 !important;
            }

            .btn[class*="btn-outline-"]:hover {

                border-color: #6fd943 !important;
            }
        </style>
    @endif
    @if ($color == 'theme-2')
        <style>
            .btn-check:checked+.btn-outline-primary,
            .btn-check:active+.btn-outline-primary,
            .btn-outline-primary:active,
            .btn-outline-primary.active,
            .btn-outline-primary.dropdown-toggle.show {
                color: #ffffff;
                background: linear-gradient(141.55deg, rgba(240, 244, 243, 0) 3.46%, #4ebbd3 99.86%)#1f3996 !important;
                border-color: #4ebbd3 !important;
            }

            .btn-outline-primary:hover {
                color: #ffffff;
                background: linear-gradient(141.55deg, rgba(240, 244, 243, 0) 3.46%, #4ebbd3 99.86%)#1f3996 !important;
                border-color: #4ebbd3 !important;
            }

            .btn.btn-outline-primary {
                color: #1F3996;
                border-color: #4ebbd3 !important;
            }
        </style>
    @endif
    @if ($color == 'theme-4')
        <style>
            .btn-check:checked+.btn-outline-primary,
            .btn-check:active+.btn-outline-primary,
            .btn-outline-primary:active,
            .btn-outline-primary.active,
            .btn-outline-primary.dropdown-toggle.show {
                color: #ffffff;
                background-color: #584ed2 !important;
                border-color: #584ed2 !important;

            }

            .btn-outline-primary:hover {
                color: #ffffff;
                background-color: #584ed2 !important;
                border-color: #584ed2 !important;
            }

            .btn.btn-outline-primary {
                color: #584ed2;
                border-color: #584ed2 !important;
            }
        </style>
    @endif
    @if ($color == 'theme-1')
        <style>
            .btn-check:checked+.btn-outline-primary,
            .btn-check:active+.btn-outline-primary,
            .btn-outline-primary:active,
            .btn-outline-primary.active,
            .btn-outline-primary.dropdown-toggle.show {
                color: #ffffff;
                background: linear-gradient(141.55deg, rgba(81, 69, 157, 0) 3.46%, rgba(255, 58, 110, 0.6) 99.86%), #51459d !important;
                border-color: #51459d !important;
            }


            body.theme-1 .btn-outline-primary:hover {
                color: #ffffff;
                background: linear-gradient(141.55deg, rgba(81, 69, 157, 0) 3.46%, rgba(255, 58, 110, 0.6) 99.86%), #51459d !important;
                border-color: #51459d !important;
            }
        </style>
    @endif
@endpush


@push('page-script')
    <script>
        var scrollSpy = new bootstrap.ScrollSpy(document.body, {
            target: '#useradd-sidenav',
            offset: 300,
        })

        $('.colorPicker').on('click', function(e) {
            $('body').removeClass('custom-color');
            if (/^theme-\d+$/) {
                $('body').removeClassRegex(/^theme-\d+$/);
            }
            $('body').addClass('custom-color');
            $('.themes-color-change').removeClass('active_color');
            $(this).addClass('active_color');
            const input = document.getElementById("color-picker");
            setColor();
            input.addEventListener("input", setColor);

            function setColor() {
                $(':root').css('--color-customColor', input.value);
            }

            $(`input[name='color_flag`).val('true');
        });

        $('.themes-color-change').on('click', function() {

            $(`input[name='color_flag`).val('false');

            var color_val = $(this).data('value');
            $('body').removeClass('custom-color');
            if (/^theme-\d+$/) {
                $('body').removeClassRegex(/^theme-\d+$/);
            }
            $('body').addClass(color_val);
            $('.theme-color').prop('checked', false);
            $('.themes-color-change').removeClass('active_color');
            $('.colorPicker').removeClass('active_color');
            $(this).addClass('active_color');
            $(`input[value=${color_val}]`).prop('checked', true);
        });

        $.fn.removeClassRegex = function(regex) {
            return $(this).removeClass(function(index, classes) {
                return classes.split(/\s+/).filter(function(c) {
                    return regex.test(c);
                }).join(' ');
            });
        };

        // storage setting
        $(document).on('change', '[name=storage_setting]', function() {
            if ($(this).val() == 's3') {
                $('.s3-setting').removeClass('d-none');
                $('.wasabi-setting').addClass('d-none');
                $('.local-setting').addClass('d-none');
            } else if ($(this).val() == 'wasabi') {
                $('.s3-setting').addClass('d-none');
                $('.wasabi-setting').removeClass('d-none');
                $('.local-setting').addClass('d-none');
            } else {
                $('.s3-setting').addClass('d-none');
                $('.wasabi-setting').addClass('d-none');
                $('.local-setting').removeClass('d-none');
            }
        });
    </script>

    <script>
        document.getElementById('logo_dark').onchange = function() {
            var src = URL.createObjectURL(this.files[0])
            document.getElementById('image').src = src
        }
        document.getElementById('logo_light').onchange = function() {
            var src = URL.createObjectURL(this.files[0])
            document.getElementById('image1').src = src
        }
        document.getElementById('favicon').onchange = function() {
            var src = URL.createObjectURL(this.files[0])
            document.getElementById('image2').src = src
        }
    </script>

    <script type="text/javascript">
        $(document).on("click", '.send_email', function(e) {
            e.preventDefault();
            var title = $(this).attr('data-title');
            var size = 'md';
            var url = $(this).attr('data-url');

            if (typeof url != 'undefined') {
                $("#commonModal .modal-title").html(title);
                $("#commonModal .modal-dialog").addClass('modal-' + size);
                $("#commonModal").modal('show');


                $.post(url, {
                    _token: '{{ csrf_token() }}',
                    mail_driver: $("#mail_driver").val(),
                    mail_host: $("#mail_host").val(),
                    mail_port: $("#mail_port").val(),
                    mail_username: $("#mail_username").val(),
                    mail_password: $("#mail_password").val(),
                    mail_encryption: $("#mail_encryption").val(),
                    mail_from_address: $("#mail_from_address").val(),
                    mail_from_name: $("#mail_from_name").val(),

                }, function(data) {
                    $('#commonModal .body').html(data);
                });
            }
        });
        $(document).on('submit', '#test_email', function(e) {
            e.preventDefault();
            // $("#email_sending").show();
            var post = $(this).serialize();
            var url = $(this).attr('action');
            $.ajax({
                type: "post",
                url: url,
                data: post,
                cache: false,
                beforeSend: function() {
                    $('#test_email .btn-create').attr('disabled', 'disabled');
                },
                success: function(data) {
                    // console.log(data)
                    if (data.success) {
                        show_toastr('success', data.message, 'success');
                    } else {
                        show_toastr('error', data.message, 'error');
                    }
                    // $("#email_sending").hide();
                    $('#commonModal').modal('hide');


                },
                complete: function() {
                    $('#test_email .btn-create').removeAttr('disabled');
                },
            });
        });
    </script>

    {{--    for cookie setting --}}
    <script type="text/javascript">
        function enablecookie() {
            const element = $('#enable_cookie').is(':checked');
            $('.cookieDiv').addClass('disabledCookie');
            if (element == true) {
                $('.cookieDiv').removeClass('disabledCookie');
                $("#cookie_logging").attr('checked', true);
            } else {
                $('.cookieDiv').addClass('disabledCookie');
                $("#cookie_logging").attr('checked', false);
            }
        }
    </script>

    <script>
        if ($('#cust-darklayout').length > 0) {
            var custthemedark = document.querySelector("#cust-darklayout");
            custthemedark.addEventListener("click", function() {
                if (custthemedark.checked) {
                    $('#main-style-link').attr('href', '{{ config('app.url') }}' +
                        '/public/assets/css/style-dark.css');
                    document.body.style.background = 'linear-gradient(141.55deg, #22242C 3.46%, #22242C 99.86%)';

                    $('.dash-sidebar .main-logo a img').attr('src',
                        '{{ isset($logo_light) && !empty($logo_light) ? $logo . $logo_light : $logo . '/logo-light.png' }}'
                        );

                } else {
                    $('#main-style-link').attr('href', '{{ config('app.url') }}' + '/public/assets/css/style.css');
                    document.body.style.setProperty('background',
                        'linear-gradient(141.55deg, rgba(240, 244, 243, 0) 3.46%, #f0f4f3 99.86%)', 'important');

                    $('.dash-sidebar .main-logo a img').attr('src',
                        '{{ isset($logo_light) && !empty($logo_light) ? $logo . $logo_light : $logo . '/logo-dark.png' }}'
                        );

                }
            });
        }

        if ($('#cust-theme-bg').length > 0) {
            var custthemebg = document.querySelector("#cust-theme-bg");
            custthemebg.addEventListener("click", function() {
                if (custthemebg.checked) {
                    document.querySelector(".dash-sidebar").classList.add("transprent-bg");
                    document
                        .querySelector(".dash-header:not(.dash-mob-header)")
                        .classList.add("transprent-bg");
                } else {
                    document.querySelector(".dash-sidebar").classList.remove("transprent-bg");
                    document
                        .querySelector(".dash-header:not(.dash-mob-header)")
                        .classList.remove("transprent-bg");
                }
            });
        }
    </script>
@endpush

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="row">
                <div class="col-xl-3">
                    <div class="card sticky-top" style="top:30px">
                        <div class="list-group list-group-flush" id="useradd-sidenav">
                            <a href="#brand-settings"
                                class="list-group-item list-group-item-action border-0">{{ __('Brand Settings') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a>
                            <a href="#email-settings"
                                class="list-group-item list-group-item-action border-0">{{ __('Email Settings') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a>
                            <a href="#payment-settings"
                                class="list-group-item list-group-item-action border-0">{{ __('Payment Settings') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a>
                            <a href="#pusher-settings"
                                class="list-group-item list-group-item-action border-0">{{ __('Pusher Settings') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a>
                            <a href="#recaptcha_settings"
                                class="list-group-item list-group-item-action border-0">{{ __('ReCaptcha Settings') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a>
                            <a href="#storage-settings"
                                class="list-group-item list-group-item-action border-0">{{ __('Storage Settings') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a>
                            <!-- <a href="#seo-settings"
                                class="list-group-item list-group-item-action border-0">{{ __('SEO Settings') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a> -->
                            <a href="#cookie-settings"
                                class="list-group-item list-group-item-action border-0">{{ __('Cookie Settings') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a>
                            <a href="#cache-settings"
                                class="list-group-item list-group-item-action border-0">{{ __('Cache Settings') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a>
                            <!-- <a href="#chat-gpt-settings"
                                class="list-group-item list-group-item-action border-0">{{ __('Chat GPT Settings') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a> -->

                        </div>
                    </div>
                </div>

                <div class="col-xl-9">
                    {{--  Start for all settings tab --}}

                    <!--Site Settings-->
                    <div id="brand-settings" class="card mb-3">
                        <div class="card-header">
                            <h5>{{ __('Brand Settings') }}</h5>
                        </div>
                        {{ Form::model($settings, ['url' => 'systems', 'method' => 'POST', 'enctype' => 'multipart/form-data']) }}
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-4 col-sm-6 col-md-6">
                                    <div class="card logo_card">
                                        <div class="card-header">
                                            <h5>{{ __('Logo dark') }}</h5>
                                        </div>
                                        <div class="card-body pt-0">
                                            <div class="setting-card">
                                                <div class="logo-content mt-4">
                                                    <img id="image"
                                                        src="{{ $logo . '/' . (isset($logo_dark) && !empty($logo_dark) ? $logo_dark : 'logo-dark.png') . '?timestamp=' . time() }}"
                                                        class="big-logo">
                                                </div>
                                                <div class="choose-files mt-5">
                                                    <label for="logo_dark">
                                                        <div class=" bg-primary company_logo_update"> <i
                                                                class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                                                        </div>
                                                        <input type="file" name="logo_dark" id="logo_dark"
                                                            class="form-control file" data-filename="logo_dark">
                                                    </label>
                                                </div>
                                                @error('logo_dark')
                                                    <div class="row">
                                                        <span class="invalid-logo" role="alert">
                                                            <strong class="text-danger">{{ $message }}</strong>
                                                        </span>
                                                    </div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-md-6">
                                    <div class="card logo_card">
                                        <div class="card-header">
                                            <h5>{{ __('Logo Light') }}</h5>
                                        </div>
                                        <div class="card-body pt-0">
                                            <div class=" setting-card">
                                                <div class="logo-content mt-4">
                                                    <img id="image1"
                                                        src="{{ $logo . '/' . (isset($logo_light) && !empty($logo_light) ? $logo_light : 'logo-light.png') . '?timestamp=' . time() }}"
                                                        class="big-logo img_setting">
                                                </div>
                                                <div class="choose-files mt-5">
                                                    <label for="logo_light">
                                                        <div class=" bg-primary dark_logo_update"> <i
                                                                class="ti ti-upload px-1">
                                                            </i>{{ __('Choose file here') }}
                                                        </div>
                                                        <input type="file" name="logo_light" id="logo_light"
                                                            class="form-control file" data-filename="logo_light">
                                                    </label>
                                                </div>
                                                @error('logo_light')
                                                    <div class="row">
                                                        <span class="invalid-logo" role="alert">
                                                            <strong class="text-danger">{{ $message }}</strong>
                                                        </span>
                                                    </div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-md-6">
                                    <div class="card logo_card">
                                        <div class="card-header">
                                            <h5>{{ __('Favicon') }}</h5>
                                        </div>
                                        <div class="card-body pt-0">
                                            <div class=" setting-card">
                                                <div class="logo-content mt-4">
                                                    <img id="image2"
                                                        src="{{ $logo . '/' . (isset($company_favicon) && !empty($company_favicon) ? $company_favicon : 'favicon.png') . '?timestamp=' . time() }}"
                                                        width="50px" class="img_setting">
                                                </div>
                                                <div class="choose-files mt-5">
                                                    <label for="favicon">
                                                        <div class="bg-primary company_favicon_update"> <i
                                                                class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                                                        </div>
                                                        <input type="file" class="form-control file" id="favicon"
                                                            name="favicon" data-filename="favicon">
                                                    </label>
                                                </div>
                                                @error('favicon')
                                                    <div class="row">
                                                        <span class="invalid-logo" role="alert">
                                                            <strong class="text-danger">{{ $message }}</strong>
                                                        </span>
                                                    </div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row ">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            {{ Form::label('title_text', __('Title Text'), ['class' => 'form-label']) }}
                                            {{ Form::text('title_text', null, ['class' => 'form-control', 'placeholder' => __('Title Text')]) }}
                                            @error('title_text')
                                                <span class="invalid-title_text" role="alert">
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            {{ Form::label('footer_text', __('Footer Text'), ['class' => 'form-label']) }}
                                            {{ Form::text('footer_text', Utility::getValByName('footer_text'), ['class' => 'form-control', 'placeholder' => __('Enter Footer Text')]) }}
                                            @error('footer_text')
                                                <span class="invalid-footer_text" role="alert">
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            {{ Form::label('default_language', __('Default Language'), ['class' => 'form-label']) }}
                                            <div class="changeLanguage">
                                                <select name="default_language" id="default_language"
                                                    class="form-control select">
                                                    @foreach (\App\Models\Utility::languages() as $code => $language)
                                                        <option @if ($lang == $code) selected @endif
                                                            value="{{ $code }}">
                                                            {{ ucFirst($language) }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            @error('default_language')
                                                <span class="invalid-default_language" role="alert">
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <div class="form-group">
                            <button class="btn-submit btn btn-primary" type="submit">
                                    {{ __('Save Changes') }}
                                </button>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>

                    <!--Email Settings-->
                    <div id="email-settings" class="card mb-3">
                        <div class="card-header">
                            <h5>{{ __('Email Settings') }}</h5>
                            <small
                                class="text-muted">{{ __('This SMTP will be used for system-level email sending. Additionally, if a company user does not set their SMTP, then this SMTP will be used for sending emails.') }}</small>
                        </div>
                        <div class="card-body">
                            {{ Form::open(['route' => 'email.settings', 'method' => 'post']) }}
                            @csrf
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        {{ Form::label('mail_driver', __('Mail Driver'), ['class' => 'form-label']) }}
                                        {{ Form::text('mail_driver', isset($settings['mail_driver']) ? $settings['mail_driver'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Mail Driver')]) }}
                                        @error('mail_driver')
                                            <span class="invalid-mail_driver" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        {{ Form::label('mail_host', __('Mail Host'), ['class' => 'form-label']) }}
                                        {{ Form::text('mail_host', isset($settings['mail_host']) ? $settings['mail_host'] : '', ['class' => 'form-control ', 'placeholder' => __('Enter Mail Host')]) }}
                                        @error('mail_host')
                                            <span class="invalid-mail_driver" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        {{ Form::label('mail_port', __('Mail Port'), ['class' => 'form-label']) }}
                                        {{ Form::text('mail_port', isset($settings['mail_port']) ? $settings['mail_port'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Mail Port')]) }}
                                        @error('mail_port')
                                            <span class="invalid-mail_port" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        {{ Form::label('mail_username', __('Mail Username'), ['class' => 'form-label']) }}
                                        {{ Form::text('mail_username', isset($settings['mail_username']) ? $settings['mail_username'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Mail Username')]) }}
                                        @error('mail_username')
                                            <span class="invalid-mail_username" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        {{ Form::label('mail_password', __('Mail Password'), ['class' => 'form-label']) }}
                                        {{ Form::text('mail_password', isset($settings['mail_password']) ? $settings['mail_password'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Mail Password')]) }}
                                        @error('mail_password')
                                            <span class="invalid-mail_password" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        {{ Form::label('mail_encryption', __('Mail Encryption'), ['class' => 'form-label']) }}
                                        {{ Form::text('mail_encryption', isset($settings['mail_encryption']) ? $settings['mail_encryption'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Mail Encryption')]) }}
                                        @error('mail_encryption')
                                            <span class="invalid-mail_encryption" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        {{ Form::label('mail_from_address', __('Mail From Address'), ['class' => 'form-label']) }}
                                        {{ Form::text('mail_from_address', isset($settings['mail_from_address']) ? $settings['mail_from_address'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Mail From Address')]) }}
                                        @error('mail_from_address')
                                            <span class="invalid-mail_from_address" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        {{ Form::label('mail_from_name', __('Mail From Name'), ['class' => 'form-label']) }}
                                        {{ Form::text('mail_from_name', isset($settings['mail_from_name']) ? $settings['mail_from_name'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Mail From Name')]) }}
                                        @error('mail_from_name')
                                            <span class="invalid-mail_from_name" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="row">
                            <div class="card-footer d-flex justify-content-end">
                                <div class="form-group me-4">
                                    <a href="#" data-url="{{ route('test.mail') }}"
                                        data-title="{{ __('Send Test Mail') }}" class="btn btn-success send_email me-1">
                                        {{ __('Send Test Mail') }}
                                    </a>
                                    <button class="btn-submit btn btn-primary" type="submit">
                                        {{ __('Save Changes') }}
                                    </button>
                                </div>

                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>

                    <!--Payment Settings-->
                    <div class="card mb-3" id="payment-settings">
                        <div class="card-header">
                            <h5>{{ 'Payment Settings' }}</h5>
                            <small class="text-secondary font-weight-bold">
                                {{ __('These details will be used to collect subscription plan payments.Each subscription plan will have a payment button based on the below configuration.') }}
                            </small>
                        </div>
                        {{ Form::open(['route' => 'payment.settings', 'method' => 'post']) }}
                        @csrf
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12">
                                    <div class="row">
                                        <div class="col-md-6 form-group">
                                            <label class="col-form-label">{{ __('Currency') }} *</label>

                                            {{ Form::text('currency', isset($admin_payment_setting['currency']) ? $admin_payment_setting['currency'] : '', ['class' => 'form-control font-style', 'required', 'placeholder' => __('Enter Currency')]) }}
                                            <small class="text-xs">
                                                {{ __('Note: Add currency code as per three-letter ISO code') }}.
                                                <a href="https://stripe.com/docs/currencies"
                                                    target="_blank">{{ __('You can find out how to do that here.') }}</a>
                                            </small>
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label for="currency_symbol"
                                                class="col-form-label">{{ __('Currency Symbol') }}</label>
                                            {{ Form::text('currency_symbol', isset($admin_payment_setting['currency_symbol']) ? $admin_payment_setting['currency_symbol'] : '', ['class' => 'form-control', 'required', 'placeholder' => __('Enter Currency Symbol')]) }}
                                        </div>
                                    </div>
                                    <div class="faq justify-content-center">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="accordion accordion-custom-button mt-4"
                                                    id="accordionExample">

                                                    <!-- Manually -->
                                                    <div class="accordion-item card">
                                                        <h2 class="accordion-header" id="headingOne">
                                                            <button class="accordion-button collapsed" type="button"
                                                                data-bs-toggle="collapse"
                                                                data-bs-target="#collapseManually" aria-expanded="false"
                                                                aria-controls="collapseOne">
                                                                <span class="d-flex align-items-center">
                                                                    {{ __('Manually') }}
                                                                </span>
                                                                <div class="d-flex align-items-center">
                                                                    <span class="me-2">{{ __('Enable') }}:</span>
                                                                    <div class="form-check form-switch custom-switch-v1">
                                                                        <input type="hidden"
                                                                            name="is_manually_payment_enabled"
                                                                            value="off">
                                                                        <input type="checkbox"
                                                                            class="form-check-input input-primary"
                                                                            id="customswitchv1-1 is_manually_payment_enabled"
                                                                            name="is_manually_payment_enabled"
                                                                            {{ isset($admin_payment_setting['is_manually_payment_enabled']) && $admin_payment_setting['is_manually_payment_enabled'] == 'on' ? 'checked="checked"' : '' }}>
                                                                    </div>
                                                                </div>
                                                            </button>
                                                        </h2>
                                                        <div id="collapseManually" class="accordion-collapse collapse"
                                                            aria-labelledby="headingOne"
                                                            data-bs-parent="#accordionExample">
                                                            <div class="accordion-body">
                                                                <div class="row gy-4">
                                                                    <div class="col-lg-12">
                                                                        <div class="input-edits">
                                                                            <small class="text-md">
                                                                                {{ __('Requesting manual payment for the planned amount for the subscriptions plan.') }}
                                                                            </small>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- Mpesa -->
                                                    <div class="accordion-item card mt-2">
                                                        <h2 class="accordion-header" id="headingOne">
                                                            <button class="accordion-button collapsed" type="button"
                                                                data-bs-toggle="collapse" data-bs-target="#collapseOne"
                                                                aria-expanded="false" aria-controls="collapseOne">
                                                                <span class="d-flex align-items-center">
                                                                    {{ __('Mpesa') }}
                                                                </span>
                                                                <div class="d-flex align-items-center">
                                                                    <span class="me-2">{{ __('Enable') }}:</span>
                                                                    <div class="form-check form-switch custom-switch-v1">
                                                                        <input type="hidden" name="is_mpesa_enabled"
                                                                            value="off">
                                                                        <input type="checkbox"
                                                                            class="form-check-input input-primary"
                                                                            id="customswitchv1-1 is_mpesa_enabled"
                                                                            name="is_mpesa_enabled"
                                                                            {{ isset($admin_payment_setting['is_mpesa_enabled']) && $admin_payment_setting['is_mpesa_enabled'] == 'on' ? 'checked="checked"' : '' }}>
                                                                    </div>
                                                                </div>
                                                            </button>
                                                        </h2>
                                                        <div id="collapseOne" class="accordion-collapse collapse"
                                                            aria-labelledby="headingOne"
                                                            data-bs-parent="#accordionExample">
                                                            <div class="accordion-body">
                                                                <div class="d-flex">
                                                                    <div class="mr-2" style="margin-right: 15px;">
                                                                        <div class="border card p-3">
                                                                            <div class="form-check">
                                                                                <label class="form-check-label text-dark">
                                                                                    <input type="radio"
                                                                                        name="mpesa_mode" value="sandbox"
                                                                                        class="form-check-input"
                                                                                        {{ (isset($admin_payment_setting['mpesa_mode']) && $admin_payment_setting['mpesa_mode'] == '') || (isset($admin_payment_setting['mpesa_mode']) && $admin_payment_setting['mpesa_mode'] == 'sandbox') ? 'checked="checked"' : '' }}>
                                                                                    {{ __('Sandbox') }}
                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="mr-2" style="margin-right: 15px;">
                                                                        <div class="border card p-3">
                                                                            <div class="form-check">
                                                                                <label class="form-check-label text-dark">
                                                                                    <input type="radio"
                                                                                        name="mpesa_mode" value="live"
                                                                                        class="form-check-input"
                                                                                        {{ isset($admin_payment_setting['mpesa_mode']) && $admin_payment_setting['mpesa_mode'] == 'live' ? 'checked="checked"' : '' }}>
                                                                                    {{ __('Live') }}
                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="row gy-4">
                                                                    <div class="col-lg-6">
                                                                        <div class="input-edits">
                                                                            <div class="form-group">
                                                                                {{ Form::label('mpesa_key', __('Mpesa Consumer Key'), ['class' => 'col-form-label']) }}
                                                                                {{ Form::text('mpesa_key', isset($admin_payment_setting['mpesa_key']) ? $admin_payment_setting['mpesa_key'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Mpesa Consumer Key')]) }}
                                                                                @if ($errors->has('mpesa_key'))
                                                                                    <span class="invalid-feedback d-block">
                                                                                        {{ $errors->first('mpesa_key') }}
                                                                                    </span>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-6">
                                                                        <div class="input-edits">
                                                                            <div class="form-group">
                                                                                {{ Form::label('mpesa_secret', __('Mpesa Consumer Secret'), ['class' => 'col-form-label']) }}
                                                                                {{ Form::text('mpesa_secret', isset($admin_payment_setting['mpesa_secret']) ? $admin_payment_setting['mpesa_secret'] : '', ['class' => 'form-control ', 'placeholder' => __('Enter Mpesa Consumer Secret')]) }}
                                                                                @if ($errors->has('mpesa_secret'))
                                                                                    <span class="invalid-feedback d-block">
                                                                                        {{ $errors->first('mpesa_secret') }}
                                                                                    </span>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-6">
                                                                        <div class="input-edits">
                                                                            <div class="form-group">
                                                                                {{ Form::label('shortcode', __('Mpesa Business Shortcode'), ['class' => 'col-form-label']) }}
                                                                                {{ Form::text('shortcode', isset($admin_payment_setting['shortcode']) ? $admin_payment_setting['shortcode'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Mpesa Business Shortcode')]) }}
                                                                                @if ($errors->has('shortcode'))
                                                                                    <span class="invalid-feedback d-block">
                                                                                        {{ $errors->first('shortcode') }}
                                                                                    </span>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-6">
                                                                        <div class="input-edits">
                                                                            <div class="form-group">
                                                                                {{ Form::label('passkey', __('Mpesa Passkey'), ['class' => 'col-form-label']) }}
                                                                                {{ Form::text('passkey', isset($admin_payment_setting['passkey']) ? $admin_payment_setting['passkey'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Mpesa Passkey')]) }}
                                                                                @if ($errors->has('passkey'))
                                                                                    <span class="invalid-feedback d-block">
                                                                                        {{ $errors->first('passkey') }}
                                                                                    </span>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- Bank Transfer -->
                                                    {{--<div class="accordion-item card">
                                                        <h2 class="accordion-header" id="headingOne">
                                                            <button class="accordion-button collapsed" type="button"
                                                                data-bs-toggle="collapse" data-bs-target="#collapseBank"
                                                                aria-expanded="false" aria-controls="collapseOne">
                                                                <span class="d-flex align-items-center">
                                                                    {{ __('Bank Transfer') }}
                                                                </span>
                                                                <div class="d-flex align-items-center">
                                                                    <span class="me-2">{{ __('Enable') }}:</span>
                                                                    <div class="form-check form-switch custom-switch-v1">
                                                                        <input type="hidden"
                                                                            name="is_bank_transfer_enabled"
                                                                            value="off">
                                                                        <input type="checkbox"
                                                                            class="form-check-input input-primary"
                                                                            id="customswitchv1-1 is_bank_transfer_enabled"
                                                                            name="is_bank_transfer_enabled"
                                                                            {{ isset($admin_payment_setting['is_bank_transfer_enabled']) && $admin_payment_setting['is_bank_transfer_enabled'] == 'on' ? 'checked="checked"' : '' }}>
                                                                    </div>
                                                                </div>
                                                            </button>
                                                        </h2>
                                                        <div id="collapseBank" class="accordion-collapse collapse"
                                                            aria-labelledby="headingOne"
                                                            data-bs-parent="#accordionExample">
                                                            <div class="accordion-body">
                                                                <div class="row gy-4">
                                                                    <div class="col-lg-12">
                                                                        <div class="input-edits">
                                                                            <div class="form-group">
                                                                                {{ Form::label('bank_details', __('Bank Details'), ['class' => 'col-form-label']) }}
                                                                                {{ Form::textarea('bank_details', isset($admin_payment_setting['bank_details']) ? $admin_payment_setting['bank_details'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Your Bank Details'), 'rows' => 4]) }}
                                                                                <small class="text-xs">
                                                                                    {{ __('Example : Bank : bank name </br> Account Number : 0000 0000 </br>') }}
                                                                                </small>
                                                                                @if ($errors->has('bank_details'))
                                                                                    <span class="invalid-feedback d-block">
                                                                                        {{ $errors->first('bank_details') }}
                                                                                    </span>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>--}}
                                                    <!-- Paypal -->
                                                    {{--<div class="accordion-item card">
                                                        <h2 class="accordion-header" id="headingTwo">
                                                            <button class="accordion-button collapsed" type="button"
                                                                data-bs-toggle="collapse" data-bs-target="#collapseTwo"
                                                                aria-expanded="false" aria-controls="collapseTwo">
                                                                <span class="d-flex align-items-center">
                                                                    {{ __('Paypal') }}
                                                                </span>
                                                                <div class="d-flex align-items-center">
                                                                    <span class="me-2">{{ __('Enable') }}:</span>
                                                                    <div class="form-check form-switch custom-switch-v1">
                                                                        <input type="hidden" name="is_paypal_enabled"
                                                                            value="off">
                                                                        <input type="checkbox"
                                                                            class="form-check-input input-primary"
                                                                            id="customswitchv1-1 is_paypal_enabled"
                                                                            name="is_paypal_enabled"
                                                                            {{ isset($admin_payment_setting['is_paypal_enabled']) && $admin_payment_setting['is_paypal_enabled'] == 'on' ? 'checked="checked"' : '' }}>
                                                                    </div>
                                                                </div>
                                                            </button>
                                                        </h2>
                                                        <div id="collapseTwo" class="accordion-collapse collapse"
                                                            aria-labelledby="headingTwo"
                                                            data-bs-parent="#accordionExample">
                                                            <div class="accordion-body">
                                                                <div class="d-flex">
                                                                    <div class="mr-2" style="margin-right: 15px;">
                                                                        <div class="border card p-3">
                                                                            <div class="form-check">
                                                                                <label class="form-check-label text-dark">
                                                                                    <input type="radio"
                                                                                        name="paypal_mode" value="sandbox"
                                                                                        class="form-check-input"
                                                                                        {{ (isset($admin_payment_setting['paypal_mode']) && $admin_payment_setting['paypal_mode'] == '') || (isset($admin_payment_setting['paypal_mode']) && $admin_payment_setting['paypal_mode'] == 'sandbox') ? 'checked="checked"' : '' }}>
                                                                                    {{ __('Sandbox') }}
                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="mr-2" style="margin-right: 15px;">
                                                                        <div class="border card p-3">
                                                                            <div class="form-check">
                                                                                <label class="form-check-label text-dark">
                                                                                    <input type="radio"
                                                                                        name="paypal_mode" value="live"
                                                                                        class="form-check-input"
                                                                                        {{ isset($admin_payment_setting['paypal_mode']) && $admin_payment_setting['paypal_mode'] == 'live' ? 'checked="checked"' : '' }}>
                                                                                    {{ __('Live') }}
                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="row gy-4">
                                                                    <div class="col-lg-6">
                                                                        <div class="input-edits">
                                                                            <div class="form-group">
                                                                                <label class="col-form-label"
                                                                                    for="paypal_client_id">{{ __('Client ID') }}</label>
                                                                                <input type="text"
                                                                                    name="paypal_client_id"
                                                                                    id="paypal_client_id"
                                                                                    class="form-control"
                                                                                    value="{{ !isset($admin_payment_setting['paypal_client_id']) || is_null($admin_payment_setting['paypal_client_id']) ? '' : $admin_payment_setting['paypal_client_id'] }}"
                                                                                    placeholder="{{ __('Client ID') }}">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-6">
                                                                        <div class="input-edits">
                                                                            <div class="form-group">
                                                                                <label class="col-form-label"
                                                                                    for="paypal_secret_key">{{ __('Secret Key') }}</label>
                                                                                <input type="text"
                                                                                    name="paypal_secret_key"
                                                                                    id="paypal_secret_key"
                                                                                    class="form-control"
                                                                                    value="{{ isset($admin_payment_setting['paypal_secret_key']) ? $admin_payment_setting['paypal_secret_key'] : '' }}"
                                                                                    placeholder="{{ __('Secret Key') }}">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>--}}

                                                    <!-- Paystack -->
                                                    {{--<div class="accordion-item card">
                                                        <h2 class="accordion-header" id="headingThree">
                                                            <button class="accordion-button collapsed" type="button"
                                                                data-bs-toggle="collapse" data-bs-target="#collapseThree"
                                                                aria-expanded="false" aria-controls="collapseThree">
                                                                <span class="d-flex align-items-center">
                                                                    {{ __('Paystack') }}
                                                                </span>
                                                                <div class="d-flex align-items-center">
                                                                    <span class="me-2">{{ __('Enable') }}:</span>
                                                                    <div class="form-check form-switch custom-switch-v1">
                                                                        <input type="hidden" name="is_paystack_enabled"
                                                                            value="off">
                                                                        <input type="checkbox"
                                                                            class="form-check-input input-primary"
                                                                            id="customswitchv1-1 is_paystack_enabled"
                                                                            name="is_paystack_enabled"
                                                                            {{ isset($admin_payment_setting['is_paystack_enabled']) && $admin_payment_setting['is_paystack_enabled'] == 'on' ? 'checked="checked"' : '' }}>
                                                                    </div>
                                                                </div>
                                                            </button>
                                                        </h2>
                                                        <div id="collapseThree" class="accordion-collapse collapse"
                                                            aria-labelledby="headingThree"
                                                            data-bs-parent="#accordionExample">
                                                            <div class="accordion-body">
                                                                <div class="row gy-4">
                                                                    <div class="col-lg-6">
                                                                        <div class="input-edits">
                                                                            <div class="form-group">
                                                                                <label for="paypal_client_id"
                                                                                    class="col-form-label">{{ __('Public Key') }}</label>
                                                                                <input type="text"
                                                                                    name="paystack_public_key"
                                                                                    id="paystack_public_key"
                                                                                    class="form-control"
                                                                                    value="{{ isset($admin_payment_setting['paystack_public_key']) ? $admin_payment_setting['paystack_public_key'] : '' }}"
                                                                                    placeholder="{{ __('Public Key') }}" />
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-6">
                                                                        <div class="input-edits">
                                                                            <div class="form-group">
                                                                                <label for="paystack_secret_key"
                                                                                    class="col-form-label">{{ __('Secret Key') }}</label>
                                                                                <input type="text"
                                                                                    name="paystack_secret_key"
                                                                                    id="paystack_secret_key"
                                                                                    class="form-control"
                                                                                    value="{{ isset($admin_payment_setting['paystack_secret_key']) ? $admin_payment_setting['paystack_secret_key'] : '' }}"
                                                                                    placeholder="{{ __('Secret Key') }}" />
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>--}}

                                                    <!-- Flutterwave -->
                                                    {{--<div class="accordion-item card">
                                                        <h2 class="accordion-header" id="headingFour">
                                                            <button class="accordion-button collapsed" type="button"
                                                                data-bs-toggle="collapse" data-bs-target="#collapseFour"
                                                                aria-expanded="false" aria-controls="collapseFour">
                                                                <span class="d-flex align-items-center">
                                                                    {{ __('Flutterwave') }}
                                                                </span>
                                                                <div class="d-flex align-items-center">
                                                                    <span class="me-2">{{ __('Enable') }}:</span>
                                                                    <div class="form-check form-switch custom-switch-v1">
                                                                        <input type="hidden"
                                                                            name="is_flutterwave_enabled" value="off">
                                                                        <input type="checkbox"
                                                                            class="form-check-input input-primary"
                                                                            id="customswitchv1-1 is_flutterwave_enabled"
                                                                            name="is_flutterwave_enabled"
                                                                            {{ isset($admin_payment_setting['is_flutterwave_enabled']) && $admin_payment_setting['is_flutterwave_enabled'] == 'on' ? 'checked="checked"' : '' }}>
                                                                    </div>
                                                                </div>
                                                            </button>
                                                        </h2>
                                                        <div id="collapseFour" class="accordion-collapse collapse"
                                                            aria-labelledby="headingFour"
                                                            data-bs-parent="#accordionExample">
                                                            <div class="accordion-body">
                                                                <div class="row gy-4">
                                                                    <div class="col-lg-6">
                                                                        <div class="input-edits">
                                                                            <div class="form-group">
                                                                                <label for="paypal_client_id"
                                                                                    class="col-form-label">{{ __('Public Key') }}</label>
                                                                                <input type="text"
                                                                                    name="flutterwave_public_key"
                                                                                    id="flutterwave_public_key"
                                                                                    class="form-control"
                                                                                    value="{{ isset($admin_payment_setting['flutterwave_public_key']) ? $admin_payment_setting['flutterwave_public_key'] : '' }}"
                                                                                    placeholder="Public Key">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-6">
                                                                        <div class="input-edits">
                                                                            <div class="form-group">
                                                                                <label for="paystack_secret_key"
                                                                                    class="col-form-label">{{ __('Secret Key') }}</label>
                                                                                <input type="text"
                                                                                    name="flutterwave_secret_key"
                                                                                    id="flutterwave_secret_key"
                                                                                    class="form-control"
                                                                                    value="{{ isset($admin_payment_setting['flutterwave_secret_key']) ? $admin_payment_setting['flutterwave_secret_key'] : '' }}"
                                                                                    placeholder="Secret Key">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>--}}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <div class="form-group">
                                <button class="btn-submit btn btn-primary" type="submit">
                                    {{ __('Save Changes') }}
                                </button>
                            </div>
                        </div>
                        </form>
                    </div>

                    <!--Pusher Settings-->
                    <div id="pusher-settings" class="card mb-3">
                        <div class="card-header">
                            <h5>{{ __('Pusher Settings') }}</h5>
                        </div>
                        {{ Form::model($settings, ['route' => 'pusher.setting', 'method' => 'post']) }}
                        @csrf
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('pusher_app_id', __('Pusher App Id'), ['class' => 'form-label']) }}
                                        {{ Form::text('pusher_app_id', null, ['class' => 'form-control font-style', 'placeholder'=>__('Pusher App Id')]) }}
                                        @error('pusher_app_id')
                                            <span class="invalid-pusher_app_id" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('pusher_app_key', __('Pusher App Key'), ['class' => 'form-label']) }}
                                        {{ Form::text('pusher_app_key', null, ['class' => 'form-control font-style', 'placeholder'=>__('Pusher App Key')]) }}
                                        @error('pusher_app_key')
                                            <span class="invalid-pusher_app_key" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('pusher_app_secret', __('Pusher App Secret'), ['class' => 'form-label']) }}
                                        {{ Form::text('pusher_app_secret', null, ['class' => 'form-control font-style', 'placeholder'=>__('Pusher App Secret')]) }}
                                        @error('pusher_app_secret')
                                            <span class="invalid-pusher_app_secret" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('pusher_app_cluster', __('Pusher App Cluster'), ['class' => 'form-label']) }}
                                        {{ Form::text('pusher_app_cluster', null, ['class' => 'form-control font-style', 'placeholder'=>__('Pusher App Cluster')]) }}
                                        @error('pusher_app_cluster')
                                            <span class="invalid-pusher_app_cluster" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <div class="form-group">
                                {{--<input class="btn btn-primary" type="submit"
                                    value="{{ __('Save Changes') }}">--}}
                                    <button class="btn-submit btn btn-primary" type="submit">
                                    {{ __('Save Changes') }}
                                </button>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>

                    <!--ReCaptcha Settings-->
                    <div id="recaptcha_settings" class="card mb-3">
                        <form method="POST" action="{{ route('recaptcha.settings.store') }}"
                            accept-charset="UTF-8">
                            @csrf
                            <div class="card-header">
                                <div class="row">
                                    <div class="col-6">
                                        <h5 class="mb-2">{{ __('ReCaptcha Settings') }}</h5>
                                        <a href="https://phppot.com/php/how-to-get-google-recaptcha-site-and-secret-key/"
                                            target="_blank" class="text-primary">
                                            <small>({{ __('How to Get Google reCaptcha Site and Secret key') }})</small>
                                        </a>
                                    </div>
                                    <div class="col switch-width text-end">
                                        <div class="form-group mb-0">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" data-toggle="switchbutton"
                                                    data-onstyle="primary" class="" name="recaptcha_module"
                                                    id="recaptcha_module"
                                                    {{ !empty($setting['recaptcha_module']) && $setting['recaptcha_module'] == 'on' ? 'checked="checked"' : '' }}>
                                                <label class="custom-control-label" for="recaptcha_module"></label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group col switch-width">
                                            {{ Form::label('google_recaptcha_version', __('Google Recaptcha Version'), ['class' => ' col-form-label']) }}

                                            {{ Form::select('google_recaptcha_version', $google_recaptcha_version, isset($setting['google_recaptcha_version']) ? $setting['google_recaptcha_version'] : 'v2-checkbox', ['id' => 'google_recaptcha_version', 'class' => 'form-control choices', 'searchEnabled' => 'true']) }}
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="google_recaptcha_key"
                                                class="form-label">{{ __('Google Recaptcha Key') }}</label>
                                            <input class="form-control"
                                                placeholder="{{ __('Enter Google Recaptcha Key') }}"
                                                name="google_recaptcha_key" type="text"
                                                value="{{ !empty($setting['google_recaptcha_key']) ? $setting['google_recaptcha_key'] : '' }}"
                                                id="google_recaptcha_key" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="google_recaptcha_secret"
                                                class="form-label">{{ __('Google Recaptcha Secret') }}</label>
                                            <input class="form-control"
                                                placeholder="{{ __('Enter Google Recaptcha Secret') }}"
                                                name="google_recaptcha_secret" type="text"
                                                value="{{ !empty($setting['google_recaptcha_secret']) ? $setting['google_recaptcha_secret'] : '' }}"
                                                id="google_recaptcha_secret" required>

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <div class="form-group">
                                    {{--<input class="btn btn-print-invoice btn-primary m-r-10" type="submit"
                                        value="{{ __('Save Changes') }}">--}}
                                        <button class="btn-submit btn btn-primary" type="submit">
                                    {{ __('Save Changes') }}
                                </button>
                                </div>
                            </div>
                            {{ Form::close() }}
                    </div>

                    <!-- Storage Settings -->
                    <div id="storage-settings" class="card mb-3">
                        {{ Form::open(['route' => 'storage.setting.store', 'enctype' => 'multipart/form-data']) }}
                        <div class="card-header">
                            <div class="row">
                                <div class="col-lg-10 col-md-10 col-sm-10">
                                    <h5 class="">{{ __('Storage Settings') }}</h5>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex">
                                <div class="pe-2">
                                    <input type="radio" class="btn-check" name="storage_setting"
                                        id="local-outlined" autocomplete="off"
                                        {{ $setting['storage_setting'] == 'local' ? 'checked' : '' }} value="local"
                                        checked>
                                    <label class="btn btn-outline-primary"
                                        for="local-outlined">{{ __('Local') }}</label>
                                </div>
                                <div class="pe-2">
                                    <input type="radio" class="btn-check" name="storage_setting" id="s3-outlined"
                                        autocomplete="off" {{ $setting['storage_setting'] == 's3' ? 'checked' : '' }}
                                        value="s3">
                                    <label class="btn btn-outline-primary" for="s3-outlined">
                                        {{ __('AWS S3') }}</label>
                                </div>
                                <div class="pe-2">
                                    <input type="radio" class="btn-check" name="storage_setting"
                                        id="wasabi-outlined" autocomplete="off"
                                        {{ $setting['storage_setting'] == 'wasabi' ? 'checked' : '' }} value="wasabi">
                                    <label class="btn btn-outline-primary"
                                        for="wasabi-outlined">{{ __('Wasabi') }}</label>
                                </div>
                            </div>
                            <div class="mt-2">
                                <div class="local-setting row {{ $setting['storage_setting'] == 'local' ? ' ' : 'd-none' }}">
                                    <div class="form-group col-8 switch-width select2-info">
                                        {{ Form::label('local_storage_validation', __('Only Upload Files'), ['class' => ' form-label']) }}
                                        <select name="local_storage_validation[]" class="select2 form-select"
                                            id="local_storage_validation" multiple>
                                            @foreach ($file_type as $f)
                                                <option @if (in_array($f, $local_storage_validations)) selected @endif>
                                                    {{ $f }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label class="form-label"
                                                for="local_storage_max_upload_size">{{ __('Max upload size ( In KB)') }}</label>
                                            <input type="number" name="local_storage_max_upload_size"
                                                class="form-control"
                                                value="{{ !isset($setting['local_storage_max_upload_size']) || is_null($setting['local_storage_max_upload_size']) ? '' : $setting['local_storage_max_upload_size'] }}"
                                                placeholder="{{ __('Max upload size') }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="s3-setting row {{ $setting['storage_setting'] == 's3' ? ' ' : 'd-none' }}">
                                    <div class=" row ">
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label class="form-label" for="s3_key">{{ __('S3 Key') }}</label>
                                                <input type="text" name="s3_key" class="form-control"
                                                    value="{{ !isset($setting['s3_key']) || is_null($setting['s3_key']) ? '' : $setting['s3_key'] }}"
                                                    placeholder="{{ __('S3 Key') }}">
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label class="form-label"
                                                    for="s3_secret">{{ __('S3 Secret') }}</label>
                                                <input type="text" name="s3_secret" class="form-control"
                                                    value="{{ !isset($setting['s3_secret']) || is_null($setting['s3_secret']) ? '' : $setting['s3_secret'] }}"
                                                    placeholder="{{ __('S3 Secret') }}">
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label class="form-label"
                                                    for="s3_region">{{ __('S3 Region') }}</label>
                                                <input type="text" name="s3_region" class="form-control"
                                                    value="{{ !isset($setting['s3_region']) || is_null($setting['s3_region']) ? '' : $setting['s3_region'] }}"
                                                    placeholder="{{ __('S3 Region') }}">
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label class="form-label"
                                                    for="s3_bucket">{{ __('S3 Bucket') }}</label>
                                                <input type="text" name="s3_bucket" class="form-control"
                                                    value="{{ !isset($setting['s3_bucket']) || is_null($setting['s3_bucket']) ? '' : $setting['s3_bucket'] }}"
                                                    placeholder="{{ __('S3 Bucket') }}">
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label class="form-label" for="s3_url">{{ __('S3 URL') }}</label>
                                                <input type="text" name="s3_url" class="form-control"
                                                    value="{{ !isset($setting['s3_url']) || is_null($setting['s3_url']) ? '' : $setting['s3_url'] }}"
                                                    placeholder="{{ __('S3 URL') }}">
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label class="form-label"
                                                    for="s3_endpoint">{{ __('S3 Endpoint') }}</label>
                                                <input type="text" name="s3_endpoint" class="form-control"
                                                    value="{{ !isset($setting['s3_endpoint']) || is_null($setting['s3_endpoint']) ? '' : $setting['s3_endpoint'] }}"
                                                    placeholder="{{ __('S3 Endpoint') }}">
                                            </div>
                                        </div>
                                        <div class="form-group col-8 switch-width select2-info">
                                            {{ Form::label('s3_storage_validation', __('Only Upload Files'), ['class' => ' form-label']) }}
                                            <select name="s3_storage_validation[]" class="select2 form-select"
                                                id="s3_storage_validation" multiple>
                                                @foreach ($file_type as $f)
                                                    <option @if (in_array($f, $s3_storage_validations)) selected @endif>
                                                        {{ $f }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                <label class="form-label"
                                                    for="s3_max_upload_size">{{ __('Max upload size ( In KB)') }}</label>
                                                <input type="number" name="s3_max_upload_size" class="form-control"
                                                    value="{{ !isset($setting['s3_max_upload_size']) || is_null($setting['s3_max_upload_size']) ? '' : $setting['s3_max_upload_size'] }}"
                                                    placeholder="{{ __('Max upload size') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    class="wasabi-setting row {{ $setting['storage_setting'] == 'wasabi' ? ' ' : 'd-none' }}">
                                    <div class=" row ">
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label class="form-label"
                                                    for="s3_key">{{ __('Wasabi Key') }}</label>
                                                <input type="text" name="wasabi_key" class="form-control"
                                                    value="{{ !isset($setting['wasabi_key']) || is_null($setting['wasabi_key']) ? '' : $setting['wasabi_key'] }}"
                                                    placeholder="{{ __('Wasabi Key') }}">
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label class="form-label"
                                                    for="s3_secret">{{ __('Wasabi Secret') }}</label>
                                                <input type="text" name="wasabi_secret" class="form-control"
                                                    value="{{ !isset($setting['wasabi_secret']) || is_null($setting['wasabi_secret']) ? '' : $setting['wasabi_secret'] }}"
                                                    placeholder="{{ __('Wasabi Secret') }}">
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label class="form-label"
                                                    for="s3_region">{{ __('Wasabi Region') }}</label>
                                                <input type="text" name="wasabi_region" class="form-control"
                                                    value="{{ !isset($setting['wasabi_region']) || is_null($setting['wasabi_region']) ? '' : $setting['wasabi_region'] }}"
                                                    placeholder="{{ __('Wasabi Region') }}">
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label class="form-label"
                                                    for="wasabi_bucket">{{ __('Wasabi Bucket') }}</label>
                                                <input type="text" name="wasabi_bucket" class="form-control"
                                                    value="{{ !isset($setting['wasabi_bucket']) || is_null($setting['wasabi_bucket']) ? '' : $setting['wasabi_bucket'] }}"
                                                    placeholder="{{ __('Wasabi Bucket') }}">
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label class="form-label"
                                                    for="wasabi_url">{{ __('Wasabi URL') }}</label>
                                                <input type="text" name="wasabi_url" class="form-control"
                                                    value="{{ !isset($setting['wasabi_url']) || is_null($setting['wasabi_url']) ? '' : $setting['wasabi_url'] }}"
                                                    placeholder="{{ __('Wasabi URL') }}">
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label class="form-label"
                                                    for="wasabi_root">{{ __('Wasabi Root') }}</label>
                                                <input type="text" name="wasabi_root" class="form-control"
                                                    value="{{ !isset($setting['wasabi_root']) || is_null($setting['wasabi_root']) ? '' : $setting['wasabi_root'] }}"
                                                    placeholder="{{ __('Wasabi Root') }}">
                                            </div>
                                        </div>
                                        <div class="form-group col-8 switch-width select2-info">
                                            {{ Form::label('wasabi_storage_validation', __('Only Upload Files'), ['class' => 'form-label']) }}

                                            <select name="wasabi_storage_validation[]" class="select2 form-select"
                                                id="wasabi_storage_validation" multiple>
                                                @foreach ($file_type as $f)
                                                    <option @if (in_array($f, $wasabi_storage_validations)) selected @endif>
                                                        {{ $f }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                <label class="form-label"
                                                    for="wasabi_root">{{ __('Max upload size ( In KB)') }}</label>
                                                <input type="number" name="wasabi_max_upload_size"
                                                    class="form-control"
                                                    value="{{ !isset($setting['wasabi_max_upload_size']) || is_null($setting['wasabi_max_upload_size']) ? '' : $setting['wasabi_max_upload_size'] }}"
                                                    placeholder="{{ __('Max upload size') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                           {{--<input class="btn btn-print-invoice  btn-primary m-r-10" type="submit"
                                value="{{ __('Save Changes') }}">--}}
                                <button class="btn-submit btn btn-primary" type="submit">
                                    {{ __('Save Changes') }}
                                </button>
                        </div>
                        {{ Form::close() }}
                    </div>

                    <!-- {{-- SEO settings --}}
                    <div id="seo-settings" class="card mb-3">
                        <div class="card-header d-flex justify-content-between">
                            <h5>{{ __('SEO Settings') }}</h5>
                            @php
                                $settings = \App\Models\Utility::settings();
                            @endphp
                            @if (!empty($settings['chat_gpt_key']))
                                <div class="d-flex justify-content-end">
                                    <div class="mt-0">
                                        <a data-size="md" class="btn btn-primary text-white btn-sm"
                                            data-ajax-popup-over="true" data-url="{{ route('generate', ['seo']) }}"
                                            data-bs-placement="top" data-title="{{ __('Generate content with AI') }}">
                                            <i class="fas fa-robot"></i> <span>{{ __('Generate with AI') }}</span>
                                        </a>
                                    </div>
                                </div>
                            @endif
                        </div>
                        {{ Form::open(['url' => route('seo.settings.store'), 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
                        @csrf
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('Meta Keywords', __('Meta Keywords'), ['class' => 'col-form-label']) }}
                                        {{ Form::text('meta_title', !empty($setting['meta_title']) ? $setting['meta_title'] : '', ['class' => 'form-control ', 'placeholder' => 'Meta Keywords']) }}
                                    </div>
                                    <div class="form-group">
                                        {{ Form::label('Meta Description', __('Meta Description'), ['class' => 'col-form-label']) }}
                                        {{ Form::textarea('meta_desc', !empty($setting['meta_desc']) ? $setting['meta_desc'] : '', ['class' => 'form-control ', 'placeholder' => 'Meta Description', 'rows' => 7]) }}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-0">
                                        {{ Form::label('Meta Image', __('Meta Image'), ['class' => 'col-form-label']) }}
                                    </div>
                                    <div class="setting-card">
                                        <div class="logo-content">
                                            <img id="image2"
                                                src="{{ $meta_image . '/' . (isset($setting['meta_image']) && !empty($setting['meta_image']) ? $setting['meta_image'] : 'default.png') }}"
                                                class="img_setting seo_image">
                                        </div>
                                        <div class="choose-files mt-4">
                                            <label for="meta_image">
                                                <div class="bg-primary company_favicon_update"> <i
                                                        class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                                                </div>
                                                <input type="file" class="form-control file" id="meta_image"
                                                    name="meta_image" data-filename="meta_image">
                                            </label>
                                        </div>
                                        @error('meta_image')
                                            <div class="row">
                                                <span class="invalid-logo" role="alert">
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                </span>
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <input class="btn btn-print-invoice btn-primary m-r-10" type="submit"
                                value="{{ __('Save Changes') }}">
                        </div>
                        {{ Form::close() }}
                    </div> -->

                    {{-- Cookie settings --}}
                    <div class="card mb-3" id="cookie-settings">

                        {{ Form::model($settings, ['route' => 'cookie.setting', 'method' => 'post']) }}
                        <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                            <h5 class="mb-0">{{ __('Cookie Settings') }}</h5>
                            
                            <div class="d-flex align-items-center flex-nowrap">
                                {{ Form::label('enable_cookie', __('Enable cookie'), ['class' => 'col-form-label p-0 fw-bold me-3 mb-0']) }}
                                <div style="display: flex; align-items: center;"> 
                                    <label class="switch switch-square switch-lg switch-success me-2 mb-0" style="margin-top: 0;">
                                        <input type="checkbox" class="switch-input" name="enable_cookie"
                                            id="enable_cookie" {{ $settings['enable_cookie'] == 'on' ? 'checked' : '' }}>
                                        <span class="switch-toggle-slider">
                                            <span class="switch-on">ON</span>
                                            <span class="switch-off">OFF</span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div
                            class="card-body cookieDiv {{ $settings['enable_cookie'] == 'off' ? 'disabledCookie ' : '' }}">
                            @php
                                $settings = \App\Models\Utility::settings();
                            @endphp
                            <div class="row">
                                <div class="text-end">
                                    @if (!empty($settings['chat_gpt_key']))
                                        <div class="mt-0">
                                            <a data-size="md" class="btn btn-primary text-white btn-sm"
                                                data-ajax-popup-over="true"
                                                data-url="{{ route('generate', ['cookie']) }}" data-bs-placement="top"
                                                data-title="{{ __('Generate content with AI') }}">
                                                <i class="fas fa-robot"></i> <span>{{ __('Generate with AI') }}</span>
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="row ">
                                <div class="col-md-6">
                                    <div class="form-check form-switch custom-switch-v1" id="cookie_log">
                                        <input type="checkbox" name="cookie_logging"
                                            class="form-check-input input-primary cookie_setting" id="cookie_logging"
                                            {{ $settings['cookie_logging'] == 'on' ? ' checked ' : '' }}>
                                        <label class="form-check-label"
                                            for="cookie_logging">{{ __('Enable logging') }}</label>
                                    </div>
                                    <div class="form-group">
                                        {{ Form::label('cookie_title', __('Cookie Title'), ['class' => 'col-form-label']) }}
                                        {{ Form::text('cookie_title', null, ['class' => 'form-control cookie_setting', 'placeholder'=>__('Cookie Title')]) }}
                                    </div>
                                    <div class="form-group ">
                                        {{ Form::label('cookie_description', __('Cookie Description'), ['class' => ' form-label']) }}
                                        {!! Form::textarea('cookie_description', null, ['class' => 'form-control cookie_setting', 'rows' => '3', 'placeholder'=>__('Cookie Description')]) !!}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch custom-switch-v1 ">
                                        <input type="checkbox" name="necessary_cookies"
                                            class="form-check-input input-primary" id="necessary_cookies" checked
                                            onclick="return false">
                                        <label class="form-check-label"
                                            for="necessary_cookies">{{ __('Strictly necessary cookies') }}</label>
                                    </div>
                                    <div class="form-group ">
                                        {{ Form::label('strictly_cookie_title', __(' Strictly Cookie Title'), ['class' => 'col-form-label']) }}
                                        {{ Form::text('strictly_cookie_title', null, ['class' => 'form-control cookie_setting', 'placeholder'=>__('Strictly Cookie Title')]) }}
                                    </div>
                                    <div class="form-group ">
                                        {{ Form::label('strictly_cookie_description', __('Strictly Cookie Description'), ['class' => ' form-label']) }}
                                        {!! Form::textarea('strictly_cookie_description', null, [
                                            'class' => 'form-control cookie_setting ',
                                            'rows' => '3', 'placeholder'=>__('Strivtly Cookie Title')
                                        ]) !!}
                                    </div>
                                </div>
                                <div class="col-12">
                                    <h5>{{ __('More Information') }}</h5>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('more_information_description', __('Contact Us Description'), ['class' => 'col-form-label']) }}
                                        {{ Form::text('more_information_description', null, ['class' => 'form-control cookie_setting', 'placeholder'=>__('Contact Us Description')]) }}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group ">
                                        {{ Form::label('contactus_url', __('Contact Us URL'), ['class' => 'col-form-label']) }}
                                        {{ Form::text('contactus_url', null, ['class' => 'form-control cookie_setting', 'placeholder'=>__('Contact Us URL')]) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer mb-3">
                            <div class="row">
                                <div class="col-6">
                                    @if (isset($settings['cookie_logging']) && $settings['cookie_logging'] == 'on')
                                        <label for="file"
                                            class="form-label">{{ __('Download cookie accepted data') }}</label>
                                        <a href="{{ asset(Storage::url('uploads/sample')) . '/data.csv' }}"
                                            class="btn btn-primary mr-3" download="">
                                            <i class="ti ti-download"></i>
                                        </a>
                                    @endif
                                </div>
                                <div class="col-6 text-end ">
                                    {{--<input class="btn btn-print-invoice btn-primary cookie_btn" type="submit"
                                        value="{{ __('Save Changes') }}">--}}
                                        <button class="btn-submit btn btn-primary" type="submit">
                                    {{ __('Save Changes') }}
                                </button>
                                </div>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>

                    {{-- Cache settings --}}
                    <div class="card mb-3" id="cache-settings">
                        <div class="card-header">
                            <h5>{{ 'Cache Settings' }}</h5>
                            <small class="text-secondary font-weight-bold">
                                {{ __("This is a page meant for more advanced users, simply ignore it if you don't understand what cache is.") }}
                            </small>
                        </div>
                        <hr>
                        <form method="POST" action="{{ route('cache.settings.store') }}" accept-charset="UTF-8">
                            @csrf
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12 form-group">
                                        {{ Form::label('Current cache size', __('Current cache size'), ['class' => 'col-form-label']) }}
                                        <div class="input-group mb-5">
                                            <input type="text" class="form-control" value="{{ $file_size }}"
                                                readonly  aria-describedby="basic-addon13">
                                                <span class="input-group-text" id="basic-addon13">{{ __('MB') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="card-footer text-end">
                                {{--<input class="btn btn-print-invoice btn-primary m-r-10" type="submit"
                                    value="{{ __('Cache Clear') }}">--}}
                                    <button class="btn-submit btn btn-primary" type="submit">
                                    {{ __('Clear Cache') }}
                                </button>
                            </div>
                            {{ Form::close() }}
                    </div>

                    <!-- {{-- chat gpt settings --}}
                    <div id="chat-gpt-settings" class="card mb-3">
                        <div class="card-header">
                            <h5>{{ __('Chat GPT Settings') }}</h5>
                        </div>
                        {{ Form::model($settings, ['route' => 'chatgpt.settings', 'method' => 'post']) }}
                        <div class="card-body">
                            <div class="row">
                                <div class="form-group col-md-12">
                                    {{ Form::label('chat_gpt_key', __('Chat GPT key'), ['class' => 'col-form-label']) }}
                                    {{ Form::text('chat_gpt_key', isset($settings['chat_gpt_key']) ? $settings['chat_gpt_key'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Chat GPT API Key'), 'required' => 'required']) }}
                                </div>
                                <div class="form-group col-md-12">
                                    {{ Form::label('chat_gpt_model', __('Chat GPT Model Name'), ['class' => 'col-form-label']) }}
                                    {{ Form::text('chat_gpt_model', isset($settings['chat_gpt_model']) ? $settings['chat_gpt_model'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Chat GPT Modal Name'), 'required' => 'required']) }}
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <input class="btn btn-print-invoice btn-primary m-r-10" type="submit"
                                value="{{ __('Save Changes') }}">
                        </div>
                        {{ Form::close() }}
                    </div> -->
                    {{--  End for all settings tab --}}

                </div>
            </div>
        </div>
    </div>
@endsection
