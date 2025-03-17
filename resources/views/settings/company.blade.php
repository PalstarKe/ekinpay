@extends('layouts/layoutMaster')
@section('page-title')
    {{ __('Settings') }}
@endsection
@php
    use App\Models\Utility;
    use App\Models\WebhookSetting;
    $logo = \App\Models\Utility::get_file('uploads/logo');

    $logo_light = !empty($setting['company_logo_light']) ? $setting['company_logo_light'] : '';
    $logo_dark = !empty($setting['company_logo_dark']) ? $setting['company_logo_dark'] : '';
    $company_favicon = !empty($setting['company_favicon']) ? $setting['company_favicon'] : '';

    $color = !empty($setting['color']) ? $setting['color'] : 'theme-3';
    $SITE_RTL = isset($setting['SITE_RTL']) ? $setting['SITE_RTL'] : 'off';

    $currantLang = Utility::languages();
    $lang = \App\Models\Utility::getValByName('default_language');
    $webhookSetting = WebhookSetting::where('created_by', '=', \Auth::user()->creatorId())->get();

@endphp

@push('css-page')
    <link rel="stylesheet" href="{{ asset('css/summernote/summernote-bs4.css') }}">
@endpush

@push('script-page')
    <script src="{{ asset('css/summernote/summernote-bs4.js') }}"></script>
    <script>
 
        $('.summernote-simple3').on('summernote.blur', function() {
            $.ajax({
                url: "{{ route('noc.update', $noclang) }}",
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    content: $(this).val()
                },
                type: 'POST',
                success: function(response) {
                    console.log(response)
                    if (response.is_success) {
                        show_toastr('success', response.success, 'success');
                    } else {
                        show_toastr('error', response.error, 'error');
                    }
                },
                error: function(response) {

                    response = response.responseJSON;
                    if (response.is_success) {
                        show_toastr('error', response.error, 'error');
                    } else {
                        show_toastr('error', response.error, 'error');
                    }
                }
            })
        });

        //footer notes
        $('.summernote-simple4').on('summernote.blur', function() {

            $.ajax({
                url: "{{ route('system.settings.footernote') }}",
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    notes: $(this).val()
                },
                type: 'POST',
                success: function(response) {
                    if (response.is_success) {
                        // show_toastr('Success', response.success,'success');
                    } else {
                        show_toastr('error', response.error, 'error');
                    }
                },
                error: function(response) {
                    response = response.responseJSON;
                    if (response.is_success) {
                        show_toastr('error', response.error, 'error');
                    } else {
                        show_toastr('error', response, 'error');
                    }
                }
            })
        });
    </script>

    <script>
        $(document).on("change", "select[name='invoice_template'], input[name='invoice_color']", function() {
            var template = $("select[name='invoice_template']").val();
            var color = $("input[name='invoice_color']:checked").val();
            $('#invoice_frame').attr('src', '{{ url('/invoices/preview') }}/' + template + '/' + color);
        });
    </script>

    <script>
        $(document).on('change', '#vat_gst_number_switch', function() {
            if ($(this).is(':checked')) {
                $('.tax_type_div').removeClass('d-none');
            } else {
                $('.tax_type_div').addClass('d-none');
            }
        });
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

    <script>
        $(document).on('keyup change', '.currency_preview', function() {
            var data = $('#currency_setting').serialize();
            $.ajax({
                type: 'POST',
                url: '{{ route('currency.preview') }}',
                data: data,
                success: function(price) {
                    $('.preview').text(price);
                }
            });
        });
    </script>
