@extends('layouts/layoutMaster')
@section('page-title')
    {{__('Manage sites')}}
@endsection

@section('content')
    <div class="row">
        <div class="float-end d-flex  mb-3">
            @can('create nas')
                <a href="#" data-size="sm" data-url="{{ route('nas.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Create')}}" data-title="{{__('Create Site')}}" class="btn btn-sm btn-primary me-2">
                    <i class="ti ti-plus"></i> {{__('Create Site')}}
                </a>
            @endcan
        </div>

            <div class="col-md-12">
                <div class="card">
                    <div class="card-body table-border-style">
                        <h5></h5>
                        <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Site Name') }}</th>
                                    <th>{{ __('IP Address') }}</th>
                                    <th>{{ __('Secret') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    @if (Gate::check('edit nas') || Gate::check('delete nas') || Gate::check('show nas'))
                                        <th>{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($nases as $k=>$nas)
                                    <tr>
                                        <td>{{$nas->shortname}}</td>
                                        <td>{{$nas->nasname}}</td>
                                        <td>{{ $nas['secret']}}</td>
                                        <td>
                                            @if ($nas['nasapi'] == 1)
                                                <span >{{ __('API') }}</span>
                                            @else
                                                <span >{{ __('Radius') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($nas->status == 'Online')
                                                <span class="badge bg-label-success">Online</span>
                                            @else
                                                <span class="badge bg-label-warning">Offline</span>
                                            @endif
                                        </td>
                                        @if (Gate::check('edit nas') || Gate::check('delete nas') || Gate::check('show nas'))
                                            <td class="Action">
                                                <span>
                                                    @can('show nas')
                                                        <a href="{{ route('nas.show', \Crypt::encrypt($nas['id'])) }}" 
                                                        data-bs-toggle="tooltip" title="{{ __('View NAS') }}">
                                                            <i class="ti ti-eye text-white"></i>
                                                        </a>
                                                    @endcan
                                                    {{--@can('edit nas')
                                                        <a href="#" class="mx-3 btn btn-sm  align-items-center bg-info" data-url="{{ route('nas.edit',$nas['id']) }}" data-ajax-popup="true"  data-size="sm" data-bs-toggle="tooltip" title="{{__('Edit')}}"  data-title="{{ __('Edit NAS') }}">
                                                            <i class="ti ti-pencil text-white"></i>
                                                        </a>
                                                    @endcan
                                                    @can('delete nas')
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['nas.destroy', $nas['id']], 'id' => 'delete-form-' . $nas['id'], 'style' => 'display:inline']) !!}
                                                            <a href="#" class="btn btn-sm bg-danger" data-bs-toggle="tooltip" title="{{ __('Delete NAS') }}"
                                                            data-confirm="{{ __('Are you sure you want to delete this NAS?') }}"
                                                            data-confirm-yes="document.getElementById('delete-form-{{ $nas->id }}').submit();">
                                                                <i class="ti ti-trash text-white"></i>
                                                            </a>
                                                        {!! Form::close() !!}
                                                    @endcan--}}
                                                </span>
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
    <script>
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll("td[id^='nas-status-']").forEach((td) => {
        setTimeout(() => {
            td.classList.add("animate-pulse"); // Add animation effect
        }, 1000);
    });
});


    </script>
@endpush