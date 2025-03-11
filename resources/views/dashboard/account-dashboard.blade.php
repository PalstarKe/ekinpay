@extends('layouts/layoutMaster')
@section('page-title')
    {{__('Dashboard')}}
@endsection
@push('script-page')
    <script>
        @if(\Auth::user()->can('show account dashboard'))
        (function () {
            var chartBarOptions = {
                series: [
                    {
                        name: "{{__('Income')}}",
                        data:{!! json_encode($incExpLineChartData['income']) !!}
                    },
                    {
                        name: "{{__('Expense')}}",
                        data: {!! json_encode($incExpLineChartData['expense']) !!}
                    }
                ],

                chart: {
                    height: 250,
                    type: 'area',
                    // type: 'line',
                    dropShadow: {
                        enabled: true,
                        color: '#000',
                        top: 18,
                        left: 7,
                        blur: 10,
                        opacity: 0.2
                    },
                    toolbar: {
                        show: false
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    width: 2,
                    curve: 'smooth'
                },
                title: {
                    text: '',
                    align: 'left'
                },
                xaxis: {
                    categories:{!! json_encode($incExpLineChartData['day']) !!},
                    title: {
                        text: '{{ __("Date") }}'
                    }
                },
                colors: ['#6fd944', '#ff3a6e'],


                grid: {
                    strokeDashArray: 4,
                },
                legend: {
                    show: false,
                },
                yaxis: {
                    title: {
                        text: '{{ __("Amount") }}'
                    },

                }

            };
            var arChart = new ApexCharts(document.querySelector("#cash-flow"), chartBarOptions);
            arChart.render();
        })();

        (function () {
            var options = {
                chart: {
                    height: 180,
                    type: 'bar',
                    toolbar: {
                        show: false,
                    },
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    width: 2,
                    curve: 'smooth'
                },
                series: [{
                    name: "{{__('Income')}}",
                    data: {!! json_encode($incExpBarChartData['income']) !!}
                }, {
                    name: "{{__('Expense')}}",
                    data: {!! json_encode($incExpBarChartData['expense']) !!}
                }],
                xaxis: {
                    categories: {!! json_encode($incExpBarChartData['month']) !!},
                },
                colors: ['#3ec9d6', '#FF3A6E'],
                fill: {
                    type: 'solid',
                },
                grid: {
                    strokeDashArray: 4,
                },
                legend: {
                    show: true,
                    position: 'top',
                    horizontalAlign: 'right',
                },
            };
            var chart = new ApexCharts(document.querySelector("#incExpBarChart"), options);
            chart.render();
        })();

        (function () {
            var options = {
                chart: {
                    height: 200,
                    type: 'donut',
                },
                dataLabels: {
                    enabled: false,
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '75%',
                        }
                    }
                },
                series: {!! json_encode($expenseCatAmount) !!},
                colors: {!! json_encode($expenseCategoryColor) !!},
                labels: {!! json_encode($expenseCategory) !!},
                legend: {
                    show: true
                }
            };
            var chart = new ApexCharts(document.querySelector("#expenseByCategory"), options);
            chart.render();
        })();

        (function () {
            var options = {
                chart: {
                    height: 200,
                    type: 'donut',
                },
                dataLabels: {
                    enabled: false,
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '75%',
                        }
                    }
                },
                series: {!! json_encode($incomeCatAmount) !!},
                colors: {!! json_encode($incomeCategoryColor) !!},
                labels:  {!! json_encode($incomeCategory) !!},
                legend: {
                    show: true
                }
            };
            var chart = new ApexCharts(document.querySelector("#incomeByCategory"), options);
            chart.render();
        })();

        (function () {
            var options = {
                series: [{{ round($storage_limit,2) }}],
                chart: {
                    height: 400,
                    type: 'radialBar',
                    offsetY: -20,
                    sparkline: {
                        enabled: true
                    }
                },
                plotOptions: {
                    radialBar: {
                        startAngle: -90,
                        endAngle: 90,
                        track: {
                            background: "#e7e7e7",
                            strokeWidth: '97%',
                            margin: 5, // margin is in pixels
                        },
                        dataLabels: {
                            name: {
                                show: true
                            },
                            value: {
                                offsetY: -50,
                                fontSize: '20px'
                            }
                        }
                    }
                },
                grid: {
                    padding: {
                        top: -10
                    }
                },
                colors: ["#6FD943"],
                labels: ['Used'],
            };
            var chart = new ApexCharts(document.querySelector("#limit-chart"), options);
            chart.render();
        })();

        @endif
    </script>
@endpush

