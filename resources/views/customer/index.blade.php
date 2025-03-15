@extends('layouts/layoutMaster')
@php
   // $profile=asset(Storage::url('uploads/avatar/'));
$profile=\App\Models\Utility::get_file('uploads/avatar/');
@endphp

@section('page-title')
    {{__('Manage Customers')}}
@endsection

@section('content')

    <div class="row g-2">
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">Total Users</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $customers->count() }}</h4>
                            </div>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="ti ti-users ti-26px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">Active Users</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $actcustomers->count() }}</h4>
                            </div>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-danger">
                                <i class="ti ti-user-plus ti-26px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">Suspended Users</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $suscustomers->count() }}</h4>
                            </div>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="ti ti-user-check ti-26px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">Expired Users</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $expcustomers->count() }}</h4>
                            </div>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-warning">
                                <i class="ti ti-user-search ti-26px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="float-end d-flex">
            <a href="#" data-size="md"  data-bs-toggle="tooltip" title="{{__('Import')}}" data-url="{{ route('customer.file.import') }}" data-ajax-popup="true" data-title="{{__('Import customer CSV file')}}" class="btn btn-sm btn-info me-2">
                <i class="ti ti-file-import"></i> {{__('Import customer CSV file')}}
            </a>
            <a href="{{route('customer.export')}}" data-bs-toggle="tooltip" title="{{__('Export')}}" class="btn btn-sm btn-secondary me-2">
                <i class="ti ti-file-export"></i> {{__('Export')}}
            </a>

            <a href="#" data-size="sm" data-url="{{ route('customer.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Create')}}" data-title="{{__('Create Customer')}}" class="btn btn-sm btn-primary me-2">
                <i class="ti ti-plus"></i> {{__('Create Customer')}}
            </a>
            {{--<a href="#" class="btn btn-sm btn-primary me-2" data-bs-toggle="tooltip" title="{{__('Create')}}" data-offcanvas="true" data-title="{{__('Create Customer')}}" data-url="{{ route('customer.create') }}">
                <i class="ti ti-plus"></i> {{__('Create Customer')}}
            </a>--}}
        </div>
        {{--<div class="col-sm-12">
            <div class="mt-2 mb-3" id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        {{ Form::open(['route' => ['customer.index'], 'method' => 'GET', 'id' => 'customer_submit']) }}
                        <div class="row d-flex align-items-center justify-content-end g-2">

                            <!-- Name Search -->
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('name', __('Customer Name'), ['class' => 'form-label']) }}
                                    {{ Form::text('name', request()->name, ['class' => 'form-control', 'placeholder' => __('Enter customer name')]) }}
                                </div>
                            </div>

                            <!-- Email Search -->
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('email', __('Customer Email'), ['class' => 'form-label']) }}
                                    {{ Form::text('email', request()->email, ['class' => 'form-control', 'placeholder' => __('Enter customer email')]) }}
                                </div>
                            </div>

                            <!-- Phone Search -->
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('phone', __('Customer Phone'), ['class' => 'form-label']) }}
                                    {{ Form::text('phone', request()->phone, ['class' => 'form-control', 'placeholder' => __('Enter phone number')]) }}
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="col-auto float-end ms-2 mt-8">
                                <!-- Search Button -->
                                <a href="#" class="btn btn-md btn-primary me-1"
                                onclick="document.getElementById('customer_submit').submit(); return false;"
                                data-bs-toggle="tooltip" data-bs-original-title="{{ __('Apply') }}">
                                    <span class="btn-inner--icon"><i class="ti ti-search"></i> Search</span>
                                </a>
                                <!-- Reset Button -->
                                <a href="{{ route('customer.index') }}" class="btn btn-md btn-danger" data-bs-toggle="tooltip"
                                data-bs-original-title="{{ __('Reset') }}">
                                    <span class="btn-inner--icon"><i class="ti ti-refresh text-white-off"></i> {{ __('Reset') }}</span>
                                </a>
                            </div>

                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>--}}
        <div class="col-md-12">
            <div class="nav-align-top mb-6">
                <ul class="nav nav-pills mb-4 nav-fill col-xl-4" role="tablist">
                    <li class="nav-item mb-1 mb-sm-0">
                        <button type="button" class="nav-link active btn-sm" role="tab" data-bs-toggle="tab" 
                            data-bs-target="#navs-pills-justified-pppoe" aria-controls="navs-pills-justified-pppoe" aria-selected="true">
                            <span class="d-none d-sm-block"><i class="tf-icons ti ti-network ti-sm me-1_5 align-text-bottom"></i> PPPoE
                                <span class="">({{ $pppoeCustomers->count() }})</span>
                            </span>
                            <i class="ti ti-network ti-sm d-sm-none"></i>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link btn btn-sm" role="tab" data-bs-toggle="tab" 
                            data-bs-target="#navs-pills-justified-hotspot" aria-controls="navs-pills-justified-hotspot" aria-selected="false">
                            <span class="d-none d-sm-block"><i class="tf-icons ti ti-wifi ti-sm me-1_5 align-text-bottom"></i> Hotspot
                                <span class="">({{ $hotspotCustomers->count() }})</span>
                            </span>
                            <i class="ti ti-wifi ti-sm d-sm-none"></i>
                        </button>
                    </li>
                </ul>
                <div class="card">
                    <div class="card-body table-border-style">
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="navs-pills-justified-pppoe" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table datatable">
                                        <thead>
                                        <tr>
                                            <!-- <th>#</th> -->
                                            <th>{{__('Fullname')}}</th>
                                            <th>{{__('Username')}}</th>
                                            <th>{{__('Phone')}}</th>
                                            <th>{{__('Plan')}}</th>
                                            <th>{{__('Status')}}</th>
                                            <th>{{__('Online')}}</th>
                                            <th>{{__('Location')}}</th>
                                            <th>{{__('Expiry')}}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach ($pppoeCustomers as $k=>$customer)
                                            <tr class="cust_tr" id="cust_detail" data-url="{{route('customer.show',$customer['id'])}}" data-id="{{$customer['id']}}">
                                                {{--<td class="Id">
                                                    <!-- @can('show customer')
                                                        <a href="{{ route('customer.show',\Crypt::encrypt($customer['id'])) }}" class="btn btn-outline-primary"> -->
                                                            {{ AUth::user()->customerNumberFormat($customer['customer_id']) }}
                                                        <!-- </a>
                                                    @else -->
                                                        <!-- <a href="#" class="btn btn-outline-primary"> -->
                                                            <!-- {{ AUth::user()->customerNumberFormat($customer['customer_id']) }} -->
                                                        <!-- </a> -->
                                                    <!-- @endcan -->
                                                </td>--}}
                                                <td class="font-style">{{$customer['fullname']}}</td>
                                                <td class="font-style">
                                                    @can('show customer')
                                                        <a href="{{ route('customer.show',\Crypt::encrypt($customer['id'])) }}" class="">
                                                            {{$customer['account']}}
                                                        </a>
                                                    @endcan
                                                </td>
                                                <td>{{$customer['contact']}}</td>
                                                <td>
                                                    @if($customer->package == '')
                                                        <span class="badge bg-label-secondary">N/A</span>
                                                    @else
                                                        <span class="">{{$customer['package']}}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($customer->is_active == 1)
                                                        <span class="">Active</span>
                                                    @else
                                                        <span class="">Expired</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($customer->online)
                                                        <span class="badge bg-label-success">Online</span>
                                                    @else
                                                        <span class="badge bg-label-warning">Offline</span>
                                                    @endif
                                                </td>
                                                <td>{{$customer['location']}}</td>
                                                <td>{{$customer['expiry']}}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="navs-pills-justified-hotspot" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table datatabl">
                                        <thead>
                                        <tr>
                                            <!-- <th>#</th> -->
                                            <th>{{__('Fullname')}}</th>
                                            <th>{{__('Username')}}</th>
                                            <th>{{__('Phone')}}</th>
                                            <th>{{__('Plan')}}</th>
                                            <th>{{__('Status')}}</th>
                                            <th>{{__('Online')}}</th>
                                            <th>{{__('Location')}}</th>
                                            <th>{{__('Expiry')}}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($hotspotCustomers as $k=>$customer)
                                                <tr class="cust_tr" id="cust_detail" data-url="{{route('customer.show',$customer['id'])}}" data-id="{{$customer['id']}}">
                                                    <td class="font-style">{{$customer['fullname']}}</td>
                                                    <td class="font-style">
                                                        @can('show customer')
                                                            <a href="{{ route('customer.show',\Crypt::encrypt($customer['id'])) }}" class="">
                                                                {{$customer['account']}}
                                                            </a>
                                                        @endcan
                                                    </td>
                                                    <td>{{$customer['contact']}}</td>
                                                    <td>
                                                        @if($customer->package == '')
                                                            <span class="badge bg-label-secondary">N/A</span>
                                                        @else
                                                            <span class="">{{$customer['package']}}</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($customer->is_active == 1)
                                                            <span class="">Active</span>
                                                        @else
                                                            <span class="">Inactive</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($customer->online)
                                                            <span class="badge bg-label-success">Online</span>
                                                        @else
                                                            <span class="badge bg-label-warning">Offline</span>
                                                        @endif
                                                    </td>
                                                    <td>{{$customer['location']}}</td>
                                                    <td>{{$customer['expiry']}}</td>
                                                </tr>
                                            @endforeach
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
@push('script-page')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

<script>
    new DataTable('table.datatabl', {
        fixedHeader: {
            header: true,
            footer: true
        }
    });
</script>
@endpush
