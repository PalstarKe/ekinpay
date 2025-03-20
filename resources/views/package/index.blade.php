@extends('layouts/layoutMaster')
@section('page-title')
    {{__('Manage Packages')}}
@endsection

@section('content')
    <div class="row">

        <div class="col-md-12">
            <div class="nav-align-top mb-6">
                <ul class="nav nav-pills mb-4 nav-fill col-xl-4" role="tablist">
                    <li class="nav-item mb-1 mb-sm-0">
                        <button type="button" class="nav-link active btn-sm" role="tab" data-bs-toggle="tab" 
                            data-bs-target="#navs-pills-justified-pppoe" aria-controls="navs-pills-justified-pppoe" aria-selected="true">
                            <span class="d-none d-sm-block"><i class="tf-icons ti ti-network ti-sm me-1_5 align-text-bottom"></i> PPPoE
                                <span class="">({{ $pppoePackages->count() }})</span>
                            </span>
                            <i class="ti ti-network ti-sm d-sm-none"></i>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link btn btn-sm" role="tab" data-bs-toggle="tab" 
                            data-bs-target="#navs-pills-justified-hotspot" aria-controls="navs-pills-justified-hotspot" aria-selected="false">
                            <span class="d-none d-sm-block"><i class="tf-icons ti ti-wifi ti-sm me-1_5 align-text-bottom"></i> Hotspot
                                <span class="">({{ $hotspotPackages->count() }})</span>
                            </span>
                            <i class="ti ti-wifi ti-sm d-sm-none"></i>
                        </button>
                    </li>
                </ul>
                
                <div class="card">
                    <div class="card-header">
                        <div class="float-end d-flex">
                            @can('create package')
                                <a href="#" data-size="md" data-url="{{ route('packages.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Create')}}" data-title="{{__('Create Package')}}" class="btn btn-sm btn-primary me-2">
                                    <i class="ti ti-plus"></i> {{__('Create Package')}}
                                </a>
                            @endcan
                        </div>
                    </div>
                    <div class="card-body table-border-style mt-0">
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="navs-pills-justified-pppoe" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table ">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Name')}}</th>
                                                <th>{{ __('Bandwidth')}}</th>
                                                <th>{{ __('Price')}}</th>
                                                <th>{{ __('Validity')}}</th>
                                                <th>{{ __('Type')}}</th>
                                                <th>{{ __('Status')}}</th>
                                                @if (Gate::check('edit package') || Gate::check('delete package') || Gate::check('show package'))
                                                    <th>{{ __('Action') }}</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($pppoePackages as $package)
                                                <tr>
                                                    <td>{{ $package['name_plan'] }}</td>
                                                    <td>
                                                        {{ $package->bandwidth->rate_down }}{{ $package->bandwidth->rate_down_unit }} /
                                                        {{ $package->bandwidth->rate_up }}{{ $package->bandwidth->rate_up_unit }}
                                                    </td>
                                                    <td>{{ isset($admin_payment_setting['currency_symbol']) ? $admin_payment_setting['currency_symbol'] : 'Ksh' }} {{ $package['price']}}</td>
                                                    <td>{{ $package->validity }} {{ $package->validity_unit }}</td>
                                                    <td><span class="badge bg-label-info">{{ $package->device }}</span></td>
                                                    <td>
                                                        @if($package->status == 'Active')
                                                            <span class="badge bg-label-success">Active</span>
                                                        @else
                                                            <span class="badge bg-label-warning">Inactive</span>
                                                        @endif
                                                    </td>
                                                    @if (Gate::check('edit package') || Gate::check('delete package') || Gate::check('show package'))
                                                        <td class="Action">
                                                            <span>
                                                                @can('show package')
                                                                    <a href="{{ route('packages.show', \Crypt::encrypt($package['id'])) }}" class=""
                                                                    data-bs-toggle="tooltip" title="{{ __('View package') }}">
                                                                        <i class="ti ti-eye text-white"></i>
                                                                    </a>
                                                                @endcan
                                                                @can('edit package')
                                                                    <a href="#" class="" data-url="{{ route('packages.edit',$package['id']) }}" data-ajax-popup="true"  data-size="sm" data-bs-toggle="tooltip" title="{{__('Edit')}}"  data-title="{{ __('Edit package') }}">
                                                                        <i class="ti ti-pencil text-white"></i>
                                                                    </a>
                                                                @endcan
                                                                @if ($package->status == 'Inactive')
                                                                    @can('delete package')
                                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['packages.destroy', $package['id']], 'id' => 'delete-form-' . $package['id'], 'style' => 'display:inline']) !!}
                                                                            <a href="#" class="" data-bs-toggle="tooltip" title="{{ __('Delete package') }}"
                                                                            data-confirm="{{ __('Are you sure you want to delete this package?') }}"
                                                                            data-confirm-yes="document.getElementById('delete-form-{{ $package->id }}').submit();">
                                                                                <i class="ti ti-trash text-white"></i>
                                                                            </a>
                                                                        {!! Form::close() !!}
                                                                    @endcan
                                                                @endif
                                                            </span>
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="navs-pills-justified-hotspot" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Name')}}</th>
                                                <th>{{ __('Bandwidth')}}</th>
                                                <th>{{ __('Price')}}</th>
                                                <th>{{ __('Validity')}}</th>
                                                <th>{{ __('Type')}}</th>
                                                <th>{{ __('Status')}}</th>
                                                @if (Gate::check('edit package') || Gate::check('delete package') || Gate::check('show package'))
                                                    <th>{{ __('Action') }}</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($hotspotPackages as $package)
                                                <tr>
                                                    <td>{{ $package['name_plan'] }}</td>
                                                    <td>
                                                        {{ $package->bandwidth->rate_down }}{{ $package->bandwidth->rate_down_unit }} /
                                                        {{ $package->bandwidth->rate_up }}{{ $package->bandwidth->rate_up_unit }}
                                                    </td>
                                                    <td>{{ isset($admin_payment_setting['currency_symbol']) ? $admin_payment_setting['currency_symbol'] : 'Ksh' }} {{ $package['price']}}</td>
                                                    <td>{{ $package->validity }} {{ $package->validity_unit }}</td>
                                                    <td><span class="">{{ $package->device }}</span></td>
                                                    <td>
                                                        @if($package->status == 'Active')
                                                            <span class="badge bg-success">Active</span>
                                                        @else
                                                            <span class="badge bg-warning">Inactive</span>
                                                        @endif
                                                    </td>
                                                    @if (Gate::check('edit package') || Gate::check('delete package') || Gate::check('show package'))
                                                        <td class="Action">
                                                            <span>
                                                                @can('show package')
                                                                    <a href="{{ route('packages.show', \Crypt::encrypt($package['id'])) }}" class=""
                                                                    data-bs-toggle="tooltip" title="{{ __('View package') }}">
                                                                        <i class="ti ti-eye text-white"></i>
                                                                    </a>
                                                                @endcan
                                                                @can('edit package')
                                                                    <a href="#" class="" data-url="{{ route('packages.edit',$package['id']) }}" data-ajax-popup="true"  data-size="sm" data-bs-toggle="tooltip" title="{{__('Edit')}}"  data-title="{{ __('Edit package') }}">
                                                                        <i class="ti ti-pencil text-white"></i>
                                                                    </a>
                                                                @endcan
                                                                @if ($package->status == 'Inactive')
                                                                    @can('delete package')
                                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['packages.destroy', $package['id']], 'id' => 'delete-form-' . $package['id'], 'style' => 'display:inline']) !!}
                                                                            <a href="#" class="" data-bs-toggle="tooltip" title="{{ __('Delete package') }}"
                                                                            data-confirm="{{ __('Are you sure you want to delete this package?') }}"
                                                                            data-confirm-yes="document.getElementById('delete-form-{{ $package->id }}').submit();">
                                                                                <i class="ti ti-trash text-white"></i>
                                                                            </a>
                                                                        {!! Form::close() !!}
                                                                    @endcan
                                                                @endif
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
            </div>
        </div>
    </div>
