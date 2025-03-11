@extends('layouts/layoutMaster')
@push('script-page')
@endpush
@section('page-title')
    {{__('Manage Customer-Detail')}}
@endsection

@push('script-page')
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            flatpickr("#flatpickr-container", {
                enableTime: true,
                dateFormat: "Y-m-d H:i",
                inline: true, // Makes the calendar always visible
                onChange: function(selectedDates, dateStr) {
                    document.getElementById("expiry-input").value = dateStr; // Update hidden input
                }
            });
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            flatpickr("#flatpickr-container-update", {
                enableTime: true,
                dateFormat: "Y-m-d H:i",
                inline: true, // Makes the calendar always visible inside the modal
                onChange: function(selectedDates, dateStr) {
                    document.getElementById("expiry-input-update").value = dateStr; // Update hidden input
                }
            });
        });
    </script>
    <script>
        function copyToClipboard(element) {

            var copyText = element.id;
            navigator.clipboard.writeText(copyText);
            // document.addEventListener('copy', function (e) {
            //     e.clipboardData.setData('text/plain', copyText);
            //     e.preventDefault();
            // }, true);
            //
            // document.execCommand('copy');
            show_toastr('success', 'Url copied to clipboard', 'success');
        }
        
        function updateChart() {
            $.ajax({
                url: '/customer/{{ $customer->username }}/live-usage',
                method: 'GET',
                success: function(response) {
                    // Ensure chart updates only when valid data is received
                    if (response.timestamp && response.download !== undefined && response.upload !== undefined) {
                        let existingLabels = liveUsageChart.w.globals.labels;
                        let existingDownloads = liveUsageChart.w.globals.series[0].data;
                        let existingUploads = liveUsageChart.w.globals.series[1].data;

                        // Limit the number of data points (max 10)
                        if (existingLabels.length >= 10) {
                            existingLabels.shift();
                            existingDownloads.shift();
                            existingUploads.shift();
                        }

                        // Append new data
                        existingLabels.push(response.timestamp);
                        existingDownloads.push(response.download);
                        existingUploads.push(response.upload);

                        // Update chart with new data
                        liveUsageChart.updateOptions({
                            xaxis: { categories: existingLabels },
                            series: [
                                { name: 'Downloads', data: existingDownloads },
                                { name: 'Uploads', data: existingUploads }
                            ]
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching live usage data:', error);
                }
            });
        }

        // Fetch new data every 5 seconds
        setInterval(updateChart, 5000);
        updateChart(); // Initial call


        $(document).ready(function () {
            let cardColor, labelColor, headingColor, borderColor, legendColor;

            if (isDarkStyle) {
                cardColor = config.colors_dark.cardColor;
                labelColor = config.colors_dark.textMuted;
                legendColor = config.colors_dark.bodyColor;
                headingColor = config.colors_dark.headingColor;
                borderColor = config.colors_dark.borderColor;
            } else {
                cardColor = config.colors.cardColor;
                labelColor = config.colors.textMuted;
                legendColor = config.colors.bodyColor;
                headingColor = config.colors.headingColor;
                borderColor = config.colors.borderColor;
            }

        // Total Revenue Report Budget Line Chart
        const liveUsageChartEl = document.querySelector('#liveUsageChart'),
        liveUsageChartOptions = {
            chart: {
                height: 240,
                toolbar: { show: false },
                zoom: { enabled: false },
                type: 'line'
            },
            series: [
                {
                name: 'Downloads',
                data: []
                },
                {
                name: 'Uploads',
                data: []
                }
            ],
            stroke: {
                curve: 'smooth',
                dashArray: [5, 0],
                width: [1, 2]
            },
            legend: {
                show: false
            },
            colors: [borderColor, config.colors.primary],
            grid: {
                show: false,
                borderColor: borderColor,
                padding: {
                // top: -30,
                bottom: -15,
                left: 25
                }
            },
            markers: {
                size: 0
            },
            xaxis: {
                labels: {
                    show: true,
                    rotate: -45, 
                    style: { 
                        colors: labelColor,
                        fontSize: '12px' 
                    },
                    formatter: function(value) {
                        return value ? new Date(value).toLocaleTimeString() : 'N/A'; 
                    }
                },
                axisTicks: {
                show: true
                },
                axisBorder: {
                show: true
                }
            },
            yaxis: {
                labels: {
                    show: true,
                    style: { 
                        colors: labelColor, 
                        fontSize: '12px' 
                    },
                    formatter: function(value) {
                        return value + " Mbps"; 
                    }
                },
                axisTicks: {
                show: true
                },
                axisBorder: {
                show: true
                }
            },
            tooltip: {
                enabled: false
            }
            };
        if (typeof liveUsageChartEl !== undefined && liveUsageChartEl !== null) {
            const liveUsageChart = new ApexCharts(liveUsageChartEl, liveUsageChartOptions);
            liveUsageChart.render();
        }

        // Total Data Usage Chart - Bar Chart
        // --------------------------------------------------------------------
        const dataUsageChartEl = document.querySelector('#dataUsageChart'),
        dataUsageChartOptions = {
            series: [
                {
                    name: 'Download',
                    data: []
                },
                {
                    name: 'Upload',
                    data: []
                }
            ],
            chart: {
                height: 250,
                parentHeightOffset: 0,
                stacked: false, 
                type: 'bar',
                toolbar: { show: false }
            },
            tooltip: {
                enabled: true
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '60%', 
                    borderRadius: 9,
                    startingShape: 'rounded',
                    endingShape: 'rounded'
                }
            },
            colors: [config.colors.primary, config.colors.warning],
            dataLabels: {
                enabled: false
            },
            stroke: {
                width: 0 
            },
            legend: {
                show: true,
                horizontalAlign: 'right',
                position: 'top',
                fontSize: '13px',
                fontFamily: 'Public Sans'
            },
            grid: {
                show: false
            },
            xaxis: {
                categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                labels: {
                    style: {
                        fontSize: '13px',
                        colors: labelColor,
                        fontFamily: 'Public Sans'
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        fontSize: '13px',
                        colors: labelColor,
                        fontFamily: 'Public Sans'
                    },
                    formatter: function(value) {
                        return value + " Mbps"; 
                    }
                }
            }
        };

        if (typeof dataUsageChartEl !== undefined && dataUsageChartEl !== null) {
            const dataUsageChart = new ApexCharts(dataUsageChartEl, dataUsageChartOptions);
            dataUsageChart.render();
        }

    })    

    </script>
@endpush

@section('content') 
<div class="row g-3">  
    <div class="col-sm-12 col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">{{ $customer->fullname ?? 'N/A' }}</h3>
                <div class="d-flex flex-column text-start">
                    <a href="#" class="text-info">MAC: {{ $customer['mac_address'] ?? 'N/A' }}</a>
                    <a href="#" class="text-info">IP: {{ optional($session)->ip ?? 'N/A' }}</a>
                    <a href="#" class="text-info">Device: {{ $deviceVendor }}</a>
                </div>
            </div>
            <div class="card-body">
                <div class="profile-blog">
                    <li class="header-profile d-flex align-items-start">
                        <div class="row w-100 g-0">
                            <div class="col-md-4 d-flex align-items-start gap-2">
                                <div class="avatar {{ $online ? 'avatar-online' : '' }}">
                                    <img src="https://robohash.org/{{$customer['id']}}?set=set3&size=100x100&bgset=bg1" 
                                        width="60" alt="Profile" class="rounded-circle">
                                </div>
                                <div class="header-info" style="cursor: pointer;">
                                    <a data-bs-toggle="offcanvas" data-bs-target="#offcanvasEnd" aria-controls="offcanvasEnd">
                                        <span class="font-w600 {{ $online ? 'text-success' : 'text-danger' }}">
                                            <b>{{$customer['username']}}</b>
                                        </span>
                                    </a>
                                </div>
                            </div>

                            <div class="col-md-8 d-flex justify-content-start align-items-center gap-2">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar">
                                        <div class="avatar-initial bg-label-primary rounded">
                                            <i class="ti ti-brand-cashapp ti-md"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <h5 class="mb-0">{{$customer['balance']}}</h5>
                                        <span>Balance</span>
                                    </div>
                                </div>
                                <div>
                                    <button class="btn bg-label-primary btn-md" data-bs-toggle="modal" data-bs-target="#useBalance">
                                        Use Balance
                                    </button>
                                </div>
                                <div>
                                    <!-- <button class="btn bg-label-warning btn-md" data-bs-toggle="modal" data-bs-target="#">
                                       Deactivate
                                    </button> -->
                                    @if( $customer->is_active == 1)
                                        <form action="{{ route('customer.deactivate', $customer->id) }}" method="POST">
                                            @csrf
                                            @method('POST') 
                                            <button type="submit" class="btn bg-label-warning btn-md">Deactivate</button>
                                        </form>
                                    @else
                                        <form action="{{ route('customer.deactivate', $customer->id) }}" method="POST">
                                            @csrf
                                            @method('POST') 
                                            <button type="submit" class="btn bg-label-warning btn-md">Activate</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </li>
                    <div class="row">
                        <ul class="d mb-3 col-4">
                            <small class="font-w400">Service: {{$customer['service']}}</small><br>
                            <small class="font-w400">Old Username: {{$customer['username']}}</small><br>
                            <small class="font-w400">Status: @if($customer->is_active == 1)<span class="text-success">Active</span>@else<span class="badge bg-label-warning">Inactive</span>@endif</small><br>
                            <small class="font-w400">Location: {{$customer['location']}}</small><br>
                            <small class="font-w400">Created On: {{ \Carbon\Carbon::parse($customer['created_at'])->format('Y-m-d') }}</small><br>
                            <small class="font-w400">Phone No: {{$customer['contact']}}</small><br>
                            <small class="font-w400">Email: {{$customer['email']}}</small><br>
                        </ul>
                        <div class="card col-8">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Child Account</h5>
                                
                                <a href="#" class="btn btn-outline-primary btn-md ">Add Child Account</a>
                            </div>
                            <div class="table-responsive" >
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Username</th>
                                            <th>Status</th>
                                            <th>Shared</th>
                                            <th>Expiry</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-12 col-lg-4">
        <div class="card">
            <div class="card-body">
                <div class="profile-statistics">
                    <div class="">
                        <div class="row mb-3 g-2">
                            <div class="col-6">
                                <form action="{{ route('customer.refresh', $customer->id) }}" method="POST">
                                    @csrf
                                    @method('POST') 
                                    <button type="submit" onclick="return confirm('This will Refresh Customer Account?')" class="btn bg-label-success btn-md btn-block w-100">Refresh</button>
                                </form>
                            </div>
                            <div class="col-6">
                                <a href="javascript:extend('1672')" class="btn bg-label-success btn-md btn-block w-100" data-bs-toggle="modal" data-bs-target="#extendExp">Extend</a>
                            </div>
                        </div>
                        <div class="row mb-3 g-2">
                            <div class="col-6">
                                <button type="button" class="btn bg-label-primary btn-md btn-block w-100" data-bs-toggle="modal" data-bs-target="#depositCash">Deposit</button>
                            </div>
                            <div class="col-6">
                                <button class="btn bg-label-primary btn-md btn-block w-100" type="button" data-bs-toggle="modal" data-bs-target="#changePlan">Change Plan</button>
                            </div>
                        </div>
                        <div class="row mb-3 g-2">
                            <div class="col-6">
                                <form action="{{ route('customer.clearmac', $customer->id) }}" method="POST">
                                    @csrf
                                    @method('POST') 
                                    <button type="submit" class="btn bg-label-info btn-md btn-block w-100">Clear Mac</button>
                                </form>
                            </div>
                            <div class="col-6">
                                <a href="" class="btn bg-label-warning btn-md btn-block w-100">Send SMS</a>
                            </div>
                        </div>
                        <div class="row mb-3 g-2">
                            <div class="col-6">
                                <a href="" id="1672" class="btn bg-label-danger btn-md btn-block w-100">Pause</a>
                            </div>
                            <div class="col-6">
                                <a href="#" data-bs-toggle="modal" data-bs-target="#updateExp" class="btn bg-label-danger btn-md btn-block w-100">
                                    {{__('Edit Expiry')}}
                                </a>
                            </div>
                        </div>
                        <a href="" class="btn bg-label-info btn-md btn-block w-100 mb-3" data-bs-toggle="modal" data-bs-target="#resolvePay">Resolve Payment</a>
                        @can('create invoice')
                            <a href="{{ route('invoice.create', $customer->id) }}" class="btn bg-label-success btn-md btn-block w-100">
                                {{ __('Create Invoice') }}
                            </a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>
     <!-- Update Expiry Date -->
     <div class="modal fade" id="updateExp" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Expiry Date</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('customer.updateExpiry', $customer->id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <!-- <label class="form-label">Select Date & Time</label> -->
                            <div id="flatpickr-container-update"></div> <!-- Full calendar inside modal -->
                            <input type="hidden" name="expiry" id="expiry-input-update"> <!-- Hidden field -->
                        </div>
                        <button class="btn btn-primary w-100" type="submit">Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!--/ Update Expiry Date -->

    <!-- Extend Expiry Date -->
    <div class="modal fade" id="extendExp" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Extend Expiry Date</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('customer.updateExpiry', $customer->id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <!-- <label class="form-label">Select Date & Time</label> -->
                            <div id="flatpickr-container"></div> <!-- Full calendar inside modal -->
                            <input type="hidden" name="expiry" id="expiry-input"> <!-- Hidden field -->
                        </div>
                        <button class="btn btn-primary w-100" type="submit">Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!--/ Extend Expiry Date -->

     <!-- Deposit Balance -->
    <div class="modal fade" id="depositCash" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel2">Deposit Cash</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('customer.depositCash', $customer->id) }}" method="POST">
                        @csrf
                        <div class="col-12">
                            <label class="form-label" for="cash">Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">Ksh</span>
                                <input type="number" id="balance" name="balance" class="form-control" placeholder="1000" required min="1"/>
                            </div>
                        </div>
                        <div class="col-12 mt-2">
                            <button type="submit" class="btn btn-primary">Submit</button>
                            <button type="reset" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!--/ Deposit Balance -->

    <!-- Resolve Payment -->
    <div class="modal fade" id="resolvePay" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel2">Resolve Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('customer.depositCash', $customer->id) }}" method="POST">
                        @csrf
                        <div class="col-12">
                            <label class="form-label" for="resolve">Mpesa Code</label>
                            <input type="text" id="mpesacode" name="mpesacode" class="form-control" placeholder="MHT6GHJJ"/>
                        </div>
                        <div class="input-group mt-3">
                            <span class="input-group-text">Ksh</span>
                            <input type="number" id="amount" name="amount" class="form-control" placeholder="1000" required min="1"/>
                        </div>
                        <div class="col-12 mt-3">
                            <button type="submit" class="btn btn-primary">Submit</button>
                            <button type="reset" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!--/ Resolve Payment -->

    <!-- Use Balance -->
    <div class="modal fade" id="useBalance" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel2">Use Balance</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('customer.useBalance', $customer->id) }}" method="POST">
                        @csrf
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label" for="type">Payment For:</label>
                                <select id="type" class="form-select" name="type">
                                    <option value="installation">Installation Fee</option>
                                    <option value="package">Package Renewal</option>
                                </select>
                            </div>
                        </div>
                        <div class="input-group mt-3">
                            <span class="input-group-text">Ksh</span>
                            <input type="number" id="amount" name="amount" class="form-control" placeholder="1000" required min="1"/>
                        </div>
                        <div class="col-12 mt-3">
                            <button type="submit" class="btn btn-primary">Submit</button>
                            <button type="reset" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!--/ Use Balance -->
    <!-- Change Plan -->
    <div class="modal fade" id="changePlan" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel2">Change Plan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('customer.changePlan', $customer->id) }}" method="POST">
                        @csrf
                        <div class="col-12">
                            {{Form::label('package',__('Select Package'),['class'=>'form-label'])}}<x-required></x-required>
                            {!! Form::select('package', array_combine($arrPackage, $arrPackage), null, ['class' => 'form-control select', 'required' => 'required']) !!}
                        </div>
                        <div class="col-12 mt-2">
                            <button type="submit" class="btn btn-primary">Submit</button>
                            <button type="reset" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!--/ Change Plan -->

    <!-- Edit Profile -->
    <div class="col-lg-3 col-md-6">
        <div class="mt-4">
            <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasEnd" aria-labelledby="offcanvasEndLabel">
                <div class="offcanvas-header">
                    <h5 id="offcanvasEndLabel" class="offcanvas-title">Edit Customer Details</h5>
                    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body my-auto mx-0 flex-grow-0">
                    <form action="{{ route('customer.update', $customer->id) }}" method="PUT">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="fullname">Full Name</label>
                            <input type="text" id="fullname" class="form-control" placeholder="John Doe" value="{{$customer['fullname']}}" aria-label="John Doe" name="fullname" />
                        </div>
                        <div class="row">
                            <div class="mb-3 col-6">
                                <label class="form-label" for="account">Username</label>
                                <input type="text" readonly  class="form-control" placeholder="John" name="account" id="account" value="{{$customer['account']}}" required  aria-label="John" />
                            </div>
                            <div class="mb-3 col-6">
                                <label class="form-label" for="username">Prevous Username</label>
                                <input type="text"  class="form-control" placeholder="Doe"  name="username" id="username" value="{{$customer['username']}}" aria-label="Doe" />
                            </div>
                        </div>
                        <div class="row">
                            <div class="mb-3 col-6">
                                <label class="form-label" for="pppoe_password">Secret</label>
                                <input type="text" id="password" class="form-control" placeholder="12345678" value="{{$customer['password']}}" aria-label="12345678" name="password" />
                            </div>
                            <div class="mb-3  col-6">
                                <label class="form-label" for="phonenumber">Phone Number</label>
                                <input type="text" id="phonenumber" class="form-control phone-mask" placeholder="+254712345678" value="{{$customer['contact']}}" aria-label="+254712345678" name="phonenumber" />
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="email">Email</label>
                            <input type="text" id="email" class="form-control" placeholder="john.doe@example.com" value="{{$customer['email']}}" aria-label="john.doe@example.com" name="email" />
                        </div>
                        <div class="row">
                            <div class="mb-3 col-6">
                                <label class="form-label" for="housenumber">House Number</label>
                                <input type="text" id="housenumber" class="form-control" placeholder="B6" value="{{$customer['housenumber']}}" aria-label="housenumber" name="housenumber" />
                            </div>
                            <div class="mb-3 col-6">
                                <label class="form-label" for="apartment">Apartmnent</label>
                                <input type="text" id="apartment" class="form-control" placeholder="Future Flats" aria-label="apartment" value="{{$customer['apartment']}}" name="apartment" />
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="location">Location</label>
                            <input type="text" id="location" class="form-control" placeholder="Ruiru" aria-label="location" value="{{$customer['location']}}" name="location" />
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="service">Service Type</label>
                            <input type="text" id="service" class="form-control" value="{{$customer['service']}}" name="service" readonly/>
                        </div>
                        <div class="mb-3">
                            {{ Form::label('package', __('Select Package'), ['class' => 'form-label']) }}<x-required></x-required>
                            {!! Form::select('package', array_combine($arrPackage, $arrPackage), $customer['package'], ['class' => 'form-control select', 'required' => 'required']) !!}
                        </div>
                        <button type="submit" class="btn btn-primary me-3">Submit</button>
                        <button type="reset" class="btn btn-label-danger" data-bs-dismiss="offcanvas">Cancel</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Edit Profile -->
    <div class="col-md-12">
        <div class="row g-3 mb-3">
            <div class="col-sm-6 col-xl-3">
                <div class="card">
                    <div class="card-body h-100">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">Uptime</span>
                                <div class="d-flex align-items-center my-1">
                                <h6 class="mb-0 me-2">
                                    @if(isset($session) && $session->uptime)
                                        Uptime: {{ gmdate('H:i:s', $session->uptime) }}
                                    @else
                                        Downtime: {{ gmdate('H:i:s', $downtime) }}
                                    @endif
                                </h6>
                                </div>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-primary">
                                    <i class="ti ti-clock ti-26px"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card">
                    <div class="card-body h-100">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">Data Used</span>
                                <div class="d-flex align-items-center my-1">
                                    @if($dataUsage->upload >= 1073741824 || $dataUsage->download >= 1073741824) 
                                        <h6 class="mb-0 me-2">{{ number_format($dataUsage->download  / 1073741824, 2) }}GB/{{ number_format($dataUsage->upload  / 1073741824, 2) }}GB</h6>
                                    @else
                                        <h6 class="mb-0 me-2">{{ number_format($dataUsage->download  / 1048576, 2) }}MB/{{ number_format($dataUsage->upload  / 1048576, 2) }}MB</h6>
                                    @endif
                                </div>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-danger">
                                    <i class="ti ti-download ti-26px"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card">
                    <div class="card-body h-100">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">Package</span>
                                <div class="d-flex align-items-center my-1">
                                    <h6 class="mb-0 me-2">{{$customer['package']}}</h6>
                                </div>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-success">
                                    <i class="ti ti-package ti-26px"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card">
                    <div class="card-body h-100">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">Days Left</span>
                                <div class="d-flex align-items-center my-1">
                                    <h6 class="mb-0 me-2">{{ $expiryStatus }}</h6>
                                </div>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-warning">
                                    <i class="ti ti-calendar ti-26px"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
                <div class="col-xl-12">
                    <div class="nav-align-top mb-6">
                        <ul class="nav nav-pills mb-4 nav-fill col-xl-12" role="tablist">
                            <li class="nav-item mb-1 mb-sm-0">
                                <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" 
                                    data-bs-target="#navs-pills-justified-pppoe" aria-controls="navs-pills-justified-pppoe" aria-selected="true">
                                    <span class="d-none d-sm-block"><i class="tf-icons ti ti-brand-google-analytics ti-sm me-1_5 align-text-bottom"></i>Trafic</span>
                                    <i class="ti ti-brand-google-analytics ti-sm d-sm-none"></i>
                                </button>
                            </li>
                            <li class="nav-item mb-1 mb-sm-0">
                                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" 
                                    data-bs-target="#navs-pills-justified-static" aria-controls="navs-pills-justified-static" aria-selected="false">
                                    <span class="d-none d-sm-block"><i class="tf-icons ti ti-cash-register ti-sm me-1_5 align-text-bottom"></i>Transactions</span>
                                    <i class="ti ti-cash-register ti-sm d-sm-none"></i>
                                </button>
                            </li>
                            <li class="nav-item">
                                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" 
                                    data-bs-target="#navs-pills-justified-dhcp" aria-controls="navs-pills-justified-dhcp" aria-selected="false">
                                    <span class="d-none d-sm-block"><i class="tf-icons ti ti-invoice ti-sm me-1_5 align-text-bottom"></i>Invoices</span>
                                    <i class="ti ti-invoice ti-sm d-sm-none"></i>
                                </button>
                            </li>
                            <li class="nav-item">
                                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" 
                                    data-bs-target="#navs-pills-justified-logs" aria-controls="navs-pills-justified-logs" aria-selected="false">
                                    <span class="d-none d-sm-block"><i class="tf-icons ti ti-logs ti-sm me-1_5 align-text-bottom"></i>Customer Logs</span>
                                    <i class="ti ti-logs ti-sm d-sm-none"></i>
                                </button>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="navs-pills-justified-pppoe" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="">
                                            <div class="card-header">
                                                <!--<i class="fa fa-th"></i>-->
                                                <h5 class="card-title mb-0">Realtime Trafic</h5>
                                                <div class="card-tools pull-right"></div>
                                            </div>
                                            <div class="card-body py-3">
                                                <div id="liveUsageChart"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="">
                                            <div class="card-header">
                                                <!--<i class="fa fa-th"></i>-->
                                                <h5 class="card-title mb-0">Monthly Data Usage</h5>
                                                <div class="card-tools pull-right"></div>
                                            </div>
                                            <div class="card-body border-radius-none">
                                                <div  id="dataUsageChart"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="navs-pills-justified-static" role="tabpanel">
                                <div class="pt-3">
                                    <div class="table-responsive" >
                                        <table class="table datata">
                                            <thead>
                                                <tr>
                                                    <th> {{__('Invoice')}}</th>
                                                    <th> {{__('Trans ID')}}</th>
                                                    <th> {{__('Amount')}}</th>
                                                    <th> {{__('Account')}}</th>
                                                    <th> {{__('Service Type')}}</th>
                                                    <th> {{__('Date')}}</th>
                                                    <th>Method</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($transactions as $transaction)
                                                    <tr  style="cursor:pointer;">
                                                        <td>{{ $transaction->invoice_id }}</td>
                                                        <td>{{ $transaction->payment_id }}</td>
                                                        <td>{{ Auth::user()->priceFormat($transaction->amount) }}</td>
                                                        <td>{{ $transaction->account }}</td>
                                                        <td>{{ $transaction->service_type }}</td>
                                                        <td>{{ Auth::user()->dateFormat($transaction->date) }}</td>
                                                        <td>{{ $transaction->type }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>                                           
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="navs-pills-justified-dhcp" role="tabpanel">
                                <div class="pt-3">
                                    <div class="table-responsive" >
                                        <table class="table datatab">
                                            <thead>
                                                <tr>
                                                    <th> {{__('Invoice')}}</th>
                                                    <th>Plan Name</th>
                                                    <th>Issue Date</th>
                                                    <th>Date Sent</th>
                                                    <th>Due Date</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($invoices as $invoice)
                                                    <tr  style="cursor:pointer;">
                                                        <td>{{ $invoice->invoice_id }}</td>
                                                        <td>{{ $invoice->ref_number }}</td>
                                                        <td>{{ Auth::user()->dateFormat($invoice->issue_date) }}</td>
                                                        <td>{{ Auth::user()->dateFormat($invoice->send_date) }}</td>
                                                        <td>{{ Auth::user()->dateFormat($invoice->due_date) }}</td>
                                                        <td>
                                                        @if( $invoice->status == 0)
                                                            <span class="">Paid</span>
                                                        @else
                                                            <span class="">Unpaid</span>
                                                        @endif
                                                    </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="navs-pills-justified-logs" role="tabpanel">
                                <div class="pt-3">
                                    <div class="table-responsive" >
                                        <table class="table datatabl">
                                            <thead>
                                                <tr>
                                                    <th>Username</th>
                                                    <th>NAS IP</th>
                                                    <th>Start Time</th>
                                                    <th>Stop Time</th>
                                                    <th>Uploaded</th>
                                                    <th>Downloaded</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($authLogs as $session)
                                                    <tr>
                                                        <td>{{ $session->username }}</td>
                                                        <td>{{ $session->nasipaddress }}</td>
                                                        <td>{{ $session->acctstarttime }}</td>
                                                        <td>{{ $session->acctstoptime ?? 'Online' }}</td>
                                                        <td>{{ round($session->acctinputoctets / 1024 / 1024, 2) }} MB</td>
                                                        <td>{{ round($session->acctoutputoctets / 1024 / 1024, 2) }} MB</td>
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
    new DataTable('table.datatab', {
        fixedHeader: {
            header: true,
            footer: true
        }
    });
    new DataTable('table.datata', {
        fixedHeader: {
            header: true,
            footer: true
        }
    });
</script>
@endpush
