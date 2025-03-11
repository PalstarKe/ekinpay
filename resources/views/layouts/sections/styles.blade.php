<!-- BEGIN: Theme CSS-->
<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">


@vite([
  'resources/assets/vendor/libs/select2/select2.scss',
  'resources/assets/vendor/libs/tagify/tagify.scss',
  'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss',
  'resources/assets/vendor/fonts/tabler-icons.scss',
  'resources/assets/vendor/fonts/fontawesome.scss',
  'resources/assets/vendor/fonts/flag-icons.scss',
  'resources/assets/vendor/libs/node-waves/node-waves.scss',
  'resources/assets/vendor/libs/animate-css/animate.scss',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
  'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
  'resources/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.scss',
  'resources/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.scss',
  'resources/assets/vendor/libs/jquery-timepicker/jquery-timepicker.scss',
  'resources/assets/vendor/libs/pickr/pickr-themes.scss',
  'resources/assets/vendor/libs/toastr/toastr.scss'
])
<!-- Core CSS -->
@vite(['resources/assets/vendor/scss'.$configData['rtlSupport'].'/core' .($configData['style'] !== 'light' ? '-' . $configData['style'] : '') .'.scss',
'resources/assets/vendor/scss'.$configData['rtlSupport'].'/' .$configData['theme'] .($configData['style'] !== 'light' ? '-' . $configData['style'] : '') .'.scss',
'resources/assets/css/demo.css'
])
@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/moment/moment.js',
  'resources/assets/vendor/libs/flatpickr/flatpickr.js',
  'resources/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js',
  'resources/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js',
  'resources/assets/vendor/libs/jquery-timepicker/jquery-timepicker.js',
  'resources/assets/vendor/libs/pickr/pickr.js'
])
@endsection
<!-- Flatpickr CSS -->

@section('page-script')
@vite(['resources/assets/js/forms-pickers.js'])
@endsection
<!-- Vendor Styles -->
@vite([
  'resources/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.scss',
  'resources/assets/vendor/libs/typeahead-js/typeahead.scss'
])
@yield('vendor-style')
<link rel="stylesheet" href="{{ asset('assets/css/plugins/main.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/plugins/style.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/plugins/flatpickr.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/plugins/animate.min.css') }}">
{{--<link rel="stylesheet" href="{{ asset('css/custom.css') }}">--}}
<link rel="stylesheet" href="{{ asset('assets/css/plugins/bootstrap-switch-button.min.css') }}">
<style>
.choose-files div {
    color: #fff;
    background: #584ED2 !important;
    border: none;
    border-radius: 4px;
    padding: 8px 15px;
    max-width: 155px !important;
    font-size: 12px;
    font-weight: 500;
}
.file {
    position: relative !important;
    left: 0;
    opacity: 0;
    top: 0;
    bottom: 0;
    width: 80%;
    border: none;
    padding: 0;
    margin: 0;
    cursor: pointer;
}
.file-icon {
    width: 30px;
    height: 30px;
    background: #0F5EF7;
    border-radius: 50px;
    float: left;
    text-align: center;
}
.file-icon i {
    color: #fff;
    z-index: 9999;
    position: relative;
    font-size: 14px;
}
.first-file {
    width: 100%;
    float: left;
    padding-bottom: 20px;
    position: relative;
}
.file-des {
    width: calc(100% - 40px);
    float: right;
    color: #A3AFBB;
    font-size: 12px;
}
.file-des span {
    width: 100%;
    float: left;
    color: #011C4B;
}
.general-tab .column-card {
    flex-direction: column;
}
.first-file:before {
    position: absolute;
    bottom: 0;
    width: 3px;
    height: 100%;
    background: var(--bs-primary) !important;
    content: "";
    left: 25px;
}
.first-file:last-child:before {
    background: none;
}
/* .setting-favimg{
    width: 100px;
}
.setting-logoimg{
    width: 200px;
} */
.colorinput {
    margin: 0;
    position: relative;
    cursor: pointer;
}

.colorinput-input {
    position: absolute;
    z-index: -1;
    opacity: 0;
}

.colorinput-color {
    background-color: #fdfdff;
    border-color: #e4e6fc;
    border-width: 1px;
    border-style: solid;
    display: inline-block;
    width: 1.75rem;
    height: 1.75rem;
    border-radius: 3px;
    color: #fff;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

.colorinput-color:before {
    content: '';
    opacity: 0;
    position: absolute;
    top: .25rem;
    left: .25rem;
    height: 1.25rem;
    width: 1.25rem;
    transition: .3s opacity;
    background: url("data:image/svg+xml;charset=utf8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3E%3Cpath fill='%23fff' d='M6.564.75l-3.59 3.612-1.538-1.55L0 4.26 2.974 7.25 8 2.193z'/%3E%3C/svg%3E") no-repeat center center/50% 50%;
}

.colorinput-input:checked~.colorinput-color:before {
    opacity: 1;
}

.img_setting {
    filter: drop-shadow(2px 3px 7px #011C4B);
}
.tab-btns {
    min-width: 100px;
    white-space: nowrap;
    border-radius: 0.625rem!important;
    padding: 10px 20px;
    font-size: 12px;
}
.custom_messanger_counter {
    position: relative;
    background: none;
    top: -15px;
    left: -5px;
}
.email_temp{
    height: 450px !important;
    overflow-y: scroll;
}
.emp_details{
    min-height: 420px !important;
}


/*start for input search*/
.searchBoxElement{
    background-color: white;
    border: 1px solid #aaa;
    position: absolute;
    max-height: 150px;
    overflow-x: hidden;
    overflow-y: auto;
    margin: 0;
    padding: 0;
    line-height: 23px;
    list-style: none;
    z-index: 1;
    -ms-overflow-style: none;
    scrollbar-width: none;
}


.searchBoxElement span{
    padding: 0 5px;
}


.searchBoxElement li{
    background-color: white;
    color: black;
}

.searchBoxElement li:hover{
    background-color: #50a0ff;
    color: white;
}

.searchBoxElement li.selected{
    background-color: #50a0ff;
    color: white;
}
</style>

<!-- Page Styles -->
@yield('page-style')
