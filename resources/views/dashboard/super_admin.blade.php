@extends('layouts/layoutMaster')

@section('title', __('Dashboard'))
@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/apex-charts/apex-charts.scss',
  'resources/assets/vendor/libs/swiper/swiper.scss',
  'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
  'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
  'resources/assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.scss',
  'resources/assets/vendor/libs/apex-charts/apex-charts.scss'
])
@endsection

@section('page-style')
<!-- Page -->
@vite([
    'resources/assets/vendor/scss/pages/cards-advance.scss'])
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/swiper/swiper.js',
  'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
  'resources/assets/vendor/libs/apex-charts/apexcharts.js'
  ])
@endsection

@section('page-script')
@vite([
  'resources/assets/js/app-ecommerce-dashboard.js',
  'resources/assets/js/cards-statistics.js',
  'resources/assets/js/charts-apex.js'
])
@endsection
<script>
    console.log("Script is running...");
</script>

@push('css-page')
<style>
    .apexcharts-yaxis
    {
        transform: translate(20px, 0px) !important;
    }
</style>
@endpush
<!-- @push('theme-script')
    <script src="{{ asset('assets/libs/apexcharts/dist/apexcharts.min.js') }}"></script>
@endpush -->
@push('script-page')
<script>
document.addEventListener("DOMContentLoaded", function () {
    var labels = {!! json_encode($chartData['label']) !!};
    var data = {!! json_encode($chartData['data']) !!};

    // Function to dynamically get primary theme color
    function getPrimaryColor() {
        var primaryColor = getComputedStyle(document.documentElement)
            .getPropertyValue('--bs-primary')
            .trim();

        if (!primaryColor) {
            var tempElem = document.createElement("div");
            tempElem.className = "text-primary";
            document.body.appendChild(tempElem);
            primaryColor = getComputedStyle(tempElem).color;
            document.body.removeChild(tempElem);
        }

        return primaryColor || "#007bff"; // Default to Bootstrap blue if no primary color is found
    }

    var themePrimaryColor = getPrimaryColor();

    var chartBarOptions = {
        series: [
            {
                name: '{{ __("Orders") }}',
                data: data
            },
        ],
        chart: {
            height: 300,
            type: 'area',
            dropShadow: {
                enabled: true,
                color: '#000',
                top: 18,
                left: 7,
                blur: 10,
                opacity: 0.2
            },
            toolbar: { show: false }
        },
        dataLabels: { enabled: false },
        stroke: { width: 2, curve: 'smooth' },
        title: { text: '', align: 'left' },
        xaxis: {
            categories: labels,
            title: {
                text: '{{ __("Months") }}',
                style: { color: themePrimaryColor }
            },
            labels: {
                style: {
                    colors: themePrimaryColor,
                    fontSize: "12px",
                    fontWeight: 400
                }
            }
        },
        yaxis: {
            title: {
                text: '{{ __("Orders") }}',
                style: { color: themePrimaryColor },
                offsetX: -5, // Move the title slightly left
                offsetY: 0,   // Center it properly
                rotate: -90     // Ensure the text is horizontal
            },
            labels: {
                style: {
                    colors: themePrimaryColor,
                    fontSize: "12px",
                    fontWeight: 400
                }
            }
        },
        grid: { strokeDashArray: 4 },
        legend: { show: false },
        colors: [themePrimaryColor], // Use primary theme color for the chart line
    };

    var chartContainer = document.querySelector("#chart-sales");
    if (!chartContainer) {
        console.error("Error: #chart-sales container is missing.");
        return;
    }

    var arChart = new ApexCharts(chartContainer, chartBarOptions);
    arChart.render();
});
</script>
@endpush
@php
$admin_payment_setting = Utility::getAdminPaymentSetting();
@endphp

@section('content')

<div class="row g-6">
	<div class="col-lg-3 col-sm-6">
		<div class="card card-border-shadow-primary h-100">
			<div class="card-body">
				<div class="d-flex align-items-center mb-2">
					<div class="avatar me-4">
						<span class="avatar-initial rounded bg-label-primary"><i class='ti ti-users ti-28px'></i></span>
					</div>
					<h6 class="mb-0">{{$user->total_user}}</h6>
				</div>
				<p class="mb-1">Total Companies</p>
			</div>
		</div>
	</div>
	<div class="col-lg-3 col-sm-6">
		<div class="card card-border-shadow-warning h-100">
			<div class="card-body">
				<div class="d-flex align-items-center mb-2">
					<div class="avatar me-4">
						<span class="avatar-initial rounded bg-label-warning"><i class='ti ti-shopping-cart-plus ti-28px'></i></span>
					</div>
					<h6 class="mb-0">{{$user->total_orders}}</h6>
				</div>
				<p class="mb-1">Total Orders</p>
			</div>
		</div>
	</div>
	<div class="col-lg-3 col-sm-6">
		<div class="card card-border-shadow-danger h-100">
			<div class="card-body">
				<div class="d-flex align-items-center mb-2">
					<div class="avatar me-4">
						<span class="avatar-initial rounded bg-label-danger"><i class='ti ti-template ti-28px'></i></span>
					</div>
					<h6 class="mb-0">{{$user->total_plan}}</h6>
				</div>
				<p class="mb-1">Total Plans</p>
			</div>
		</div>
	</div>
	<div class="col-lg-3 col-sm-6">
		<div class="card card-border-shadow-info h-100">
			<div class="card-body">
				<div class="d-flex align-items-center mb-2">
					<div class="avatar me-4">
						<span class="avatar-initial rounded bg-label-info"><i class='ti ti-clock ti-28px'></i></span>
					</div>
					<h6 class="mb-0">{{isset($admin_payment_setting['currency_symbol']) ? $admin_payment_setting['currency_symbol'] : '$'}} {{number_format($user['total_orders_price'])}}</h6>
				</div>
				<p class="mb-1">Total Amount</p>
			</div>
		</div>
	</div>
	{{--<pre>{{ json_encode($chartData['data']) }}</pre>
<pre>{{ json_encode($chartData['label']) }}</pre>--}}


  	<div class="col-xxl-12">
		<div class="card h-100">
			<div class="card-body p-0">
				<div class="row row-bordered g-0">
					<div class="col-md-12 position-relative p-6">
						<div class="card-header d-inline-block p-0 text-wrap position-absolute">
							<h5 class="mb-3 card-title">Recent Order</h5>
						</div>
						<div id="chart-sales" data-color="primary" data-height="280" class="p-3"></div>
					</div>
				</div>
			</div>
		</div>
  	</div>
</div>
@endsection
