@extends('layouts/layoutMaster')
@section('page-title')
    {{__('Manage Packages')}}
@endsection

@section('content')
    <div class="row">

        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="float-end d-flex">
                       {{-- @can('send bulk sms')
                        <a href="#" data-size="md" data-url="{{ route('sms.bulk.form') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Bulk Sms')}}" data-title="{{__('Send Bulk SMS')}}" class="btn btn-sm btn-primary me-2">
                            <i class="ti ti-send"></i> {{__('Send Bulk SMS')}}
                        </a>
                        @endcan--}}
                    </div>
                </div>
                <div class="card-body table-border-style mt-0">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Template')}}</th>
                                    <th>{{ __('Sent On')}}</th>
                                    <th>{{ __('Sent To')}}</th>
                                    <th>{{ __('Message')}}</th>
                                    <th>{{ __('Deliverly')}}</th>
                                    <th>{{ __('Customer Name')}}</th>
                                    {{--@if (Gate::check('edit sms template') || Gate::check('delete sms template') || Gate::check('show sms template'))
                                        <th>{{ __('Action') }}</th>
                                    @endif--}}
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($SmsDelivered as $sms)
                                    <tr>
                                        <td>{{ $sms->smsalert }}</td>
                                        <td>{{ $sms->datetime }}</td>
                                        <td>{{ $sms->destination }}</td>
                                        <td>{{ $sms->message }}</td>
                                        <td>
                                            @if($sms->sms_api_response == '200')
                                                <span class="badge bg-label-success">Success</span>
                                            @else
                                                <span class="badge bg-label-warning">Failed</span>
                                            @endif
                                        </td>
                                        <td>{{ $sms->userid }}</td>
                                        {{--@if (Gate::check('edit sms template') || Gate::check('delete sms template'))
                                        <td class="Action">
                                            <div class="d-flex gap-2">
                                                @can('edit sms template')
                                                    <a title="{{ __('Edit Template') }}" href="#" class="badge bg-label-primary btn-icon btn-sm"
                                                        data-url="{{ route('sms.edit', $sms->id) }}" data-ajax-popup="true"
                                                        data-title="{{ __('Edit Template') }}" data-size="md"
                                                        data-bs-toggle="tooltip" data-original-title="{{ __('Edit') }}">
                                                        <i class="ti ti-edit"></i>
                                                    </a>
                                                @endcan
                                                @can('delete sms template')
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['sms.destroy', $sms->id], 'id' => 'delete-form-' . $sms->id, 'style' => 'display:inline']) !!}
                                                        <a href="#" class="delete-btn badge bg-label-warning btn-icon btn-sm"
                                                            data-bs-toggle="tooltip" title="{{ __('Delete template') }}"
                                                            data-confirm="{{ __('Are you sure you want to delete this template?') }}"
                                                            data-id="{{ $sms->id }}"
                                                            onclick="confirmDelete(event, {{ $sms->id }})">
                                                            <i class="ti ti-trash"></i>
                                                        </a> 
                                                    {!! Form::close() !!}
                                                @endcan
                                            </div>
                                        </td>
                                        @endif--}}
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('script-page')
    
@endpush