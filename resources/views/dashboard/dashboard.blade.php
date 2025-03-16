@extends('layouts/layoutMaster')
@section('page-title')
    {{__('Dashboard')}}
@endsection
@push('script-page')
<script>
    function fetchNasCounts() {
        $.ajax({
            url: "{{ route('nas.counts') }}",
            method: "GET",
            success: function (response) {
                $("#total-nas").text(response.total);
                $("#online-nas").text(response.online);
                $("#offline-nas").text(response.offline);
            },
            error: function () {
                console.error("Failed to fetch NAS counts");
            }
        });
    }

    $(document).ready(function () {
        fetchNasCounts();
        setInterval(fetchNasCounts, 10000);
    });
</script>
<script>
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

        // Donut Chart Colors
        const chartColors = {
            donut: {
            series1: '#24B364',
            series2: '#53D28C',
            series3: '#7EDDA9',
            series4: '#A9E9C5'
            }
        };
          // Expenses Radial Bar Chart
        // --------------------------------------------------------------------
        const activeChartEl = document.querySelector('#activeChart'),
        activeChartConfig = {
            chart: {
                height: 170,
                sparkline: {
                enabled: true
                },
                parentHeightOffset: 0,
                type: 'radialBar'
            },
            colors: [config.colors.warning],
            series: [ [{{ $Actdata['activePercentage'] }}] ],
            plotOptions: {
                radialBar: {
                offsetY: 0,
                startAngle: -90,
                endAngle: 90,
                hollow: {
                    size: '65%'
                },
                track: {
                    strokeWidth: '45%',
                    background: borderColor
                },
                dataLabels: {
                    name: {
                    show: false
                    },
                    value: {
                    fontSize: '24px',
                    color: headingColor,
                    fontWeight: 500,
                    offsetY: -5
                    }
                }
                }
            },
            grid: {
                show: false,
                padding: {
                bottom: 5
                }
            },
            stroke: {
                lineCap: 'round'
            },
            labels: ['Progress'],
            responsive: [
                {
                breakpoint: 1442,
                options: {
                    chart: {
                    height: 120
                    },
                    plotOptions: {
                    radialBar: {
                        dataLabels: {
                        value: {
                            fontSize: '18px'
                        }
                        },
                        hollow: {
                        size: '60%'
                        }
                    }
                    }
                }
                },
                {
                breakpoint: 1025,
                options: {
                    chart: {
                    height: 136
                    },
                    plotOptions: {
                    radialBar: {
                        hollow: {
                        size: '65%'
                        },
                        dataLabels: {
                        value: {
                            fontSize: '18px'
                        }
                        }
                    }
                    }
                }
                },
                {
                breakpoint: 769,
                options: {
                    chart: {
                    height: 120
                    },
                    plotOptions: {
                    radialBar: {
                        hollow: {
                        size: '55%'
                        }
                    }
                    }
                }
                },
                {
                breakpoint: 426,
                options: {
                    chart: {
                    height: 145
                    },
                    plotOptions: {
                    radialBar: {
                        hollow: {
                        size: '65%'
                        }
                    }
                    },
                    dataLabels: {
                    value: {
                        offsetY: 0
                    }
                    }
                }
                },
                {
                breakpoint: 376,
                options: {
                    chart: {
                    height: 105
                    },
                    plotOptions: {
                    radialBar: {
                        hollow: {
                        size: '60%'
                        }
                    }
                    }
                }
                }
            ]
            };
        if (typeof activeChartEl !== undefined && activeChartEl !== null) {
            const activeChart = new ApexCharts(activeChartEl, activeChartConfig);
            activeChart.render();
        }

        // dailyEntries Line Chart
        // --------------------------------------------------------------------
        const dailyEntriesEl = document.querySelector('#dailyEntries'),
        dailyEntriesConfig = {
            chart: {
                height: 110,
                type: 'line',
                parentHeightOffset: 0,
                toolbar: {
                show: false
                }
            },
            grid: {
                borderColor: borderColor,
                strokeDashArray: 6,
                xaxis: {
                lines: {
                    show: true,
                    colors: '#000'
                }
                },
                yaxis: {
                lines: {
                    show: false
                }
                },
                padding: {
                top: -18,
                left: -4,
                right: 7,
                bottom: -10
                }
            },
            colors: [config.colors.info],
            stroke: {
                width: 2
            },
            series: [
                {
                data: @json($chartData['data'])
                }
            ],
            tooltip: {
                shared: false,
                intersect: true,
                x: {
                show: false
                }
            },
            xaxis: {
                categories: @json($chartData['labels']),
                labels: {
                    show: true,
                    rotate: -45, 
                    style: { colors: labelColor, fontSize: '12px' }
                },
                axisTicks: {
                show: true
                },
                axisBorder: {
                show: false
                }
            },
            yaxis: {
                labels: {
                show: false
                }
            },
            tooltip: {
                enabled: false
            },
            markers: {
                size: 3.5,
                fillColor: config.colors.info,
                strokeColors: 'transparent',
                strokeWidth: 3.2,
                discrete: [
                {
                    seriesIndex: 0,
                    dataPointIndex: 5,
                    fillColor: cardColor,
                    strokeColor: config.colors.info,
                    size: 5,
                    shape: 'circle'
                }
                ],
                hover: {
                size: 5.5
                }
            },
            responsive: [
                {
                breakpoint: 1442,
                options: {
                    chart: {
                    height: 100
                    }
                }
                },
                {
                breakpoint: 1025,
                options: {
                    chart: {
                    height: 86
                    }
                }
                },
                {
                breakpoint: 769,
                options: {
                    chart: {
                    height: 93
                    }
                }
                }
            ]
            };
        if (typeof dailyEntriesEl !== undefined && dailyEntriesEl !== null) {
            const dailyEntries = new ApexCharts(dailyEntriesEl, dailyEntriesConfig);
            dailyEntries.render();
        }

        // Generated Leads Chart
        // --------------------------------------------------------------------
        const monthlyEntriesEl = document.querySelector('#monthlyEntries'),

        monthlyEntriesConfig = {
            chart: {
                height: 165,
                width: 150,
                parentHeightOffset: 0,
                type: 'donut'
            },
            labels: ['PPPoE', 'Hotspot'],
            series: [ [{{ $Entdata['pppoeEntries'] }}], [{{ $Entdata['hotspotEntries'] }}] ],
            colors: [
                chartColors.donut.series1,
                chartColors.donut.series2,
            ],
            stroke: {
                width: 0
            },
            dataLabels: {
                enabled: false,
                formatter: function (val, opt) {
                return parseInt(val) + '%';
                }
            },
            legend: {
                show: false
            },
            tooltip: {
                theme: false
            },
            grid: {
                padding: {
                top: 15,
                right: -20,
                left: -20
                }
            },
            states: {
                hover: {
                filter: {
                    type: 'none'
                }
                }
            },
            plotOptions: {
                pie: {
                donut: {
                    size: '70%',
                    labels: {
                    show: true,
                    value: {
                        fontSize: '1.5rem',
                        fontFamily: 'Public Sans',
                        color: headingColor,
                        fontWeight: 500,
                        offsetY: -15,
                        formatter: function (val) {
                        return parseInt(val) + '%';
                        }
                    },
                    name: {
                        offsetY: 20,
                        fontFamily: 'Public Sans'
                    },
                    total: {
                        show: true,
                        showAlways: true,
                        color: config.colors.success,
                        fontSize: '.8125rem',
                        label: 'Total',
                        fontFamily: 'Public Sans',
                        formatter: function (w) {
                        return [ [{{ $Entdata['thisMonthEntries'] }}]];
                        }
                    }
                    }
                }
                }
            },
            responsive: [
                {
                breakpoint: 1025,
                options: {
                    chart: {
                    height: 172,
                    width: 160
                    }
                }
                },
                {
                breakpoint: 769,
                options: {
                    chart: {
                    height: 178
                    }
                }
                },
                {
                breakpoint: 426,
                options: {
                    chart: {
                    height: 147
                    }
                }
                }
            ]
            };
        if (typeof monthlyEntriesEl !== undefined && monthlyEntriesEl !== null) {
            const monthlyEntries = new ApexCharts(monthlyEntriesEl, monthlyEntriesConfig);
            monthlyEntries.render();
        }
        // Total Revenue Report Chart - Bar Chart
        // --------------------------------------------------------------------
        const totalRevenueChartEl = document.querySelector('#totalRevenueChart'),
            totalRevenueChartOptions = {
            series: [
                {
                name: 'Earning',
                data: @json($expenData['revenues'])
                },
                {
                name: 'Expense',
                data: @json($expenData['expenses'])
                }
            ],
            chart: {
                height: 413,
                parentHeightOffset: 0,
                stacked: true,
                type: 'bar',
                toolbar: { show: false }
            },
            tooltip: {
                enabled: false
            },
            plotOptions: {
                bar: {
                horizontal: false,
                columnWidth: '40%',
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
                curve: 'smooth',
                width: 6,
                lineCap: 'round',
                colors: [cardColor]
            },
            legend: {
                show: true,
                horizontalAlign: 'right',
                position: 'top',
                fontSize: '13px',
                fontFamily: 'Public Sans',
                markers: {
                height: 12,
                width: 12,
                radius: 12,
                offsetX: -5,
                offsetY: 2
                },
                labels: {
                colors: headingColor
                },
                itemMargin: {
                horizontal: 10,
                vertical: 2
                }
            },
            grid: {
                show: false,
                padding: {
                bottom: -8,
                top: 20
                }
            },
            xaxis: {
                categories: @json($expenData['months']),
                labels: {
                style: {
                    fontSize: '13px',
                    colors: labelColor,
                    fontFamily: 'Public Sans'
                }
                },
                axisTicks: {
                show: false
                },
                axisBorder: {
                show: false
                }
            },
            yaxis: {
                labels: {
                offsetX: -16,
                style: {
                    fontSize: '13px',
                    colors: labelColor,
                    fontFamily: 'Public Sans'
                }
                }
                // ,
                // min: -1000000,
                // max: 1000000,
                // tickAmount: 5
            },
            responsive: [
                {
                breakpoint: 1700,
                options: {
                    plotOptions: {
                    bar: {
                        columnWidth: '43%'
                    }
                    }
                }
                },
                {
                breakpoint: 1441,
                options: {
                    plotOptions: {
                    bar: {
                        columnWidth: '50%'
                    }
                    },
                    chart: {
                    height: 422
                    }
                }
                },
                {
                breakpoint: 1300,
                options: {
                    plotOptions: {
                    bar: {
                        columnWidth: '50%'
                    }
                    }
                }
                },
                {
                breakpoint: 1025,
                options: {
                    plotOptions: {
                    bar: {
                        columnWidth: '50%'
                    }
                    },
                    chart: {
                    height: 390
                    }
                }
                },
                {
                breakpoint: 991,
                options: {
                    plotOptions: {
                    bar: {
                        columnWidth: '38%'
                    }
                    }
                }
                },
                {
                breakpoint: 850,
                options: {
                    plotOptions: {
                    bar: {
                        columnWidth: '50%'
                    }
                    }
                }
                },
                {
                breakpoint: 449,
                options: {
                    plotOptions: {
                    bar: {
                        columnWidth: '73%'
                    }
                    },
                    chart: {
                    height: 360
                    },
                    xaxis: {
                    labels: {
                        offsetY: -5
                    }
                    },
                    legend: {
                    show: true,
                    horizontalAlign: 'right',
                    position: 'top',
                    itemMargin: {
                        horizontal: 10,
                        vertical: 0
                    }
                    }
                }
                },
                {
                breakpoint: 394,
                options: {
                    plotOptions: {
                    bar: {
                        columnWidth: '88%'
                    }
                    },
                    legend: {
                    show: true,
                    horizontalAlign: 'center',
                    position: 'bottom',
                    markers: {
                        offsetX: -3,
                        offsetY: 0
                    },
                    itemMargin: {
                        horizontal: 10,
                        vertical: 5
                    }
                    }
                }
                }
            ],
            states: {
                hover: {
                filter: {
                    type: 'none'
                }
                },
                active: {
                filter: {
                    type: 'none'
                }
                }
            }
            };
        if (typeof totalRevenueChartEl !== undefined && totalRevenueChartEl !== null) {
            const totalRevenueChart = new ApexCharts(totalRevenueChartEl, totalRevenueChartOptions);
            totalRevenueChart.render();
        }
        // Total Revenue Report Budget Line Chart
        const budgetChartEl = document.querySelector('#budgetChart'),
            budgetChartOptions = {
            chart: {
                height: 100,
                toolbar: { show: false },
                zoom: { enabled: false },
                type: 'line'
            },
            series: [
                {
                name: 'Last Month',
                data: [20, 10, 30, 16, 24, 5, 40, 23, 28, 5, 30]
                },
                {
                name: 'This Month',
                data: [50, 40, 60, 46, 54, 35, 70, 53, 58, 35, 60]
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
                top: -30,
                bottom: -15,
                left: 25
                }
            },
            markers: {
                size: 0
            },
            xaxis: {
                labels: {
                show: false
                },
                axisTicks: {
                show: false
                },
                axisBorder: {
                show: false
                }
            },
            yaxis: {
                show: false
            },
            tooltip: {
                enabled: false
            }
            };
        if (typeof budgetChartEl !== undefined && budgetChartEl !== null) {
            const budgetChart = new ApexCharts(budgetChartEl, budgetChartOptions);
            budgetChart.render();
        }
})
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
                        <h4 class="text-primary mb-1">{{ \Auth::user()->priceFormat($data['todayIncome']) }}</h4>
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
                            <a href="" data-bs-toggle="offcanvas" data-bs-target="#offcanvasEnd" aria-controls="offcanvasEnd" class="btn btn-outline-primary w-100">Add Client</a>
                        </div>
                        <!-- Recharge User -->
                        <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                            <a href="{{ route('invoice.index') }}" class="btn btn-outline-primary w-100">Reports</a>
                        </div>
                        <!-- Send Message -->
                        <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                            <a href="{{ route('nas.index') }}" class="btn btn-outline-primary w-100">Sites</a>
                        </div>
                        <!-- Bulk Message -->
                        <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                            <a href="{{ route('sms.index') }}" class="btn btn-outline-primary w-100">Bulk Message</a>
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
                                        <h5 class="mb-0">{{ $data['totalCustomers'] }}</h5>
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
                                        <h5 class="mb-0">{{ $data['activeCustomers'] }}</h5>
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
                                        <h5 class="mb-0">{{ $data['expiredCustomers'] }}</h5>
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
                                        <h5 class="mb-0">{{ $data['onlineCustomers'] }}</h5>
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
                                        <h5 class="mb-0">{{ $data['onlinePPPoE'] }}</h5>
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
                                        <h5 class="mb-0">{{ $data['onlineHotspot'] }}</h5>
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
                                        <i class="ti ti-server-2 ti-lg"></i>
                                    </div>
                                    <div class="card-info">
                                        <h5 class="mb-0">{{ $data['totalSites'] }}</h5>
                                        <small>Total</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-6">
                                <div class="d-flex align-items-center">
                                    <div class="badge rounded bg-label-warning me-4 p-2">
                                        <i class="ti ti-server-2 ti-lg"></i>
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
                                        <i class="ti ti-server-2 ti-lg"></i>
                                    </div>
                                    <div class="card-info">
                                        <h5 class="mb-0" id="online-nas"></h5>
                                        <small>Online</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-6">
                                <div class="d-flex align-items-center">
                                    <div class="badge rounded bg-label-danger me-4 p-2">
                                        <i class="ti ti-server-2 ti-lg"></i>
                                    </div>
                                    <div class="card-info">
                                        <h5 class="mb-0" id="offline-nas"></h5>
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
                            <div id="dailyEntries"></div>
                            <div class="d-flex justify-content-between align-items-center mt-3 gap-3">
                                <h4 class="mb-0">{{ $data['todayEntries'] }} Clients</h4>
                                @if ($data['percentageChangeEntries'] > 0)
                                    <small class="text-success">+{{ $data['percentageChangeEntries'] }}%</small>
                                @elseif ($data['percentageChangeEntries'] < 0)
                                    <small class="text-danger">-{{ $data['percentageChangeEntries'] }}%</small>
                                @else
                                    <small class="text-muted">0%</small>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-3">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-1">{{ $data['activeCustomers'] }}</h5>
                            <p class="card-subtitle">Active Clients</p>
                        </div>
                        <div class="card-body">
                            <div id="activeChart"></div>
                            <div class="mt-1 text-center">
                                <small class="text-muted mt-3">{{ $data['activePercentage'] }}% active Clients out of {{ $data['totalCustomers'] }} Clients</small>
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
                                    <h3 class="card-title mb-0">Ksh {{ number_format($data['thisMonthIncome'], 2) }}</h3>
                                    @if ($data['incomePercentageChange'] >= 0)
                                        <p class="text-success text-nowrap mb-0"><i class="ti ti-trending-up me-1"></i>+{{ $data['incomePercentageChange'] }}%</p>
                                    @else
                                        <p class="text-danger text-nowrap mb-0"><i class="ti ti-trending-down me-1"></i>-{{ $data['incomePercentageChange'] }}%</p>
                                    @endif
                                    <!-- <p class="mb-0">Less than last month.</p> -->
                                </div>
                            </div>
                            <div id="monthlyEntries"></div>
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
                            <div id="totalRevenueChart" class="p-4"></div>
                        </div>
                        <div class="col-md-4 p-4">
                            <div class="text-center mt-5">
                                <div class="dropdown">
                                <button class="btn btn-sm btn-label-primary "
                                        type="button" id="budgetId" >
                                    <script>
                                    document.write(new Date().getFullYear());
                                    </script>
                                </button>
                                </div>
                            </div>
                            <h3 class="text-center pt-8 mb-0">Ksh {{ $data['totalRevenueYear'] }}</h3>
                            <div class="px-3">
                                <div id="budgetChart"></div>
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
            <div class="card-body table-responsive">
                <table class="table table-sm datatable-invoice border-top">
                <thead>
                    <tr>
                    <th>Package</th>
                    <th>Service Type</th>
                    <th>Subscription</th>
                    <th>Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($topPackages as $package)
                        <tr>
                            <td>{{ $package->reference }}</td>
                            <td>{{ $package->service_type }}</td>
                            <td>{{ $package->total_sales }}</td>
                            <td>${{ number_format($package->total_revenue, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                </table>
            </div>
            </div>
        </div>
        <div class="col-xxl-4 col-sm-4">
            <div class="card">
            <div class="card-body table-responsive">
                <table class="table table-sm datatable-invoice border-top">
                    <thead>
                        <tr>
                            <th>Account</th>
                            <th>Usage</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($topUsers as $user)
                            <tr>
                                <td>{{ $user->username }}</td>
                                <td>{{ number_format($user->total_data_usage / (1024 * 1024 * 1024), 2) }} GB</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            </div>
        </div>
    </div>
            <!-- Edit Profile -->
            <div class="col-lg-3 col-md-6">
            <div class="mt-4">
                <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasEnd" aria-labelledby="offcanvasEndLabel">
                    <div class="offcanvas-header">
                        <h5 id="offcanvasEndLabel" class="offcanvas-title">Edit Customer Details</h5>
                        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                    </div>
                    <div class="offcanvas-body my-auto mx-0 flex-grow-0">
                        <form action="{{ route('customer.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                {{Form::label('fullname',__('Full Name'),array('class'=>'form-label')) }}<x-required></x-required>
                                {{Form::text('fullname',null,array('class'=>'form-control','required'=>'required' ,'placeholder'=>__('Enter Full Name')))}}
                            </div>
                            <div class="row">
                                <div class="mb-3 col-6">
                                    {{Form::label('account',__('Account'),array('class'=>'form-label')) }}<x-required></x-required>
                                    {{ Form::text('account', $customerN, ['class' => 'form-control', 'required' => 'required', 'readonly' => 'readonly', 'placeholder' => __('Enter Account')]) }}
                                </div>
                                <div class="mb-3 col-6">
                                    {{Form::label('username',__('Old Username'),['class'=>'form-label'])}}
                                    {{Form::text('username',null,array('class'=>'form-control', 'placeholder'=>__('Enter Old Username')))}}
                                </div>
                            </div>
                            <div class="row">
                                <div class="mb-3 col-6">
                                    {{Form::label('password',__('Secret'),['class'=>'form-label'])}}<x-required></x-required>
                                    {{ Form::text('password', $customerN, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter Password')]) }}
                                </div>
                                <div class="mb-3  col-6">
                                    {{Form::label('contact',__('Contact'),['class'=>'form-label'])}}<x-required></x-required>
                                    {{Form::number('contact',null,array('class'=>'form-control','required'=>'required' , 'placeholder'=>__('Enter Contact')))}}
                                </div>
                            </div>
                            <div class="mb-3">
                                {{Form::label('email',__('Email'),['class'=>'form-label'])}}
                                {{Form::email('email',null,array('class'=>'form-control' , 'placeholder'=>__('Enter email')))}}
                            </div>
                            <div class="row">
                                <div class="mb-3 col-6">
                                    {{Form::label('housenumber',__('House Number'),['class'=>'form-label'])}}
                                    {{Form::text('housenumber',null,array('class'=>'form-control' , 'placeholder' => __('B5')))}}
                                </div>
                                <div class="mb-3 col-6">
                                    {{Form::label('apartment',__('Apartmnent'),array('class'=>'form-label')) }}
                                    {{Form::text('apartment',null,array('class'=>'form-control','placeholder'=>__('Future Flats')))}}
                                </div>
                            </div>
                            <div class="mb-3">
                                {{Form::label('location',__('Location'),['class'=>'form-label'])}}
                                {{Form::text('location',null,array('class'=>'form-control', 'placeholder'=>__('Nairobi')))}}
                            </div>
                            <div class="mb-3">
                                {{Form::label('service',__('Service'),['class'=>'form-label'])}}<x-required></x-required>
                                {!! Form::select('service', $arrType, null,array('class' => 'form-control select','required'=>'required', 'readonly'=>'readonly')) !!}
                            </div>
                            <div class="mb-3">
                                {{Form::label('package',__('Select Package'),['class'=>'form-label'])}}<x-required></x-required>
                                {!! Form::select('package', array_combine($arrPackage, $arrPackage), null, ['class' => 'form-control select', 'required' => 'required']) !!}
                            </div>
                            <div class="mb-3">
                                {{Form::label('charges',__('Installation Fee'),['class'=>'form-label'])}}
                                {{Form::number('charges',null,array('class'=>'form-control', 'placeholder'=>__('Installation Fee')))}}
                            </div>
                            <button type="submit" class="btn btn-primary me-3">Submit</button>
                            <button type="reset" class="btn btn-label-danger" data-bs-dismiss="offcanvas">Cancel</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- Edit Profile -->
@endsection

@push('script-page')
    <script>
        if(window.innerWidth <= 500)
        {
            $('p').removeClass('text-sm');
        }
    </script>
@endpush
