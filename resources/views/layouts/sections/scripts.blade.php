<!-- BEGIN: Vendor JS-->
@php
    use App\Models\Utility;
    $setting = \App\Models\Utility::settings();
    $setting_arr = Utility::file_validate();
@endphp
@vite([
  'resources/assets/vendor/libs/jquery/jquery.js',
  'resources/assets/vendor/libs/popper/popper.js',
  'resources/assets/vendor/js/bootstrap.js',
  'resources/assets/vendor/libs/node-waves/node-waves.js',
  'resources/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js',
  'resources/assets/vendor/libs/hammer/hammer.js',
  'resources/assets/vendor/libs/typeahead-js/typeahead.js',
  'resources/assets/vendor/js/menu.js',
  'resources/assets/vendor/libs/select2/select2.js',
  'resources/assets/vendor/libs/tagify/tagify.js',
  'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js',
  'resources/assets/vendor/libs/typeahead-js/typeahead.js',
  'resources/assets/vendor/libs/bloodhound/bloodhound.js',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
  'resources/assets/js/extended-ui-sweetalert2.js',
  'resources/assets/vendor/libs/clipboard/clipboard.js',
  'resources/assets/vendor/libs/toastr/toastr.js',
  'resources/assets/js/extended-ui-misc-clipboardjs.js',
  'resources/assets/vendor/libs/flatpickr/flatpickr.js'

])
@yield('vendor-script')
<!-- END: Page Vendor JS-->
<!-- BEGIN: Theme JS-->
@vite([
    'resources/assets/js/main.js'
    ])
<!-- END: Theme JS-->
<!-- Pricing Modal JS-->
@stack('pricing-script')

<!-- END: Pricing Modal JS-->
<!-- BEGIN: Page JS-->
@yield('page-script')
<!-- END: Page JS-->
<!-- Flatpickr JS -->
<script src="{{ asset('js/jquery.min.js') }}"></script>
<script src="{{ asset('js/jquery.form.js') }}"></script>
<script src="{{ asset('assets/js/plugins/simplebar.min.js') }}"></script>

<script src="{{ asset('assets/js/plugins/feather.min.js') }}"></script>
<script src="{{ asset('assets/js/dash.js') }}"></script>
<script src="{{ asset('js/moment.min.js') }}"></script>

<script src="{{ asset('assets/js/plugins/bootstrap-switch-button.min.js') }}"></script>

<script src="{{ asset('assets/js/plugins/sweetalert2.all.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/simple-datatables.js') }}"></script>

<!-- Apex Chart -->
<script src="{{ asset('assets/js/plugins/apexcharts.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/main.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/choices.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/flatpickr.min.js') }}"></script>

<script src="{{ asset('js/jscolor.js') }}"></script>

<script src="{{ asset('js/popper.min.js') }}"></script>
<script src="assets/js/custom.js"></script>
<script src="js/cookieconsent.js"></script>

{{--@if($message = Session::get('success'))
    <script>
        show_toastr('success', '{!! $message !!}');
    </script>
@endif
@if($message = Session::get('error'))
    <script>
        show_toastr('error', '{!! $message !!}');
    </script>
@endif--}}

@if(Session::has('success'))
    <script>
        show_toastr('success', {!! json_encode(Session::get('success')) !!});
    </script>
@endif

@if(Session::has('error'))
    <script>
        show_toastr('error', {!! json_encode(Session::get('error')) !!});
    </script>
@endif

@if($setting['enable_cookie'] == 'on')
    @include('layouts.cookie_consent')
@endif
@stack('script-page')
