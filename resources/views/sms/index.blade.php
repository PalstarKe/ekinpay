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
                        @can('send bulk sms')
                            <a href="#" data-size="md" data-url="{{ route('sms.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Bulk Sms')}}" data-title="{{__('Send Bulk SMS')}}" class="btn btn-sm btn-primary me-2">
                                <i class="ti ti-send"></i> {{__('Send Bulk SMS')}}
                            </a>
                        @endcan
                        @can('create sms template')
                            <a href="#" data-size="md" data-url="{{ route('sms.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Create')}}" data-title="{{__('Create SMS Template')}}" class="btn btn-sm btn-primary me-2">
                                <i class="ti ti-plus"></i> {{__('Create SMS Template')}}
                            </a>
                        @endcan
                    </div>
                </div>
                <div class="card-body table-border-style mt-0">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Name')}}</th>
                                    <th>{{ __('Template')}}</th>
                                    <th>{{ __('Status')}}</th>
                                    @if (Gate::check('edit sms template') || Gate::check('delete sms template') || Gate::check('show sms template'))
                                        <th>{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($smsTemplates as $sms)
                                    <tr>
                                        <td>{{ $sms->type }}</td>
                                        <td>{{ $sms->template }}</td>
                                        <td>
                                            @if($sms->status == '1')
                                                <span class="badge bg-label-success">Active</span>
                                            @else
                                                <span class="badge bg-label-warning">Inactive</span>
                                            @endif
                                        </td>
                                        @if (Gate::check('edit sms template') || Gate::check('delete sms template'))
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
                                        @endif
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