@endsection
@push('script-page')
    <script>
        $(document).on('change', '#burst_input', function() {
            if ($(this).is(':checked')) {
                $('.burst_div').removeClass('d-none');
                // $('#trial_days').attr("required", true);

            } else {
                $('.burst_div').addClass('d-none');
                // $('#trial_days').removeAttr("required");
            }
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let burstLimit = document.querySelector("input[name='burst_limit']");
            let burstThreshold = document.querySelector("input[name='burst_threshold']");
            let burstTime = document.querySelector("input[name='burst_time']");
            let burstPriority = document.querySelector("input[name='burst_priority']");
            let burstLimitAt = document.querySelector("input[name='burst_limit_at']");
            let burstCombined = document.getElementById("burst_combined");
            let enableBurstCheckbox = document.getElementById("enable_burst");
            let burstInputDiv = document.getElementById("burst_input");

            function updateBurstValue() {
                burstCombined.value = 
                    (burstLimit.value || "0") + " " + 
                    (burstThreshold.value || "0") + " " + 
                    (burstTime.value || "0") + " " + 
                    (burstPriority.value || "0") + " " + 
                    (burstLimitAt.value || "0");
            }

            function toggleBurstInputs() {
                if (enableBurstCheckbox.checked) {
                    burstInputDiv.style.display = "block";
                } else {
                    burstInputDiv.style.display = "none";
                }
            }

            // Initialize burst values on load
            updateBurstValue();
            toggleBurstInputs();

            // Event listeners
            burstLimit.addEventListener("input", updateBurstValue);
            burstThreshold.addEventListener("input", updateBurstValue);
            burstTime.addEventListener("input", updateBurstValue);
            burstPriority.addEventListener("input", updateBurstValue);
            burstLimitAt.addEventListener("input", updateBurstValue);
            enableBurstCheckbox.addEventListener("change", toggleBurstInputs);
        });
    </script>
@endpush