@section('content')
    <div class="row g-3">
        <div class="col-xl-6 col-sm-6">
            <div class="card  h-100">
                <div class="d-flex align-items-end row">
                    <div class="col-7">
                        <div class="card-body text-nowrap">
                        <h5 class="card-title mb-0">Welcome Back {{\Auth::user()->name }}!</h5>
                        <p class="mb-2">Tota Income Today</p>
                        <h4 class="text-primary mb-1">{{\Auth::user()->priceFormat(\Auth::user()->todayIncome())}}</h4>
                        <a href="{{ route('invoice.index') }}" class="btn btn-primary">View Sales</a>
                        </div>
                    </div>
                    <div class="col-5 text-center text-sm-left">
                        <div class="card-body pb-0 px-0 px-md-4">
                        <img
                            src="{{ asset('assets/img/illustrations/card-advance-sale.png') }}"
                            height="160"
                            alt="view sales" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6 col-sm-6">
            <div class="card rounded-4 h-100">
                <div class="card-body">
                    <div class="row g-2 text-center">
                        <!-- Add User -->
                        <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                            <a href="{{ route('customer.create') }}" class="btn btn-outline-primary w-100">Add Client</a>
                        </div>
                        <!-- Recharge User -->
                        <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                            <a href="{{ route('invoice.index') }}" class="btn btn-outline-primary w-100">Reports</a>
                        </div>
                        <!-- Send Message -->
                        <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                            <a href="" class="btn btn-outline-primary w-100">Network</a>
                        </div>
                        <!-- Bulk Message -->
                        <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                            <a href="{{ url('chats') }}" class="btn btn-outline-primary w-100">Bulk Message</a>
                        </div>
                        <!-- Export Clients -->
                        <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                            <a href="{{route('customer.export')}}" class="btn btn-outline-primary w-100">Export Clients</a>
                        </div>
                        <!-- Add Branch -->
                        <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                            <a href="{{ route('customer.index') }}" class="btn btn-outline-primary w-100">CRM</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-8 col-sm-8 ">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="card-title mb-0">Clients statistics</h5>
                    <small class="text-muted">Updated Today</small>
                </div>
                <div class="card-body d-flex align-items-end">
                    <div class="w-100">
                        <div class="row gy-2">
                            <div class="col-md-4 col-6">
                                <div class="d-flex align-items-center">
                                    <div class="badge rounded bg-label-primary me-4 p-2">
                                        <i class="ti ti ti-users ti-lg"></i>
                                    </div>
                                    <div class="card-info">
                                        <h5 class="mb-0">{{\Auth::user()->countCustomers()}}</h5>
                                        <small>Total Users</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 col-6">
                                <div class="d-flex align-items-center">
                                    <div class="badge rounded bg-label-info me-4 p-2">
                                        <i class="ti ti-ti ti-users ti-lg"></i>
                                    </div>
                                    <div class="card-info">
                                        <h5 class="mb-0">0</h5>
                                        <small>Active Users</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 col-6">
                                <div class="d-flex align-items-center">
                                    <div class="badge rounded bg-label-danger me-4 p-2">
                                        <i class="ti ti-users ti-lg"></i>
                                    </div>
                                    <div class="card-info">
                                        <h5 class="mb-0">0</h5>
                                        <small>Expired Users</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row gy-2">
                            <div class="col-md-4 col-6">
                                <div class="d-flex align-items-center">
                                    <div class="badge rounded bg-label-primary me-4 p-2">
                                        <i class="ti ti ti-users ti-lg"></i>
                                    </div>
                                    <div class="card-info">
                                        <h5 class="mb-0">0</h5>
                                        <small>Total Online</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 col-6">
                                <div class="d-flex align-items-center">
                                    <div class="badge rounded bg-label-info me-4 p-2">
                                        <i class="ti ti-ti ti-users ti-lg"></i>
                                    </div>
                                    <div class="card-info">
                                        <h5 class="mb-0">0</h5>
                                        <small>Online PPPoE</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 col-6">
                                <div class="d-flex align-items-center">
                                    <div class="badge rounded bg-label-danger me-4 p-2">
                                        <i class="ti ti-users ti-lg"></i>
                                    </div>
                                    <div class="card-info">
                                        <h5 class="mb-0">0</h5>
                                        <small>Online Hotspot</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-sm-4 ">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between">
                <h5 class="card-title mb-0">Site Statistics</h5>
                </div>
                <div class="card-body d-flex align-items-end">
                    <div class="w-100">
                        <div class="row gy-2">
                            <div class="col-md-6 col-6">
                                <div class="d-flex align-items-center">
                                    <div class="badge rounded bg-label-primary me-4 p-2">
                                        <i class="ti ti-users ti-lg"></i>
                                    </div>
                                    <div class="card-info">
                                        <h5 class="mb-0">6</h5>
                                        <small>Total</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-6">
                                <div class="d-flex align-items-center">
                                    <div class="badge rounded bg-label-warning me-4 p-2">
                                        <i class="ti ti-users ti-lg"></i>
                                    </div>
                                    <div class="card-info">
                                        <h5 class="mb-0">0</h5>
                                        <small>Inactive</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row gy-2">
                            <div class="col-md-6 col-6">
                                <div class="d-flex align-items-center">
                                    <div class="badge rounded bg-label-success me-4 p-2">
                                        <i class="ti ti-users ti-lg"></i>
                                    </div>
                                    <div class="card-info">
                                        <h5 class="mb-0">0</h5>
                                        <small>Online</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-6">
                                <div class="d-flex align-items-center">
                                    <div class="badge rounded bg-label-danger me-4 p-2">
                                        <i class="ti ti-users ti-lg"></i>
                                    </div>
                                    <div class="card-info">
                                        <h5 class="mb-0">6</h5>
                                        <small>Offline</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-12 col-12">
            <div class="row g-3">
                <div class="col-xl-3 col-sm-3">
                    <div class="card h-100">
                        <div class="card-header pb-0">
                            <h5 class="card-title mb-1">Daily Entries</h5>
                        </div>
                        <div class="card-body">
                            <div id="profitLastMonth"></div>
                            <div class="d-flex justify-content-between align-items-center mt-3 gap-3">
                                <h4 class="mb-0">0</h4>
                                <small class="text-success">+0%</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-3">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-1">0</h5>
                            <p class="card-subtitle">Active Clients</p>
                        </div>
                        <div class="card-body">
                            <div id="expensesChart"></div>
                            <div class="mt-1 text-center">
                                <small class="text-muted mt-3">0 Clients more than last Yesterday</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6 col-sm-6">
                    <div class="card h-100">
                        <div class="card-body d-flex justify-content-between">
                            <div class="d-flex flex-column">
                                <div class="card-title mb-auto">
                                    <h5 class="mb-0 text-nowrap">Earnings</h5>
                                    <p class="mb-0">Monthly Report</p>
                                </div>
                                <div class="chart-statistics">
                                    <h3 class="card-title mb-0">{{\Auth::user()->priceFormat(\Auth::user()->incomeCurrentMonth())}}</h3>
                                    <p class="text-danger text-nowrap mb-0"><i class="ti ti-chevron-down me-1"></i>- -100%</p>
                                    <p class="mb-0">Less than last month.</p>
                                </div>
                            </div>
                            <div id="generatedLeadChart"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-12">
            <div class="card h-100">
                <div class="card-body p-0">
                    <div class="row row-bordered g-0">
                        <div class="col-md-8 position-relative p-6">
                            <div class="card-header d-inline-block p-0 text-wrap position-absolute">
                                <h5 class="m-0 card-title">Yearly Report</h5>
                            </div>
                            <div id="totalRevenueChrt" class="p-4"></div>
                        </div>
                        <div class="col-md-4 p-4">
                            <div class="text-center mt-5">
                                <div class="dropdown">
                                <button class="btn btn-sm btn-label-primary dropdown-toggle"
                                        type="button" id="budgetId" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <script>
                                    document.write(new Date().getFullYear());
                                    </script>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="budgetId">
                                    <a class="dropdown-item prev-year1" href="javascript:void(0);">
                                    <script>
                                        document.write(new Date().getFullYear() - 1);
                                    </script>
                                    </a>
                                    <a class="dropdown-item prev-year2" href="javascript:void(0);">
                                    <script>
                                        document.write(new Date().getFullYear() - 2);
                                    </script>
                                    </a>
                                    <a class="dropdown-item prev-year3" href="javascript:void(0);">
                                    <script>
                                        document.write(new Date().getFullYear() - 3);
                                    </script>
                                    </a>
                                </div>
                                </div>
                            </div>
                            <h3 class="text-center pt-8 mb-0">{{\Auth::user()->priceFormat(\Auth::user()->expenseCurrentMonth())}}</h3>
                            <div class="px-3">
                                <div id="bugetChart"></div>
                            </div>
                            <div class="text-center mt-8">
                                <button type="button" class="btn btn-warning">Add Expenses</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-8 col-sm-8">
            <div class="card">
            <div class="card-datatable table-responsive">
                <table class="table table-sm datatable-invoice border-top">
                <thead>
                    <tr>
                    <th>#</th>
                    <th>Package</th>
                    <th>Service Type</th>
                    <th>Subscription</th>
                    <th>Revenue</th>
                    </tr>
                </thead>
                </table>
            </div>
            </div>
        </div>
        <div class="col-xxl-4 col-sm-4">
            <div class="card">
            <div class="card-datatable table-responsive">
                <table class="table table-sm datatable-invoice border-top">
                <thead>
                    <tr>
                    <th>#</th>
                    <th>Account</th>
                    <th>Usage</th>
                    </tr>
                </thead>
                </table>
            </div>
            </div>
        </div>
    </div>
@endsection

@push('script-page')
    <script>
        if(window.innerWidth <= 500)
        {
            $('p').removeClass('text-sm');
        }
    </script>
@endpush
