@extends('layouts/layoutMaster')
@push('script-page')
@endpush
@section('page-title')
    {{__('Site-Detail')}}
@endsection
@section('content')
<div class="row g-3">
    <div class="col-sm-12 col-md-3">
		<div class="card">
			<div class="card-header"><h5 class=""><i class="fas fa-bolt"></i> {{$nas->shortname}} </h5></div>
			<div class="card-body">
				<div class="mb-3">
					<label><i class="fas fa-microchip"></i> IP: {{$nas->nasname}}</label>
				</div>
				<div class="mb-3">
					<label><i class="fas fa-microchip"></i> Name: {{$nas->shortname}}</label>
				</div>
				<div class="mb-3">
                    @if ($nas->status == 'Online')
                        <span class="badge bg-success"><i class="fas fa-bolt"></i> Status: {{ __('Active') }}</span>
                    @else
                        <span class="badge bg-danger"> <i class="fas fa-bolt"></i> Status: {{ __('Inactive') }}</span>
                    @endif
				</div>
				<div class="mb-3">
					<label><i class="fas fa-clock"></i> Last Seen: {{$nas['last_seen']}}</label>
				</div>
			</div>
		</div>
		<div class="card mt-3">
			<div class="card-header"><h5 class="">Assigned Packages</h5></div>
			<div class="card-body">
				<div class="mb-3">
				<ul>
					@foreach($nas->routers as $router)
						@foreach($router->packages as $package)
							<li>{{ $package->name_plan }}</li>
						@endforeach
					@endforeach
				</ul>
				<span class="">Assign New Package</span>
				<form action="{{ route('nas.assignPackage', $nas->id) }}" method="POST">
					@csrf
					<!-- <label for="package">Select Package:</label> -->
					<div class="row g-2 mb-2">
						<select  class="form-control select" name="package_id" required>
							@foreach($packages as $package)
								<option value="{{ $package->id }}">{{ $package->name_plan }}</option>
							@endforeach
						</select>
						<button class="clipboard-btn btn btn-primary me-2" type="submit">Assign</button>
					</div>
				</form>
				</div>
			</div>
		</div>
  	</div>

  	<div class="col-xl-9 col-12">
    	<div class="card mb-6">
      		<h5 class="card-header">Configure Mikrotik</h5>
      		<div class="card-body">
				<span class="">RouterOS V6</span>
				<div class="row g-2 mb-2">
					<div class="col-md-10 col-sm-12 pe-0 mb-md-0 mb-2">
						<input class="form-control" id="rosv6" type="text" value="/interface ovpn-client add connect-to=158.220.116.211 port=1094 name=THEFUTUREOVPN user={{$nas['shortname']}} password={{$nas['shortname']}} profile=default comment=THEFUTUREOVPN cipher=aes256 auth=sha1" />
					</div>
					<div class="col-md-2 col-sm-12">
						<button class="clipboard-btn btn btn-primary me-2" data-clipboard-action="copy" data-clipboard-target="#rosv6">Copy </button>
					</div>
				</div>
				<span class="">RouterOS V7</span>
				<div class="row g-2 mb-2">
					<div class="col-md-10 col-sm-12 pe-0 mb-md-0 mb-2">
						<input class="form-control" id="rosv7" type="text" value="/interface ovpn-client add connect-to=158.220.116.211 port=1094 proto=tcp name=THEFUTUREOVPN user={{$nas['shortname']}} password={{$nas['shortname']}} profile=default comment=THEFUTUREOVPN cipher=aes256-cbc auth=sha1 route-nopull=yes" />
					</div>
					<div class="col-md-2 col-sm-12">
						<button class="clipboard-btn btn btn-primary me-2" data-clipboard-action="copy" data-clipboard-target="#rosv7">Copy </button>
					</div>
				</div>
				<span class="">Add Radius</span>
				<div class="row g-2 mb-2">
					<div class="col-md-10 col-sm-12 pe-0 mb-md-0 mb-2">
						<input class="form-control" id="radiusadd" type="text" value="/radius add address=10.108.0.1 secret=thefuture2025 service=ppp,hotspot timeout=3s" />
					</div>
					<div class="col-md-2 col-sm-12">
						<button class="clipboard-btn btn btn-primary me-2" data-clipboard-action="copy" data-clipboard-target="#radiusadd">Copy </button>
					</div>
				</div>
				<div class="row g-2 mb-2">
					<div class="col-md-10 col-sm-12 pe-0 mb-md-0 mb-2">
						<input class="form-control" id="radiusin" type="text" value="/radius incoming set accept=yes port=3799" />
					</div>
					<div class="col-md-2 col-sm-12">
						<button class="clipboard-btn btn btn-primary me-2" data-clipboard-action="copy" data-clipboard-target="#radiusin">Copy </button>
					</div>
				</div>
				<span class="">Adjust Mikrotik Time</span>
				<div class="row g-2 mb-2">
					<div class="col-md-10 col-sm-12 pe-0 mb-md-0 mb-2">
						<input class="form-control" id="timeset" type="text" value="/system clock set time-zone-name=Africa/Nairobi " />
					</div>
					<div class="col-md-2 col-sm-12">
						<button class="clipboard-btn btn btn-primary me-2" data-clipboard-action="copy" data-clipboard-target="#timeset">Copy </button>
					</div>
				</div>
    		</div>
  		</div>
  	</div>
</div>
@endsection