@endpush
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="row">
                <div class="col-xl-3">
                    <div class="card sticky-top" style="top:30px; z-index:unset;">
                        <div class="list-group list-group-flush" id="useradd-sidenav">
                            <a href="#system-settings"
                                class="list-group-item list-group-item-action border-0">{{ __('System Settings') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a>
                            <a href="#company-settings"
                                class="list-group-item list-group-item-action border-0">{{ __('Company Settings') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a>
                            <a href="#currency-settings"
                                class="list-group-item list-group-item-action border-0">{{ __('Currency Settings') }}
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
                            {{--<a href="#zoom-settings"
                                class="list-group-item list-group-item-action border-0">{{ __('Zoom Settings') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a>
                            <a href="#slack-settings"
                                class="list-group-item list-group-item-action border-0">{{ __('Slack Settings') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a>
                            <a href="#telegram-settings"
                                class="list-group-item list-group-item-action border-0">{{ __('Telegram Settings') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a>--}}
                            <a href="#sms-settings"
                                class="list-group-item list-group-item-action border-0">{{ __('SMS Settings') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a>
                            {{--<a href="#email-notification-settings"
                                class="list-group-item list-group-item-action border-0">{{ __('Email Settings') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a>--}}
                            <a href="#whatsapp-settings"
                                class="list-group-item list-group-item-action border-0">{{ __('Whatsapp Settings') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a>
                            {{--<a href="#noc-settings"
                                class="list-group-item list-group-item-action border-0">{{ __('NOC Settings') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a>
                            <a href="#google-calender"
                                class="list-group-item list-group-item-action border-0">{{ __('Google Calendar Settings') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a>--}}
                            <a href="#webhook-settings"
                                class="list-group-item list-group-item-action border-0">{{ __('Webhook Settings') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a>
                            <a href="#ip-restriction-settings"
                                class="list-group-item list-group-item-action border-0">{{ __('IP Restriction Settings') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-xl-9">
                    <!--System Settings-->
                    <div id="system-settings" class="card mb-3">
                        <div class="card-header">
                            <h5>{{ __('System Settings') }}</h5>
                            <small class="text-muted">{{ __('Edit your system details') }}</small>
                        </div>
                        {{ Form::model($setting, ['route' => 'system.settings', 'method' => 'post']) }}
                        <div class="card-body">
                            <div class="row">
                                {{-- <div class="form-group col-md-6">
                                    {{ Form::label('site_currency', __('Currency *'), ['class' => 'form-label']) }}
                                    {{ Form::text('site_currency', $setting['site_currency'], ['class' => 'form-control font-style', 'required', 'placeholder' => __('Enter Currency')]) }}
                                    <small> {{ __('Note: Add currency code as per three-letter ISO code.') }}<br>
                                        <a href="https://stripe.com/docs/currencies"
                                            target="_blank">{{ __('You can find out how to do that here.') }}</a></small>
                                    <br>
                                    @error('site_currency')
                                        <span class="invalid-site_currency" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    {{ Form::label('site_currency_symbol', __('Currency Symbol *'), ['class' => 'form-label']) }}
                                    {{ Form::text('site_currency_symbol', null, ['class' => 'form-control']) }}
                                    @error('site_currency_symbol')
                                        <span class="invalid-site_currency_symbol" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label class="form-label"
                                        for="example3cols3Input">{{ __('Currency Symbol Position') }}</label>
                                    <div class="row ms-1">
                                        <div class="form-check col-md-6">
                                            <input class="form-check-input" type="radio"
                                                name="site_currency_symbol_position" value="pre"
                                                @if (@$setting['site_currency_symbol_position'] == 'pre') checked @endif id="flexCheckDefault">
                                            <label class="form-check-label" for="flexCheckDefault">
                                                {{ __('Pre') }}
                                            </label>
                                        </div>
                                        <div class="form-check col-md-6">
                                            <input class="form-check-input" type="radio"
                                                name="site_currency_symbol_position" value="post"
                                                @if (@$setting['site_currency_symbol_position'] == 'post') checked @endif id="flexCheckChecked">
                                            <label class="form-check-label" for="flexCheckChecked">
                                                {{ __('Post') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-md-6">
                                    {{ Form::label('decimal_number', __('Decimal Number Format'), ['class' => 'form-label']) }}
                                    {{ Form::number('decimal_number', null, ['class' => 'form-control']) }}
                                    @error('decimal_number')
                                        <span class="invalid-decimal_number" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div> --}}
                                <div class="form-group col-md-6">
                                    <label for="site_date_format" class="form-label">{{ __('Date Format') }}</label>
                                    <select type="text" name="site_date_format" class="form-control selectric"
                                        id="site_date_format">
                                        <option value="M j, Y"
                                            @if (@$setting['site_date_format'] == 'M j, Y') selected="selected" @endif>Jan 1,2015</option>
                                        <option value="d-m-Y"
                                            @if (@$setting['site_date_format'] == 'd-m-Y') selected="selected" @endif>dd-mm-yyyy</option>
                                        <option value="m-d-Y"
                                            @if (@$setting['site_date_format'] == 'm-d-Y') selected="selected" @endif>mm-dd-yyyy</option>
                                        <option value="Y-m-d"
                                            @if (@$setting['site_date_format'] == 'Y-m-d') selected="selected" @endif>yyyy-mm-dd</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="site_time_format" class="form-label">{{ __('Time Format') }}</label>
                                    <select type="text" name="site_time_format" class="form-control selectric"
                                        id="site_time_format">
                                        <option value="g:i A"
                                            @if (@$setting['site_time_format'] == 'g:i A') selected="selected" @endif>10:30 PM</option>
                                        <option value="g:i a"
                                            @if (@$setting['site_time_format'] == 'g:i a') selected="selected" @endif>10:30 pm</option>
                                        <option value="H:i"
                                            @if (@$setting['site_time_format'] == 'H:i') selected="selected" @endif>22:30</option>
                                    </select>
                                </div>

                                <div class="form-group col-md-6">
                                    {{ Form::label('customer_prefix', __('Customer Prefix'), ['class' => 'form-label']) }}
                                    {{ Form::text('customer_prefix', null, ['class' => 'form-control', 'placeholder' => __('Enter Customer Prefix')]) }}
                                    @error('customer_prefix')
                                        <span class="invalid-customer_prefix" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                {{--<div class="form-group col-md-6">
                                    {{ Form::label('vender_prefix', __('Vendor Prefix'), ['class' => 'form-label']) }}
                                    {{ Form::text('vender_prefix', null, ['class' => 'form-control', 'placeholder' => __('Enter Vendor Prifix')]) }}
                                    @error('vender_prefix')
                                        <span class="invalid-vender_prefix" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>--}}
                                {{--<div class="form-group col-md-6">
                                    {{ Form::label('proposal_prefix', __('Proposal Prefix'), ['class' => 'form-label']) }}
                                    {{ Form::text('proposal_prefix', null, ['class' => 'form-control', 'placeholder' => __('Enter Proposal Prifix')]) }}
                                    @error('proposal_prefix')
                                        <span class="invalid-proposal_prefix" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>--}}
                                <div class="form-group col-md-6">
                                    {{ Form::label('invoice_prefix', __('Invoice Prefix'), ['class' => 'form-label']) }}
                                    {{ Form::text('invoice_prefix', null, ['class' => 'form-control', 'placeholder' => __('Enter Invoice Prifix')]) }}
                                    @error('invoice_prefix')
                                        <span class="invalid-invoice_prefix" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    {{ Form::label('bill_prefix', __('Bill Prefix'), ['class' => 'form-label']) }}
                                    {{ Form::text('bill_prefix', null, ['class' => 'form-control', 'placeholder' => __('Enter Bill Prifix')]) }}
                                    @error('bill_prefix')
                                        <span class="invalid-bill_prefix" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                {{--<div class="form-group col-md-6">
                                    {{ Form::label('quotation_prefix', __('Quotation Prefix'), ['class' => 'form-label']) }}
                                    {{ Form::text('quotation_prefix', null, ['class' => 'form-control', 'placeholder' => __('Enter Quotation Prifix')]) }}
                                    @error('quotation_prefix')
                                        <span class="invalid-quotation_prefix" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    {{ Form::label('purchase_prefix', __('Purchase Prefix'), ['class' => 'form-label']) }}
                                    {{ Form::text('purchase_prefix', null, ['class' => 'form-control', 'placeholder' => __('Enter Purchase Prifix')]) }}
                                    @error('purchase_prefix')
                                        <span class="invalid-purchase_prefix" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    {{ Form::label('pos_prefix', __('Pos Prefix'), ['class' => 'form-label']) }}
                                    {{ Form::text('pos_prefix', null, ['class' => 'form-control', 'placeholder' => __('Enter Pos Prifix')]) }}
                                    @error('pos_prefix')
                                        <span class="invalid-pos_prefix" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    {{ Form::label('journal_prefix', __('Journal Prefix'), ['class' => 'form-label']) }}
                                    {{ Form::text('journal_prefix', null, ['class' => 'form-control', 'placeholder' => __('Enter Journal Prifix')]) }}
                                    @error('journal_prefix')
                                        <span class="invalid-journal_prefix" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>--}}
                                <div class="form-group col-md-6">
                                    {{ Form::label('expense_prefix', __('Expense Prefix'), ['class' => 'form-label']) }}
                                    {{ Form::text('expense_prefix', null, ['class' => 'form-control', 'placeholder' => __('Enter Expense Prifix')]) }}
                                    @error('expense_prefix')
                                        <span class="invalid-expense_prefix" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                {{--<div class="form-group col-md-6">
                                    {{ Form::label('shipping_display', __('Display Shipping in Proposal / Invoice / Bill'), ['class' => 'form-label']) }}
                                    <div class=" form-switch form-switch-left">
                                        <input type="checkbox" class="form-check-input mt-3" name="shipping_display"
                                            id="email_tempalte_13"
                                            {{ isset($setting['shipping_display']) && $setting['shipping_display'] == 'on' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="email_tempalte_13"></label>
                                    </div>
                                    @error('shipping_display')
                                        <span class="invalid-shipping_display" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>--}}
                                <div class="form-group col-md-12">
                                    {{ Form::label('footer_title', __('Invoice/Bill Footer Title'), ['class' => 'form-label']) }}
                                    {{ Form::text('footer_title', null, ['class' => 'form-control', 'placeholder' => __('Enter Footer Title')]) }}
                                    @error('footer_title')
                                        <span class="invalid-footer_title" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                {{--<div class="form-group col-md-12">
                                    {{ Form::label('footer_notes', __('Proposal/Invoice/Bill/Purchase/POS Footer Note'), ['class' => 'form-label']) }}
                                    <textarea class="summernote-simple4 summernote-simple">{!! isset($setting['footer_notes']) ? $setting['footer_notes'] : '' !!}</textarea>
                                </div>--}}
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <div class="form-group">
                                <button class="btn btn-print-invoice btn-primary m-r-10" type="submit">{{ __('Save Changes') }}</button>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>

                    <!--Company Settings-->
                    <div id="company-settings" class="card mb-3">
                        <div class="card-header">
                            <h5>{{ __('Company Settings') }}</h5>
                            <small class="text-muted">{{ __('Edit your company details') }}</small>
                        </div>
                        {{ Form::model($setting, ['route' => 'company.settings', 'method' => 'post']) }}
                        <div class="card-body">
                            <div class="row">
                                <div class="form-group col-md-6">
                                    {{ Form::label('company_name', __('Company Name *'), ['class' => 'form-label']) }}
                                    {{ Form::text('company_name', null, ['class' => 'form-control font-style', 'placeholder' => __('Enter Company Name')]) }}
                                    @error('company_name')
                                        <span class="invalid-company_name" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    {{ Form::label('company_address', __('Address'), ['class' => 'form-label']) }}
                                    {{ Form::text('company_address', null, ['class' => 'form-control font-style', 'placeholder' => __('Enter Company Address')]) }}
                                    @error('company_address')
                                        <span class="invalid-company_address" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    {{ Form::label('company_city', __('City'), ['class' => 'form-label']) }}
                                    {{ Form::text('company_city', null, ['class' => 'form-control font-style', 'placeholder' => __('Enter Company City')]) }}
                                    @error('company_city')
                                        <span class="invalid-company_city" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                {{--<div class="form-group col-md-6">
                                    {{ Form::label('company_state', __('State'), ['class' => 'form-label']) }}
                                    {{ Form::text('company_state', null, ['class' => 'form-control font-style', 'placeholder' => __('Enter Company State')]) }}
                                    @error('company_state')
                                        <span class="invalid-company_state" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    {{ Form::label('company_zipcode', __('Zip/Post Code'), ['class' => 'form-label']) }}
                                    {{ Form::text('company_zipcode', null, ['class' => 'form-control', 'placeholder' => __('Enter Company Zip')]) }}
                                    @error('company_zipcode')
                                        <span class="invalid-company_zipcode" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>--}}
                                <div class="form-group  col-md-6">
                                    {{ Form::label('company_country', __('Country'), ['class' => 'form-label']) }}
                                    {{ Form::text('company_country', null, ['class' => 'form-control font-style', 'placeholder' => __('Enter Company Country')]) }}
                                    @error('company_country')
                                        <span class="invalid-company_country" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    {{ Form::label('company_telephone', __('Phone Number'), ['class' => 'form-label']) }}
                                    {{ Form::text('company_telephone', null, ['class' => 'form-control', 'placeholder' => __('Enter Company Telephone')]) }}
                                    @error('company_telephone')
                                        <span class="invalid-company_telephone" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    {{ Form::label('registration_number', __('Company Registration Number'), ['class' => 'form-label']) }}
                                    {{ Form::text('registration_number', null, ['class' => 'form-control', 'placeholder' => __('Enter Company Registration Number')]) }}
                                    @error('registration_number')
                                        <span class="invalid-registration_number" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                {{--<div class="form-group col-md-4">
                                    {{ Form::label('company_start_time', __('Company Start Time'), ['class' => 'form-label']) }}
                                    {{ Form::time('company_start_time', null, ['class' => 'form-control']) }}
                                    @error('company_start_time')
                                        <span class="invalid-company_start_time" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    {{ Form::label('company_end_time', __('Company End Time'), ['class' => 'form-label']) }}
                                    {{ Form::time('company_end_time', null, ['class' => 'form-control']) }}
                                    @error('company_end_time')
                                        <span class="invalid-company_end_time" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>


                                <div class="form-group col-md-4">
                                    <label class="" for="ip_restrict">{{ __('Ip Restrict') }}</label>
                                    <div class="custom-control custom-switch mt-2">
                                        <input type="checkbox" class=" form-check-input" data-toggle="switchbutton"
                                            data-onstyle="btn-primary" name="ip_restrict" id="ip_restrict"
                                            {{ isset($setting['ip_restrict']) && $setting['ip_restrict'] == 'on' ? 'checked' : '' }}>
                                    </div>
                                </div>--}}

                                <div class="form-group col-md-12 mt-2">
                                    {{ Form::label('timezone', __('Timezone'), ['class' => 'form-label']) }}
                                    <select type="text" name="timezone" class="form-control custom-select"
                                        id="timezone">
                                        <option value="">{{ __('Select Timezone') }}</option>
                                        @foreach ($timezones as $k => $timezone)
                                            <option value="{{ $k }}"
                                                {{ isset($setting['timezone']) && $setting['timezone'] == $k ? 'selected' : '' }}>{{ $timezone }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <div class="row mt-4">
                                        <div class="col-md-6">
                                            <label for="vat_gst_number_switch">{{ __('Tax Number') }}</label>
                                            <div class="form-check form-switch custom-switch-v1 float-end">
                                                <input type="checkbox" name="vat_gst_number_switch"
                                                    class="form-check-input input-primary pointer" value="on"
                                                    id="vat_gst_number_switch"
                                                    {{ isset($setting['vat_gst_number_switch']) && $setting['vat_gst_number_switch'] == 'on' ? ' checked ' : '' }}>
                                                <label class="form-check-label" for="vat_gst_number_switch"></label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    class="form-group col-md-6 tax_type_div {{ isset($setting['vat_gst_number_switch']) && $setting['vat_gst_number_switch'] != 'on' ? ' d-none ' : '' }}">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-check form-check-inline form-group mb-3">
                                                <input type="radio" id="customRadio8" name="tax_type" value="VAT"
                                                    class="form-check-input"
                                                    {{ isset($setting['tax_type']) && $setting['tax_type'] == 'VAT' ? 'checked' : '' }}>
                                                <label class="form-check-label"
                                                    for="customRadio8">{{ __('VAT Number') }}</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check form-check-inline form-group mb-3">
                                                <input type="radio" id="customRadio7" name="tax_type" value="GST"
                                                    class="form-check-input"
                                                    {{ isset($setting['tax_type']) && $setting['tax_type'] == 'GST' ? 'checked' : '' }}>
                                                <label class="form-check-label"
                                                    for="customRadio7">{{ __('GST Number') }}</label>
                                            </div>
                                        </div>
                                    </div>
                                    {{ Form::text('vat_number', null, ['class' => 'form-control', 'placeholder' => __('Enter VAT / GST Number')]) }}
                                </div>

                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <div class="form-group">
                                <button class="btn btn-print-invoice btn-primary m-r-10" type="submit">{{ __('Save Changes') }}</button>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>

                    <!--Currency Settings-->
                    <div id="currency-settings" class="card mb-3">
                        <div class="card-header">
                            <h5>{{ __('Currency Settings') }}</h5>
                            <small class="text-muted">{{ __('Edit your currency details') }}</small>
                        </div>
                        {{ Form::model($setting, ['route' => 'currency.settings', 'method' => 'post', 'id' => 'currency_setting']) }}
                        <div class="card-body">
                            <div class="row">
                                <div class="form-group col-md-6">
                                    {{ Form::label('site_currency', __('Currency'), ['class' => 'form-label']) }}
                                    {{ Form::text('site_currency', isset($setting['site_currency']) ? $setting['site_currency'] : '', ['class' => 'form-control font-style currency_preview', 'required', 'placeholder' => __('Enter Currency')]) }}
                                    <small> {{ __('Note: Add currency code as per three-letter ISO code.') }}<br>
                                        <a href="https://stripe.com/docs/currencies"
                                            target="_blank">{{ __('You can find out how to do that here.') }}</a></small>
                                    <br>
                                    @error('site_currency')
                                        <span class="invalid-site_currency" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    {{ Form::label('site_currency_symbol', __('Currency Symbol'), ['class' => 'form-label']) }}
                                    {{ Form::text('site_currency_symbol', null, ['class' => 'form-control currency_preview', 'placeholder' => __('Enter Currency Symbol')]) }}
                                    @error('site_currency_symbol')
                                        <span class="invalid-site_currency_symbol" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    {{ Form::label('decimal_number', __('Decimal Number Format'), ['class' => 'form-label']) }}
                                    {{ Form::number('decimal_number', null, ['class' => 'form-control currency_preview']) }}
                                    @error('decimal_number')
                                        <span class="invalid-decimal_number" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="float_number"
                                        class="form-label">{{ __('Float Number') }}</label>
                                    <select type="text" name="float_number"
                                        class="form-control selectric currency_preview" id="float_number">
                                        <option value="comma"
                                            @if (@$setting['float_number'] == 'comma') selected="selected" @endif>
                                            {{ __('Comma') }}</option>
                                        <option value="dot"
                                            @if (@$setting['float_number'] == 'dot') selected="selected" @endif>
                                            {{ __('Dot') }}</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="decimal_separator"
                                        class="form-label">{{ __('Decimal Separator') }}</label>
                                    <select type="text" name="decimal_separator"
                                        class="form-control selectric currency_preview" id="decimal_separator">
                                        <option value="dot"
                                            @if (@$setting['decimal_separator'] == 'dot') selected="selected" @endif>
                                            {{ __('Dot') }}</option>
                                        <option value="comma"
                                            @if (@$setting['decimal_separator'] == 'comma') selected="selected" @endif>
                                            {{ __('Comma') }}</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="thousand_separator"
                                        class="form-label">{{ __('Thousands Separator') }}</label>
                                    <select type="text" name="thousand_separator"
                                        class="form-control selectric currency_preview" id="thousand_separator">
                                        <option value="dot"
                                            @if (@$setting['thousand_separator'] == 'dot') selected="selected" @endif>
                                            {{ __('Dot') }}</option>
                                        <option value="comma"
                                            @if (@$setting['thousand_separator'] == 'comma') selected="selected" @endif>
                                            {{ __('Comma') }}</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label class="form-label"
                                        for="example3cols3Input">{{ __('Currency Symbol Position') }}</label>
                                    <div class="row ms-1">
                                        <div class="form-check col-md-6">
                                            <input class="form-check-input currency_preview" type="radio"
                                                name="site_currency_symbol_position" value="pre"
                                                @if (@$setting['site_currency_symbol_position'] == 'pre') checked @endif id="flexCheckDefault">
                                            <label class="form-check-label" for="flexCheckDefault">
                                                {{ __('Pre') }}
                                            </label>
                                        </div>
                                        <div class="form-check col-md-6">
                                            <input class="form-check-input currency_preview" type="radio"
                                                name="site_currency_symbol_position" value="post"
                                                @if (@$setting['site_currency_symbol_position'] == 'post') checked @endif id="flexCheckChecked">
                                            <label class="form-check-label" for="flexCheckChecked">
                                                {{ __('Post') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-md-6">
                                    {{ Form::label('currency_space', __('Currency Symbol Space'), ['class' => 'form-label']) }}
                                    <div class="row ms-1">
                                        <div class="form-check col-md-6">
                                            <input class="form-check-input currency_preview" type="radio"
                                                name="currency_space" value="withspace"
                                                @if (@$setting['currency_space'] == 'withspace') checked @endif id="withspace">
                                            <label class="form-check-label" for="withspace">
                                                {{ __('With space') }}
                                            </label>
                                        </div>
                                        <div class="form-check col-md-6">
                                            <input class="form-check-input currency_preview" type="radio"
                                                name="currency_space" value="withoutspace"
                                                @if (@$setting['currency_space'] == 'withoutspace') checked @endif id="withoutspace">
                                            <label class="form-check-label" for="withoutspace">
                                                {{ __('Without space') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-md-6">
                                    {{ Form::label('currency_symbol', __('Currency Symbol & Name'), ['class' => 'form-label']) }}
                                    <div class="row ms-1">
                                        <div class="form-check col-md-6">
                                            <input class="form-check-input currency_preview" type="radio"
                                                name="currency_symbol" value="withcurrencysymbol"
                                                @if (@$setting['currency_symbol'] == 'withcurrencysymbol') checked @endif id="withcurrencysymbol">
                                            <label class="form-check-label" for="withcurrencysymbol">
                                                {{ __('With Currency Symbol') }}
                                            </label>
                                        </div>
                                        <div class="form-check col-md-6">
                                            <input class="form-check-input currency_preview" type="radio"
                                                name="currency_symbol" value="withcurrencyname"
                                                @if (@$setting['currency_symbol'] == 'withcurrencyname') checked @endif id="withcurrencyname">
                                            <label class="form-check-label" for="withcurrencyname">
                                                {{ __('With Currency Name') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-md-6">
                                    {{ Form::label('preview', __('Preview : '), ['class' => 'form-label']) }}
                                    <div class="row">
                                        <div class="col-md-6 preview">
                                            {{ __('$ 10.000,00') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <div class="form-group">
                                <button class="btn btn-print-invoice btn-primary m-r-10" type="submit">{{ __('Save Changes') }}</button>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>

                    <!--Email Settings-->
                    <div id="email-settings" class="card mb-3">
                        <div class="card-header">
                            <h5>{{ __('Email Settings') }}</h5>
                            <small class="text-muted">{{ __('This SMTP will be used for sending your company-level email. If this field is empty, then SuperAdmin SMTP will be used for sending emails.')}}</small>
                        </div>
                        {{ Form::model($emailSetting, ['route' => 'company.email.settings', 'method' => 'post']) }}
                        <div class="card-body">
                            @csrf
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        {{ Form::label('mail_driver', __('Mail Driver'), ['class' => 'form-label']) }}
                                        {{ Form::text('mail_driver', isset($emailSetting['mail_driver']) ? $emailSetting['mail_driver'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Mail Driver')]) }}
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
                                        {{ Form::text('mail_host', isset($emailSetting['mail_host']) ? $emailSetting['mail_host'] : '', ['class' => 'form-control ', 'placeholder' => __('Enter Mail Host')]) }}
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
                                        {{ Form::text('mail_port', isset($emailSetting['mail_port']) ? $emailSetting['mail_port'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Mail Port')]) }}
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
                                        {{ Form::text('mail_username', isset($emailSetting['mail_username']) ? $emailSetting['mail_username'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Mail Username')]) }}
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
                                        {{ Form::text('mail_password', isset($emailSetting['mail_password']) ? $emailSetting['mail_password'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Mail Password')]) }}
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
                                        {{ Form::text('mail_encryption', isset($emailSetting['mail_encryption']) ? $emailSetting['mail_encryption'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Mail Encryption')]) }}
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
                                        {{ Form::text('mail_from_address', isset($emailSetting['mail_from_address']) ? $emailSetting['mail_from_address'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Mail From Address')]) }}
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
                                        {{ Form::text('mail_from_name', isset($emailSetting['mail_from_name']) ? $emailSetting['mail_from_name'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Mail From Name')]) }}
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
                                    <button class="btn btn-print-invoice btn-primary m-r-10" type="submit">{{ __('Save Changes') }}</button>
                                </div>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>

                    <!--Payment Settings-->
                    <div class="card mb-3" id="payment-settings">
                        <div class="card-header">
                            <h5>{{ 'Payment Settings' }}</h5>
                            <small class="text-secondary font-weight-bold">{{ __('These details will be used to collect invoice payments. Each invoice will have a payment button based on the below configuration.') }}</small>
                            <small class="text-secondary font-weight-bold">{{ __('Enable either one Gateway for Both PPPoE and Hotspot or Two gateways for each and not more than two gateways at a time.') }}</small>
                        </div>
                        {{ Form::model($setting, ['route' => 'company.payment.settings', 'method' => 'POST']) }}
                        @csrf
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12">
                                    <div class="faq justify-content-center">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="accordion accordion-flush setting-accordion" id="accordionExample">
                                                    <!-- Mpesa -->
                                                    <div class="accordion-item card mt-2">
                                                        <h2 class="accordion-header" id="headingOne">
                                                            <button class="accordion-button collapsed" type="button"
                                                                data-bs-toggle="collapse" data-bs-target="#mpesa"
                                                                aria-expanded="false" aria-controls="collapseOne">
                                                                <span class="d-flex align-items-center me-4">
                                                                    {{ __('Mpesa Business Till or Paybill') }}
                                                                </span>
                                                                <div class="d-flex align-items-center">
                                                                    <!-- <span class="me-2">{{ __('Enable') }}:</span> -->
                                                                    <div class="form-check form-switch custom-switch-v1">
                                                                        <input type="hidden" name="is_mpesa_enabled"
                                                                            value="off">
                                                                        <input type="checkbox"
                                                                            class="form-check-input input-primary"
                                                                            id="customswitchv1-1 is_mpesa_enabled"
                                                                            name="is_mpesa_enabled"
                                                                            {{ isset($company_payment_setting['is_mpesa_enabled']) && $company_payment_setting['is_mpesa_enabled'] == 'on' ? 'checked="checked"' : '' }}>
                                                                    </div>
                                                                </div>
                                                            </button>
                                                        </h2>
                                                        <div id="mpesa" class="accordion-collapse collapse"
                                                            aria-labelledby="headingOne"
                                                            data-bs-parent="#accordionExample">
                                                            <div class="accordion-body">
                                                                <div class="d-flex">
                                                                    <div class="mr-2" style="margin-right: 15px;">
                                                                        <div class="border card p-3">
                                                                            <div class="form-check">
                                                                                <label class="form-check-label text-dark">
                                                                                    <input type="radio"
                                                                                        name="mpesa_mode" value="PPPoE"
                                                                                        class="form-check-input"
                                                                                        {{ (isset($company_payment_setting['mpesa_mode']) && $company_payment_setting['mpesa_mode'] == '') || (isset($company_payment_setting['mpesa_mode']) && $company_payment_setting['mpesa_mode'] == 'PPPoE') ? 'checked="checked"' : '' }}>
                                                                                    {{ __('For PPPoE') }}
                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="mr-2" style="margin-right: 15px;">
                                                                        <div class="border card p-3">
                                                                            <div class="form-check">
                                                                                <label class="form-check-label text-dark">
                                                                                    <input type="radio"
                                                                                        name="mpesa_mode" value="Hotspot"
                                                                                        class="form-check-input"
                                                                                        {{ isset($company_payment_setting['mpesa_mode']) && $company_payment_setting['mpesa_mode'] == 'Hotspot' ? 'checked="checked"' : '' }}>
                                                                                    {{ __('For Hotspot') }}
                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="mr-2" style="margin-right: 15px;">
                                                                        <div class="border card p-3">
                                                                            <div class="form-check">
                                                                                <label class="form-check-label text-dark">
                                                                                    <input type="radio"
                                                                                        name="mpesa_mode" value="Both"
                                                                                        class="form-check-input"
                                                                                        {{ isset($company_payment_setting['mpesa_mode']) && $company_payment_setting['mpesa_mode'] == 'Both' ? 'checked="checked"' : '' }}>
                                                                                    {{ __('For Both') }}
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
                                                                                {{ Form::text('mpesa_key', isset($company_payment_setting['mpesa_key']) ? $company_payment_setting['mpesa_key'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Mpesa Consumer Key')]) }}
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
                                                                                {{ Form::text('mpesa_secret', isset($company_payment_setting['mpesa_secret']) ? $company_payment_setting['mpesa_secret'] : '', ['class' => 'form-control ', 'placeholder' => __('Enter Mpesa Consumer Secret')]) }}
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
                                                                                {{ Form::label('mpesa_shortcode_type', __('Mpesa Business Shortcode Type'), ['class' => 'col-form-label']) }}
                                                                                {{ Form::select('mpesa_shortcode_type', 
                                                                                    ['paybill' => 'Mpesa Paybill', 'till' => 'Mpesa Till/Buy Goods'], 
                                                                                    isset($company_payment_setting['mpesa_shortcode_type']) ? $company_payment_setting['mpesa_shortcode_type'] : '', 
                                                                                    ['class' => 'form-control', 'required' => 'required']) 
                                                                                }}
                                                                                @if ($errors->has('mpesa_shortcode_type'))
                                                                                    <span class="invalid-feedback d-block">
                                                                                        {{ $errors->first('mpesa_shortcode_type') }}
                                                                                    </span>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-6">
                                                                        <div class="input-edits">
                                                                            <div class="form-group">
                                                                                {{ Form::label('mpesa_shortcode', __('Mpesa Business Shortcode'), ['class' => 'col-form-label']) }}
                                                                                {{ Form::text('mpesa_shortcode', isset($company_payment_setting['mpesa_shortcode']) ? $company_payment_setting['mpesa_shortcode'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Mpesa Business Shortcode')]) }}
                                                                                @if ($errors->has('mpesa_shortcode'))
                                                                                    <span class="invalid-feedback d-block">
                                                                                        {{ $errors->first('mpesa_shortcode') }}
                                                                                    </span>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-6">
                                                                        <div class="input-edits">
                                                                            <div class="form-group">
                                                                                {{ Form::label('mpesa_passkey', __('Mpesa Passkey'), ['class' => 'col-form-label']) }}
                                                                                {{ Form::text('mpesa_passkey', isset($company_payment_setting['mpesa_passkey']) ? $company_payment_setting['mpesa_passkey'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Mpesa Passkey')]) }}
                                                                                @if ($errors->has('mpesa_passkey'))
                                                                                    <span class="invalid-feedback d-block">
                                                                                        {{ $errors->first('mpesa_passkey') }}
                                                                                    </span>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- Mpesa PayBill-->
                                                    <div class="accordion-item card mt-2">
                                                        <h2 class="accordion-header" id="headingOne">
                                                            <button class="accordion-button collapsed" type="button"
                                                                data-bs-toggle="collapse" data-bs-target="#mpesaPaybill"
                                                                aria-expanded="false" aria-controls="collapseOne">
                                                                <span class="d-flex align-items-center me-4">
                                                                    {{ __('Mpesa Personal Paybill') }}
                                                                </span>
                                                                <div class="d-flex align-items-center">
                                                                    <div class="form-check form-switch custom-switch-v1">
                                                                        <input type="hidden" name="is_mpesa_paybill_enabled"
                                                                            value="off">
                                                                        <input type="checkbox"
                                                                            class="form-check-input input-primary"
                                                                            id="customswitchv1-1 is_mpesa_paybill_enabled"
                                                                            name="is_mpesa_paybill_enabled"
                                                                            {{ isset($company_payment_setting['is_mpesa_paybill_enabled']) && $company_payment_setting['is_mpesa_paybill_enabled'] == 'on' ? 'checked="checked"' : '' }}>
                                                                    </div>
                                                                </div>
                                                            </button>
                                                        </h2>
                                                        <div id="mpesaPaybill" class="accordion-collapse collapse"
                                                            aria-labelledby="headingOne"
                                                            data-bs-parent="#accordionExample">
                                                            <div class="accordion-body">
                                                                <div class="d-flex">
                                                                    <div class="mr-2" style="margin-right: 15px;">
                                                                        <div class="border card p-3">
                                                                            <div class="form-check">
                                                                                <label class="form-check-label text-dark">
                                                                                    <input type="radio"
                                                                                        name="mpesa_paybill_mode" value="PPPoE"
                                                                                        class="form-check-input"
                                                                                        {{ (isset($company_payment_setting['mpesa_paybill_mode']) && $company_payment_setting['mpesa_paybill_mode'] == '') || (isset($company_payment_setting['mpesa_paybill_mode']) && $company_payment_setting['mpesa_paybill_mode'] == 'PPPoE') ? 'checked="checked"' : '' }}>
                                                                                    {{ __('For PPPoE') }}
                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="mr-2" style="margin-right: 15px;">
                                                                        <div class="border card p-3">
                                                                            <div class="form-check">
                                                                                <label class="form-check-label text-dark">
                                                                                    <input type="radio"
                                                                                        name="mpesa_paybill_mode" value="Hotspot"
                                                                                        class="form-check-input"
                                                                                        {{ isset($company_payment_setting['mpesa_paybill_mode']) && $company_payment_setting['mpesa_paybill_mode'] == 'Hotspot' ? 'checked="checked"' : '' }}>
                                                                                    {{ __('For Hotspot') }}
                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="mr-2" style="margin-right: 15px;">
                                                                        <div class="border card p-3">
                                                                            <div class="form-check">
                                                                                <label class="form-check-label text-dark">
                                                                                    <input type="radio"
                                                                                        name="mpesa_paybill_mode" value="Both"
                                                                                        class="form-check-input"
                                                                                        {{ isset($company_payment_setting['mpesa_paybill_mode']) && $company_payment_setting['mpesa_paybill_mode'] == 'Both' ? 'checked="checked"' : '' }}>
                                                                                    {{ __('For Both') }}
                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="d-flex align-items-center">
                                                                        <span class="me-2"> {{ __('System API') }}:</span>
                                                                        <div class="form-check form-switch custom-switch-v1">
                                                                            <input type="hidden" name="is_system_mpesa_paybill_api_enabled"
                                                                                value="off">
                                                                            <input type="checkbox"
                                                                                class="form-check-input input-primary"
                                                                                id="customswitchv1-1 is_system_mpesa_paybill_api_enabled"
                                                                                name="is_system_mpesa_paybill_api_enabled"
                                                                                {{ isset($company_payment_setting['is_system_mpesa_paybill_api_enabled']) && $company_payment_setting['is_system_mpesa_paybill_api_enabled'] == 'on' ? 'checked="checked"' : '' }}>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="row gy-4">
                                                                    <div class="col-lg-6">
                                                                        <div class="input-edits">
                                                                            <div class="form-group">
                                                                                {{ Form::label('mpesa_paybill', __('Paybill Number'), ['class' => 'col-form-label']) }}
                                                                                {{ Form::text('mpesa_paybill', isset($company_payment_setting['mpesa_paybill']) ? $company_payment_setting['mpesa_paybill'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Paybill Number')]) }}
                                                                                @if ($errors->has('mpesa_paybill'))
                                                                                    <span class="invalid-feedback d-block">
                                                                                        {{ $errors->first('mpesa_paybill') }}
                                                                                    </span>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-6">
                                                                        <div class="input-edits">
                                                                            <div class="form-group">
                                                                                {{ Form::label('mpesa_paybill_account', __('PayBill Name/Reference'), ['class' => 'col-form-label']) }}
                                                                                {{ Form::text('mpesa_paybill_account', isset($company_payment_setting['mpesa_paybill_account']) ? $company_payment_setting['mpesa_paybill_account'] : '', ['class' => 'form-control', 'placeholder' => __('Enter PayBill Name/Reference')]) }}
                                                                                @if ($errors->has('mpesa_paybill_account'))
                                                                                    <span class="invalid-feedback d-block">
                                                                                        {{ $errors->first('mpesa_paybill_account') }}
                                                                                    </span>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- Mpesa Till -->
                                                    <div class="accordion-item card mt-2">
                                                        <h2 class="accordion-header" id="headingOne">
                                                            <button class="accordion-button collapsed" type="button"
                                                                data-bs-toggle="collapse" data-bs-target="#mpesaTill"
                                                                aria-expanded="false" aria-controls="collapseOne">
                                                                <span class="d-flex align-items-center me-4">
                                                                    {{ __('Mpesa Personal Till/Buy Goods') }}
                                                                </span>
                                                                <div class="d-flex align-items-center">
                                                                    <div class="form-check form-switch custom-switch-v1">
                                                                        <input type="hidden" name="is_mpesa_till_enabled"
                                                                            value="off">
                                                                        <input type="checkbox"
                                                                            class="form-check-input input-primary"
                                                                            id="customswitchv1-1 is_mpesa_till_enabled"
                                                                            name="is_mpesa_till_enabled"
                                                                            {{ isset($company_payment_setting['is_mpesa_till_enabled']) && $company_payment_setting['is_mpesa_till_enabled'] == 'on' ? 'checked="checked"' : '' }}>
                                                                    </div>
                                                                </div>
                                                            </button>
                                                        </h2>
                                                        <div id="mpesaTill" class="accordion-collapse collapse"
                                                            aria-labelledby="headingOne"
                                                            data-bs-parent="#accordionExample">
                                                            <div class="accordion-body">
                                                                <div class="d-flex">
                                                                    <div class="mr-2" style="margin-right: 15px;">
                                                                        <div class="border card p-3">
                                                                            <div class="form-check">
                                                                                <label class="form-check-label text-dark">
                                                                                    <input type="radio"
                                                                                        name="mpesa_till_mode" value="PPPoE"
                                                                                        class="form-check-input"
                                                                                        {{ (isset($company_payment_setting['mpesa_till_mode']) && $company_payment_setting['mpesa_till_mode'] == '') || (isset($company_payment_setting['mpesa_till_mode']) && $company_payment_setting['mpesa_till_mode'] == 'PPPoE') ? 'checked="checked"' : '' }}>
                                                                                    {{ __('For PPPoE') }}
                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="mr-2" style="margin-right: 15px;">
                                                                        <div class="border card p-3">
                                                                            <div class="form-check">
                                                                                <label class="form-check-label text-dark">
                                                                                    <input type="radio"
                                                                                        name="mpesa_till_mode" value="Hotspot"
                                                                                        class="form-check-input"
                                                                                        {{ isset($company_payment_setting['mpesa_till_mode']) && $company_payment_setting['mpesa_till_mode'] == 'Hotspot' ? 'checked="checked"' : '' }}>
                                                                                    {{ __('For Hotspot') }}
                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="mr-2" style="margin-right: 15px;">
                                                                        <div class="border card p-3">
                                                                            <div class="form-check">
                                                                                <label class="form-check-label text-dark">
                                                                                    <input type="radio"
                                                                                        name="mpesa_till_mode" value="Both"
                                                                                        class="form-check-input"
                                                                                        {{ isset($company_payment_setting['mpesa_till_mode']) && $company_payment_setting['mpesa_till_mode'] == 'Both' ? 'checked="checked"' : '' }}>
                                                                                    {{ __('For Both') }}
                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="d-flex align-items-center">
                                                                        <span class="me-2"> {{ __('System API') }}:</span>
                                                                        <div class="form-check form-switch custom-switch-v1">
                                                                            <input type="hidden" name="is_system_mpesa_till_api_enabled"
                                                                                value="off">
                                                                            <input type="checkbox"
                                                                                class="form-check-input input-primary"
                                                                                id="customswitchv1-1 is_system_mpesa_till_api_enabled"
                                                                                name="is_system_mpesa_till_api_enabled"
                                                                                {{ isset($company_payment_setting['is_system_mpesa_till_api_enabled']) && $company_payment_setting['is_system_mpesa_till_api_enabled'] == 'on' ? 'checked="checked"' : '' }}>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="row gy-4">
                                                                    <div class="col-lg-6">
                                                                        <div class="input-edits">
                                                                            <div class="form-group">
                                                                                {{ Form::label('mpesa_till', __('Till Number'), ['class' => 'col-form-label']) }}
                                                                                {{ Form::text('mpesa_till', isset($company_payment_setting['mpesa_till']) ? $company_payment_setting['mpesa_till'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Till Number')]) }}
                                                                                @if ($errors->has('mpesa_till'))
                                                                                    <span class="invalid-feedback d-block">
                                                                                        {{ $errors->first('mpesa_till') }}
                                                                                    </span>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-6">
                                                                        <div class="input-edits">
                                                                            <div class="form-group">
                                                                                {{ Form::label('mpesa_till_account', __('Till/Buy Goods Name/Reference'), ['class' => 'col-form-label']) }}
                                                                                {{ Form::text('mpesa_till_account', isset($company_payment_setting['mpesa_till_account']) ? $company_payment_setting['mpesa_till_account'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Till/Buy Goods Name/Reference')]) }}
                                                                                @if ($errors->has('mpesa_till_account'))
                                                                                    <span class="invalid-feedback d-block">
                                                                                        {{ $errors->first('mpesa_till_account') }}
                                                                                    </span>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- Mpesa Bank and Sacco -->
                                                    <div class="accordion-item card mt-2">
                                                        <h2 class="accordion-header" id="headingOne">
                                                            <button class="accordion-button collapsed" type="button"
                                                                data-bs-toggle="collapse" data-bs-target="#BankSacco"
                                                                aria-expanded="false" aria-controls="collapseOne">
                                                                <span class="d-flex align-items-center me-4">
                                                                    {{ __('Bank and Sacco') }}
                                                                </span>
                                                                <div class="d-flex align-items-center">
                                                                    <div class="form-check form-switch custom-switch-v1">
                                                                        <input type="hidden" name="is_mpesa_bank_enabled"
                                                                            value="off">
                                                                        <input type="checkbox"
                                                                            class="form-check-input input-primary"
                                                                            id="customswitchv1-1 is_mpesa_bank_enabled"
                                                                            name="is_mpesa_bank_enabled"
                                                                            {{ isset($company_payment_setting['is_mpesa_bank_enabled']) && $company_payment_setting['is_mpesa_bank_enabled'] == 'on' ? 'checked="checked"' : '' }}>
                                                                    </div>
                                                                </div>
                                                            </button>
                                                        </h2>
                                                        <div id="BankSacco" class="accordion-collapse collapse"
                                                            aria-labelledby="headingOne"
                                                            data-bs-parent="#accordionExample">
                                                            <div class="accordion-body">
                                                                <div class="d-flex">
                                                                    <div class="mr-2" style="margin-right: 15px;">
                                                                        <div class="border card p-3">
                                                                            <div class="form-check">
                                                                                <label class="form-check-label text-dark">
                                                                                    <input type="radio"
                                                                                        name="mpesa_bank_mode" value="PPPoE"
                                                                                        class="form-check-input"
                                                                                        {{ (isset($company_payment_setting['mpesa_bank_mode']) && $company_payment_setting['mpesa_bank_mode'] == '') || (isset($company_payment_setting['mpesa_bank_mode']) && $company_payment_setting['mpesa_bank_mode'] == 'PPPoE') ? 'checked="checked"' : '' }}>
                                                                                    {{ __('For PPPoE') }}
                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="mr-2" style="margin-right: 15px;">
                                                                        <div class="border card p-3">
                                                                            <div class="form-check">
                                                                                <label class="form-check-label text-dark">
                                                                                    <input type="radio"
                                                                                        name="mpesa_bank_mode" value="Hotspot"
                                                                                        class="form-check-input"
                                                                                        {{ isset($company_payment_setting['mpesa_bank_mode']) && $company_payment_setting['mpesa_bank_mode'] == 'Hotspot' ? 'checked="checked"' : '' }}>
                                                                                    {{ __('For Hotspot') }}
                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="mr-2" style="margin-right: 15px;">
                                                                        <div class="border card p-3">
                                                                            <div class="form-check">
                                                                                <label class="form-check-label text-dark">
                                                                                    <input type="radio"
                                                                                        name="mpesa_bank_mode" value="Both"
                                                                                        class="form-check-input"
                                                                                        {{ isset($company_payment_setting['mpesa_bank_mode']) && $company_payment_setting['mpesa_bank_mode'] == 'Both' ? 'checked="checked"' : '' }}>
                                                                                    {{ __('For Both') }}
                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="d-flex align-items-center">
                                                                        <span class="me-2"> {{ __('System API') }}:</span>
                                                                        <div class="form-check form-switch custom-switch-v1">
                                                                            <input type="hidden" name="is_system_mpesa_api_enabled"
                                                                                value="off">
                                                                            <input type="checkbox"
                                                                                class="form-check-input input-primary"
                                                                                id="customswitchv1-1 is_system_mpesa_api_enabled"
                                                                                name="is_system_mpesa_api_enabled"
                                                                                {{ isset($company_payment_setting['is_system_mpesa_api_enabled']) && $company_payment_setting['is_system_mpesa_api_enabled'] == 'on' ? 'checked="checked"' : '' }}>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="row gy-4">
                                                                    <div class="col-lg-6">
                                                                        <div class="input-edits">
                                                                            <div class="form-group">
                                                                                {{ Form::label('mpesa_bank_paybill', __('Bank Paybill'), ['class' => 'col-form-label']) }}
                                                                                {{ Form::text('mpesa_bank_paybill', isset($company_payment_setting['mpesa_bank_paybill']) ? $company_payment_setting['mpesa_bank_paybill'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Bank Paybill Number')]) }}
                                                                                @if ($errors->has('mpesa_bank_paybill'))
                                                                                    <span class="invalid-feedback d-block">
                                                                                        {{ $errors->first('mpesa_bank_paybill') }}
                                                                                    </span>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-6">
                                                                        <div class="input-edits">
                                                                            <div class="form-group">
                                                                                {{ Form::label('mpesa_bank_account', __('Bank Account'), ['class' => 'col-form-label']) }}
                                                                                {{ Form::text('mpesa_bank_account', isset($company_payment_setting['mpesa_bank_account']) ? $company_payment_setting['mpesa_bank_account'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Bank Account Number')]) }}
                                                                                @if ($errors->has('mpesa_bank_account'))
                                                                                    <span class="invalid-feedback d-block">
                                                                                        {{ $errors->first('mpesa_bank_account') }}
                                                                                    </span>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Paypal -->
                                                    {{--<div class="accordion-item">
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
                                                                            {{ isset($company_payment_setting['is_paypal_enabled']) && $company_payment_setting['is_paypal_enabled'] == 'on' ? 'checked="checked"' : '' }}>
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
                                                                                <label
                                                                                    class="form-check-label text-dark me-2">
                                                                                    <input type="radio"
                                                                                        name="paypal_mode" value="sandbox"
                                                                                        class="form-check-input"
                                                                                        {{ (isset($company_payment_setting['paypal_mode']) && $company_payment_setting['paypal_mode'] == '') || (isset($company_payment_setting['paypal_mode']) && $company_payment_setting['paypal_mode'] == 'sandbox') ? 'checked="checked"' : '' }}>
                                                                                    {{ __('Sandbox') }}
                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="mr-2" style="margin-right: 15px;">
                                                                        <div class="border card p-3">
                                                                            <div class="form-check">
                                                                                <label
                                                                                    class="form-check-label text-dark me-2">
                                                                                    <input type="radio"
                                                                                        name="paypal_mode" value="live"
                                                                                        class="form-check-input"
                                                                                        {{ isset($company_payment_setting['paypal_mode']) && $company_payment_setting['paypal_mode'] == 'live' ? 'checked="checked"' : '' }}>
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
                                                                                    value="{{ !isset($company_payment_setting['paypal_client_id']) || is_null($company_payment_setting['paypal_client_id']) ? '' : $company_payment_setting['paypal_client_id'] }}"
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
                                                                                    value="{{ isset($company_payment_setting['paypal_secret_key']) ? $company_payment_setting['paypal_secret_key'] : '' }}"
                                                                                    placeholder="{{ __('Secret Key') }}">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Paystack -->
                                                    <div class="accordion-item">
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
                                                                            {{ isset($company_payment_setting['is_paystack_enabled']) && $company_payment_setting['is_paystack_enabled'] == 'on' ? 'checked="checked"' : '' }}>
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
                                                                                    value="{{ isset($company_payment_setting['paystack_public_key']) ? $company_payment_setting['paystack_public_key'] : '' }}"
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
                                                                                    value="{{ isset($company_payment_setting['paystack_secret_key']) ? $company_payment_setting['paystack_secret_key'] : '' }}"
                                                                                    placeholder="{{ __('Secret Key') }}" />
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
                                <button class="btn btn-print-invoice btn-primary m-r-10" type="submit">{{ __('Save Changes') }}</button>
                            </div>
                        </div>
                        </form>
                    </div>

                    
                    <!--sms Settings-->
                    <div id="sms-settings" class="card mb-3">
                        <div class="card-header">
                            <h5>{{ __('SMS Settings') }}</h5>
                            <small class="text-muted">{{ __('Edit your SMS settings') }}</small>
                        </div>
                        {{ Form::model($setting, ['route' => 'sms.setting', 'method' => 'post']) }}
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        {{ Form::label('sms_url', __('SMS Provider'), ['class' => 'form-label']) }}
                                        {{ Form::select('sms_url', [
                                            'https://blessedtexts.com/api/sms/v1/sendsms?api_key=[apikey]&sender_id=[senderid]&message=[text]&phone=[number]' => 'Blessed TEXT',
                                            'https://sms.textsms.co.ke/api/services/sendsms/?apikey=[apikey]&partnerID=[patnerid]&message=[text]&shortcode=[senderid]&mobile=[number]' => 'TextSMS',
                                            'https://api.clicksend.com' => 'TrueHost',
                                            'https://api.textlocal.in' => 'ByteWave',
                                            'https://api.africastalking.com' => 'HostAfrica',
                                            'https://api.africastalking.com' => 'AfroKat',
                                            'https://api.africastalking.com' => 'Africa’s Talking',
                                        ], isset($comSetting['sms_url']) ? $comSetting['sms_url'] : '', ['class' => 'form-control w-100', 'required' => 'required']) }}

                                        @error('sms_url')
                                            <span class="invalid-sms_url" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        {{ Form::label('sms_apitoken', __('API Token'), ['class' => 'form-label']) }}
                                        {{ Form::text('sms_apitoken', isset($comSetting['sms_apitoken']) ? $comSetting['sms_apitoken'] : '', ['class' => 'form-control w-100', 'placeholder' => __('Enter API Token'), 'required' => 'required']) }}
                                        @error('sms_apitoken')
                                            <span class="invalid-sms_apitoken" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        {{ Form::label('sms_senderid', __('Sender ID'), ['class' => 'form-label']) }}
                                        {{ Form::text('sms_senderid', isset($comSetting['sms_senderid']) ? $comSetting['sms_senderid'] : '', ['class' => 'form-control w-100', 'placeholder' => __('Enter Sender ID'), 'required' => 'required']) }}
                                        @error('sms_senderid')
                                            <span class="invalid-sms_senderid" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-12 mt-4 mb-2">
                                    <h5 class="small-title">{{ __('Notification Settings') }}</h5>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span>{{ __('New Customer') }}</span>
                                                <div class=" form-switch form-switch-right">
                                                    {{ Form::checkbox('sms_customer_notification', '1', isset($comSetting['sms_customer_notification']) && $comSetting['sms_customer_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'sms_customer_notification']) }}
                                                    <label class="form-check-label"
                                                        for="sms_customer_notification"></label>
                                                </div>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span>{{ __('Balance Deposit') }}</span>
                                                <div class=" form-switch form-switch-right">
                                                    {{ Form::checkbox('sms_deposit_notification', '1', isset($comSetting['sms_deposit_notification']) && $comSetting['sms_deposit_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'sms_deposit_notification']) }}
                                                    <label class="form-check-label"
                                                        for="sms_deposit_notification"></label>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span>{{ __('New Invoice') }}</span>
                                                <div class=" form-switch form-switch-right">
                                                    {{ Form::checkbox('sms_invoice_notification', '1', isset($comSetting['sms_invoice_notification']) && $comSetting['sms_invoice_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'sms_invoice_notification']) }}
                                                    <label class="form-check-label"
                                                        for="sms_invoice_notification"></label>
                                                </div>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span>{{ __('New Payment') }}</span>
                                                <div class=" form-switch form-switch-right">
                                                    {{ Form::checkbox('sms_payment_notification', '1', isset($comSetting['sms_payment_notification']) && $comSetting['sms_payment_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'sms_payment_notification']) }}
                                                    <label class="form-check-label"
                                                        for="sms_payment_notification"></label>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span>{{ __('Payment Reminder') }}</span>
                                                <div class=" form-switch form-switch-right">
                                                    {{ Form::checkbox('sms_reminder_notification', '1', isset($comSetting['sms_reminder_notification']) && $comSetting['sms_reminder_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'sms_reminder_notification']) }}
                                                    <label class="form-check-label"
                                                        for="sms_reminder_notification"></label>
                                                </div>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span>{{ __('Expiry Notification') }}</span>
                                                <div class=" form-switch form-switch-right">
                                                    {{ Form::checkbox('sms_expiry_notification', '1', isset($comSetting['sms_expiry_notification']) && $comSetting['sms_expiry_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'sms_expiry_notification']) }}
                                                    <label class="form-check-label"
                                                        for="sms_expiry_notification"></label>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <div class="form-group">
                                <button class="btn btn-print-invoice btn-primary m-r-10" type="submit">{{ __('Save Changes') }}</button>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>

                    <!--Whatsapp Settings-->
                    <div id="whatsapp-settings" class="card mb-3">
                        <div class="card-header">
                            <h5>{{ __('Whatsapp Settings') }}</h5>
                            <small class="text-muted">{{ __('Edit your Whatsapp settings') }}</small>
                        </div>
                        {{ Form::model($setting, ['route' => 'whatsapp.setting', 'method' => 'post']) }}
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        {{ Form::label('whatsapp_url', __('Whatsapp  Url'), ['class' => 'form-label']) }}
                                        {{ Form::text('whatsapp_url', isset($comSetting['whatsapp_url']) ? $comSetting['whatsapp_url'] : '', ['class' => 'form-control w-100', 'placeholder' => __('Enter Whatsapp Url'), 'required' => 'required']) }}
                                        @error('whatsapp_url')
                                            <span class="invalid-whatsapp_url" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                {{--<div class="col-md-4">
                                    <div class="form-group">
                                        {{ Form::label('sms_apitoken', __('API Token'), ['class' => 'form-label']) }}
                                        {{ Form::text('sms_apitoken', isset($comSetting['sms_apitoken']) ? $comSetting['sms_apitoken'] : '', ['class' => 'form-control w-100', 'placeholder' => __('Enter API Token'), 'required' => 'required']) }}
                                        @error('sms_apitoken')
                                            <span class="invalid-sms_apitoken" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>--}}

                                <div class="col-md-12 mt-4 mb-2">
                                    <h5 class="small-title">{{ __('Notification Settings') }}</h5>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span>{{ __('New Customer') }}</span>
                                                <div class=" form-switch form-switch-right">
                                                    {{ Form::checkbox('whatsapp_customer_notification', '1', isset($comSetting['whatsapp_customer_notification']) && $comSetting['whatsapp_customer_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'whatsapp_customer_notification']) }}
                                                    <label class="form-check-label"
                                                        for="whatsapp_customer_notification"></label>
                                                </div>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span>{{ __('Balance Deposit') }}</span>
                                                <div class=" form-switch form-switch-right">
                                                    {{ Form::checkbox('whatsapp_deposit_notification', '1', isset($comSetting['whatsapp_deposit_notification']) && $comSetting['whatsapp_deposit_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'whatsapp_deposit_notification']) }}
                                                    <label class="form-check-label"
                                                        for="whatsapp_deposit_notification"></label>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span>{{ __('New Invoice') }}</span>
                                                <div class=" form-switch form-switch-right">
                                                    {{ Form::checkbox('whatsapp_invoice_notification', '1', isset($comSetting['whatsapp_invoice_notification']) && $comSetting['whatsapp_invoice_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'whatsapp_invoice_notification']) }}
                                                    <label class="form-check-label"
                                                        for="whatsapp_invoice_notification"></label>
                                                </div>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span>{{ __('New Payment') }}</span>
                                                <div class=" form-switch form-switch-right">
                                                    {{ Form::checkbox('whatsapp_payment_notification', '1', isset($comSetting['whatsapp_payment_notification']) && $comSetting['whatsapp_payment_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'whatsapp_payment_notification']) }}
                                                    <label class="form-check-label"
                                                        for="whatsapp_payment_notification"></label>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span>{{ __('Payment Reminder') }}</span>
                                                <div class=" form-switch form-switch-right">
                                                    {{ Form::checkbox('whatsapp_reminder_notification', '1', isset($comSetting['whatsapp_reminder_notification']) && $comSetting['whatsapp_reminder_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'whatsapp_reminder_notification']) }}
                                                    <label class="form-check-label"
                                                        for="whatsapp_reminder_notification"></label>
                                                </div>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span>{{ __('Expiry Notification') }}</span>
                                                <div class=" form-switch form-switch-right">
                                                    {{ Form::checkbox('whatsapp_expiry_notification', '1', isset($comSetting['whatsapp_expiry_notification']) && $comSetting['whatsapp_expiry_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'whatsapp_expiry_notification']) }}
                                                    <label class="form-check-label"
                                                        for="whatsapp_expiry_notification"></label>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <div class="form-group">
                                <button class="btn btn-print-invoice btn-primary m-r-10" type="submit">{{ __('Save Changes') }}</button>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>

                    <div id="webhook-settings" class="card mb-3">
                        <div class="col-md-12">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col-6">
                                        <h5 class="mb-2">{{ __('Webhook Settings') }}</h5>
                                    </div>
                                    @can('create webhook')
                                        <div class="col-6 text-end">
                                            <a href="#" data-size="lg" data-url="{{ route('webhook.create') }}"
                                                data-ajax-popup="true" data-bs-toggle="tooltip"
                                                title="{{ __('Create') }}" data-title="{{ __('Create New Webhook') }}"
                                                class="btn btn-sm btn-primary">
                                                <i class="ti ti-plus"></i>
                                            </a>

                                        </div>
                                    @endcan
                                </div>
                            </div>
                            <div class="card-body table-border-style">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Module') }}</th>
                                                <th>{{ __('Url') }}</th>
                                                <th>{{ __('Method') }}</th>
                                                <th>{{ __('Action') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="font-style">
                                            @forelse ($webhookSetting as $webhooksetting)
                                                <tr>
                                                    <td>{{ ucwords($webhooksetting->module) }}</td>
                                                    <td>{{ $webhooksetting->url }}</td>
                                                    <td>{{ ucwords($webhooksetting->method) }}</td>
                                                    <td class="Action">
                                                        <span>
                                                            @can('edit webhook')
                                                                <div class="action-btn me-2">
                                                                    <a href="#"
                                                                        class="mx-3 btn btn-sm align-items-center bg-info"
                                                                        data-url="{{ URL::to('webhook-settings/' . $webhooksetting->id . '/edit') }}"
                                                                        data-ajax-popup="true" data-bs-toggle="tooltip"
                                                                        title="{{ __('Edit') }}"
                                                                        data-title="{{ __('Webhook Edit') }}">
                                                                        <i class="ti ti-pencil text-white"></i>
                                                                    </a>
                                                                </div>
                                                            @endcan
                                                            @can('delete webhook')
                                                                <div class="action-btn ">
                                                                    {!! Form::open([
                                                                        'method' => 'DELETE',
                                                                        'route' => ['webhook.destroy', $webhooksetting->id],
                                                                        'id' => 'delete-form-' . $webhooksetting->id,
                                                                    ]) !!}
                                                                    <a href="#"
                                                                        class="mx-3 btn btn-sm  align-items-center bs-pass-para bg-danger"
                                                                        data-bs-toggle="tooltip"
                                                                        title="{{ __('Delete') }}">
                                                                        <i class="ti ti-trash text-white text-white"></i>
                                                                    </a>
                                                                    {!! Form::close() !!}
                                                                </div>
                                                            @endcan
                                                        </span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr class="text-center">
                                                    <td colspan="4">{{ __('No Data Found.!') }}</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="ip-restriction-settings" class="card mb-3">
                        <div class="col-md-12">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col-6">
                                        <h5 class="mb-2">{{ __('IP Restriction Settings') }}</h5>
                                    </div>
                                    @can('create webhook')
                                        <div class="col-6 text-end">
                                            <a data-size="md" data-url="{{ route('create.ip') }}" data-ajax-popup="true"
                                                data-bs-toggle="tooltip" title="{{ __('Create') }}"
                                                data-title="{{ __('Create New IP') }}" class="btn btn-sm btn-primary">
                                                <i class="ti ti-plus text-white"></i>
                                            </a>

                                        </div>
                                    @endcan
                                </div>
                            </div>
                            <div class="card-body table-border-style">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th class="w-75">{{ __('IP') }}</th>
                                                <th>{{ __('Action') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="font-style">
                                            @forelse ($ips as $ip)
                                                <tr>
                                                    <td>{{ $ip->ip }}</td>

                                                    <td class="Action">
                                                        <span>
                                                            @can('edit webhook')
                                                                <div class="action-btn me-2">
                                                                    <a class="mx-3 btn btn-sm align-items-center bg-info"
                                                                        data-url="{{ route('edit.ip', $ip->id) }}"
                                                                        data-ajax-popup="true" data-bs-toggle="tooltip"
                                                                        title="{{ __('Edit') }}"
                                                                        data-title="{{ __('Edit IP') }}">
                                                                        <i class="ti ti-pencil text-white"></i>
                                                                    </a>
                                                                </div>
                                                            @endcan
                                                            @can('delete webhook')
                                                                <div class="action-btn ">
                                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['destroy.ip', $ip->id], 'id' => 'delete-form-' . $ip->id]) !!}
                                                                    <a class="mx-3 btn btn-sm  align-items-center bs-pass-para bg-danger"
                                                                        data-bs-toggle="tooltip"
                                                                        title="{{ __('Delete') }}">
                                                                        <i class="ti ti-trash text-white text-white"></i>
                                                                    </a>
                                                                    {!! Form::close() !!}
                                                                </div>
                                                            @endcan
                                                        </span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr class="text-center">
                                                    <td colspan="4">{{ __('No Data Found.!') }}</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
