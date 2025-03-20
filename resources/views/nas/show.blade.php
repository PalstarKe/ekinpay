@extends('layouts/layoutMaster')
@push('script-page')
@endpush
@section('page-title')
    {{__('Site-Detail')}}
@endsection
@section('content')
<div class="row g-3">
    <div class="col-sm-12 col-md-4">
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
				{{--<div class="mb-3">
					<label><i class="fas fa-clock"></i> Last Seen: {{$nas['last_seen']}}</label>
				</div>--}}
			</div>
		</div>
		<div class="card mt-3">
			<div class="card-body">
				<div class="mb-3">
					<button class="clipboard-btn btn btn-primary me-2 mb-2 w-100" type="submit" data-bs-toggle="modal" data-bs-target="#assignPackage">Assign New Packages</button>
					<form action="{{ route('nas.downloadHotspot', ['nas_ip' => $nas->nasname]) }}" method="GET">
						<button class="clipboard-btn btn btn-primary me-2 w-100" type="submit">
							Download Hotspot Page
						</button>
					</form>
				</div>
			</div>
		</div>
  	</div>
	    <!-- Assign Packages -->
		<div class="modal fade" id="assignPackage" tabindex="-1" aria-hidden="true">
			<div class="modal-dialog modal-md modal-dialog-centered" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="exampleModalLabel2">Assign Packages</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body">
						<form action="{{ route('nas.assignPackage', $nas->id) }}" method="POST">
							@csrf
							<div class="nav-align-top mb-3">
								<!-- Navigation Tabs -->
								<ul class="nav nav-pills mb-4 d-flex flex-row gap-2" role="tablist">
									<li class="nav-item">
										<button type="button" class="nav-link active btn-sm" role="tab" data-bs-toggle="tab" 
											data-bs-target="#navs-pills-justified-pppoe" aria-controls="navs-pills-justified-pppoe" aria-selected="true">
											<span class="d-none d-sm-block"><i class="tf-icons ti ti-network ti-sm me-1_5 align-text-bottom"></i> PPPoE</span>
											<i class="ti ti-network ti-sm d-sm-none"></i>
										</button>
									</li>
									<li class="nav-item">
										<button type="button" class="nav-link btn btn-sm" role="tab" data-bs-toggle="tab" 
											data-bs-target="#navs-pills-justified-hotspot" aria-controls="navs-pills-justified-hotspot" aria-selected="false">
											<span class="d-none d-sm-block"><i class="tf-icons ti ti-wifi ti-sm me-1_5 align-text-bottom"></i> Hotspot</span>
											<i class="ti ti-wifi ti-sm d-sm-none"></i>
										</button>
									</li>
								</ul>

								<!-- Tab Content Wrapper -->
								<div class="tab-content bg-transparent shadow-none" style="background: transparent !important; box-shadow: none !important;">
									<!-- PPPoE Packages Tab -->
									<div class="tab-pane fade show active w-100 bg-transparent shadow-none" style="background: transparent !important; box-shadow: none !important;" id="navs-pills-justified-pppoe" role="tabpanel">
										<div class="table-responsive">
											<table class="table">
												<thead>
													<tr>
														<th>{{ __('Name') }}</th>
														<th>{{ __('Price') }}</th>
														<th>{{ __('Status') }}</th>
														<th>{{ __('Assign') }}</th> <!-- Checkbox column -->
													</tr>
												</thead>
												<tbody>
													@foreach($packages->where('type', 'PPPoE') as $package)
													<tr>
														<td>{{ $package->name_plan }}</td>
														<td>{{ $package->price }}</td>
														<td>
															@if($package->assigned_router_id)
																<span class="badge bg-label-success">Active</span>
															@else
																<span class="badge bg-label-secondary">Inactive</span>
															@endif
														</td>
														<td>
															<input class="form-check-input" type="checkbox" name="package_ids[]" 
																value="{{ $package->id }}" id="package{{ $package->id }}"
																@if($package->assigned_router_id) checked @endif>
														</td>
													</tr>
													@endforeach
												</tbody>
											</table>
										</div>
									</div>

									<!-- Hotspot Packages Tab -->
									<div class="tab-pane fade w-100" id="navs-pills-justified-hotspot" role="tabpanel">
										<div class="table-responsive">
											<table class="table">
												<thead>
													<tr>
														<th>{{ __('Name') }}</th>
														<th>{{ __('Price') }}</th>
														<th>{{ __('Status') }}</th>
														<th>{{ __('Assign') }}</th> <!-- Checkbox column -->
													</tr>
												</thead>
												<tbody>
													@foreach($packages->where('type', 'Hotspot') as $package)
													<tr>
														<td>{{ $package->name_plan }}</td>
														<td>{{ $package->price }}</td>
														<td>
															@if($package->assigned_router_id)
																<span class="badge bg-label-success">Active</span>
															@else
																<span class="badge bg-label-secondary">Inactive</span>
															@endif
														</td>
														<td>
															<input class="form-check-input" type="checkbox" name="package_ids[]" 
																value="{{ $package->id }}" id="package{{ $package->id }}"
																@if($package->assigned_router_id) checked @endif>
														</td>
													</tr>
													@endforeach
												</tbody>
											</table>
										</div>
									</div>
								</div> <!-- End Tab Content -->
							</div>

							<!-- Submit & Cancel Buttons -->
							<button type="submit" class="btn btn-primary">Submit</button>
							<button type="reset" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
						</form>
					</div>
				</div>
			</div>
   		 </div>
    	<!--/ Assign Packages -->
  	<div class="col-xl-8 col-12">
    	<div class="card mb-6">
      		<h5 class="card-header">Configure Mikrotik</h5>
      		<div class="card-body">
				<div class="row">
					<div class="col-md">
						<div id="accordionCustomIcon" class="accordion mt-4 accordion-custom-button">
							<div class="accordion-item">
								<h2 class="accordion-header text-body d-flex justify-content-between" id="accordionCustomIconOne">
									<button type="button" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#accordionCustomIcon-1" aria-controls="accordionCustomIcon-1">
										<i class="ri-bar-chart-2-line me-2 ri-20px"></i>
										RouterOS V6 - VPN & Radius Config
									</button>
								</h2>
								<div id="accordionCustomIcon-1" class="accordion-collapse collapse" data-bs-parent="#accordionCustomIcon">
									<div class="accordion-body">
									<div class="position-relative">
										<button class="btn btn-sm btn-primary position-absolute top-0 end-0 m-2" onclick="copyCode()">
											Copy
										</button>
										<pre highlighter="hljs"  style="display: block; overflow-x: auto; padding: 0.5em; background: rgb(240, 240, 240); color: rgb(68, 68, 68);"><code id="configCode" class="language-routeros" style="white-space: pre;"><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">1</span><span style="color: rgb(136, 136, 136);">#VPN and Radius Config </span><span>
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">2</span><span></span><span style="color: rgb(136, 136, 136);">#Dont Edit anything</span><span>
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">3</span><span></span><span style="color: rgb(12, 154, 154);">/ip dns
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">4</span><span style="color: rgb(12, 154, 154);"></span><span> </span><span style="color: rgb(153, 6, 154);">set</span><span> </span><span style="color: rgb(14, 154, 0);">servers</span><span>=8.8.8.8,8.8.4.4
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">5</span><span></span><span style="color: rgb(12, 154, 154);">/interface </span><span>ovpn-client
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">6</span><span> </span><span style="color: rgb(153, 6, 154);">add</span><span> </span><span style="color: rgb(14, 154, 0);">connect-to</span><span>=vpn.ekinpay.com </span><span style="color: rgb(14, 154, 0);">port</span><span>=</span><span style="color: rgb(120, 169, 96);">1094</span><span> </span><span style="color: rgb(14, 154, 0);">name</span><span>=</span><span style="color: rgb(136, 0, 0);">"EKINPAYOVPN"</span><span> </span><span style="color: rgb(14, 154, 0);">password</span><span>={{$nas['shortname']}} </span><span style="color: rgb(14, 154, 0);">user</span><span>={{$nas['shortname']}} </span><span style="color: rgb(14, 154, 0);">profile</span><span>=default </span><span style="color: rgb(14, 154, 0);">comment</span><span>=EKINPAYOVPN </span><span style="color: rgb(14, 154, 0);">cipher</span><span>=aes256 </span><span style="color: rgb(14, 154, 0);">auth</span><span>=sha1
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">7</span><span></span><span style="color: rgb(12, 154, 154);">/radius
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">8</span><span style="color: rgb(12, 154, 154);"></span><span> </span><span style="color: rgb(153, 6, 154);">add</span><span> </span><span style="color: rgb(14, 154, 0);">address</span><span>=10.108.0.1 </span><span style="color: rgb(14, 154, 0);">secret</span><span>=ekinpay2025 </span><span style="color: rgb(14, 154, 0);">service</span><span>=ppp,hotspot </span><span style="color: rgb(14, 154, 0);">timeout</span><span>=3s
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">9</span><span></span><span style="color: rgb(12, 154, 154);">/radius incoming
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">10</span><span style="color: rgb(12, 154, 154);"></span><span> </span><span style="color: rgb(153, 6, 154);">set</span><span> </span><span style="color: rgb(14, 154, 0);">accept</span><span>=</span><span style="color: rgb(120, 169, 96);">yes</span><span> </span><span style="color: rgb(14, 154, 0);">port</span><span>=3799
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">11</span><span></span><span style="color: rgb(12, 154, 154);">/user
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">12</span><span style="color: rgb(12, 154, 154);"></span><span> </span><span style="color: rgb(153, 6, 154);">add</span><span> </span><span style="color: rgb(14, 154, 0);">name</span><span>={{$nas['nasname']}} </span><span style="color: rgb(14, 154, 0);">password</span><span>=ekinpay2025 </span><span style="color: rgb(14, 154, 0);">group</span><span>=full
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">13</span><span></span><span style="color: rgb(12, 154, 154);">/system clock
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">14</span><span style="color: rgb(12, 154, 154);"></span><span> </span><span style="color: rgb(153, 6, 154);">set</span><span> </span><span style="color: rgb(14, 154, 0);">time-zone-name</span><span>=Africa/Nairobi
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">15</span><span></span><span style="color: rgb(136, 136, 136);">#</span><span>
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">16</span>
</code></pre>
									</div>
							</div>
								</div>
							</div>

							<div class="accordion-item previous-active">
								<h2 class="accordion-header text-body d-flex justify-content-between" id="accordionCustomIconTwo">
									<button type="button" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#accordionCustomIcon-2" aria-controls="accordionCustomIcon-2">
										<i class="ri-heart-fill me-2 ri-20px"></i>
										RouterOS V7 - VPN & Radius Config
									</button>
								</h2>
								<div id="accordionCustomIcon-2" class="accordion-collapse collapse" data-bs-parent="#accordionCustomIcon">
									<div class="accordion-body">
									<div class="position-relative">
										<button class="btn btn-sm btn-primary position-absolute top-0 end-0 m-2" onclick="copyCode()">
											Copy
										</button>
										<pre highlighter="hljs"  style="display: block; overflow-x: auto; padding: 0.5em; background: rgb(240, 240, 240); color: rgb(68, 68, 68);"><code id="configCode7" class="language-routeros" style="white-space: pre;"><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">1</span><span style="color: rgb(136, 136, 136);">#VPN and Radius Config </span><span>
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">2</span><span></span><span style="color: rgb(136, 136, 136);">#Dont Edit anything</span><span>
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">3</span><span></span><span style="color: rgb(12, 154, 154);">/ip dns
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">4</span><span style="color: rgb(12, 154, 154);"></span><span> </span><span style="color: rgb(153, 6, 154);">set</span><span> </span><span style="color: rgb(14, 154, 0);">servers</span><span>=8.8.8.8,8.8.4.4
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">5</span><span></span><span style="color: rgb(12, 154, 154);">/interface </span><span>ovpn-client
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">6</span><span> </span><span style="color: rgb(153, 6, 154);">add</span><span> </span><span style="color: rgb(14, 154, 0);">connect-to</span><span>=vpn.ekinpay.com </span><span style="color: rgb(14, 154, 0);">port</span><span>=</span><span style="color: rgb(120, 169, 96);">1094 </span><span style="color: rgb(14, 154, 0);">proto</span><span>=</span><span style="color: rgb(120, 169, 96);">tcp</span><span> </span><span style="color: rgb(14, 154, 0);">name</span><span>=</span><span style="color: rgb(136, 0, 0);">"EKINPAYOVPN"</span><span> </span><span style="color: rgb(14, 154, 0);">password</span><span>={{$nas['shortname']}} </span><span style="color: rgb(14, 154, 0);">user</span><span>={{$nas['shortname']}} </span><span style="color: rgb(14, 154, 0);">profile</span><span>=default </span><span style="color: rgb(14, 154, 0);">comment</span><span>=EKINPAYOVPN </span><span style="color: rgb(14, 154, 0);">cipher</span><span>=aes256-cbc </span><span style="color: rgb(14, 154, 0);">auth</span><span>=sha1 </span><span style="color: rgb(14, 154, 0);">route-nopull</span><span>=yes
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">7</span><span></span><span style="color: rgb(12, 154, 154);">/radius
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">8</span><span style="color: rgb(12, 154, 154);"></span><span> </span><span style="color: rgb(153, 6, 154);">add</span><span> </span><span style="color: rgb(14, 154, 0);">require-message-auth</span><span>=</span><span style="color: rgb(120, 169, 96);">no</span><span> </span><span style="color: rgb(14, 154, 0);">address</span><span>=10.108.0.1 </span><span style="color: rgb(14, 154, 0);">secret</span><span>=ekinpay2025 </span><span style="color: rgb(14, 154, 0);">service</span><span>=ppp,hotspot </span><span style="color: rgb(14, 154, 0);">timeout</span><span>=3s
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">9</span><span></span><span style="color: rgb(12, 154, 154);">/radius incoming
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">10</span><span style="color: rgb(12, 154, 154);"></span><span> </span><span style="color: rgb(153, 6, 154);">set</span><span> </span><span style="color: rgb(14, 154, 0);">accept</span><span>=</span><span style="color: rgb(120, 169, 96);">yes</span><span> </span><span style="color: rgb(14, 154, 0);">port</span><span>=3799
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">11</span><span></span><span style="color: rgb(12, 154, 154);">/user
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">12</span><span style="color: rgb(12, 154, 154);"></span><span> </span><span style="color: rgb(153, 6, 154);">add</span><span> </span><span style="color: rgb(14, 154, 0);">name</span><span>={{$nas['nasname']}} </span><span style="color: rgb(14, 154, 0);">password</span><span>=ekinpay2025 </span><span style="color: rgb(14, 154, 0);">group</span><span>=full
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">13</span><span></span><span style="color: rgb(12, 154, 154);">/system clock
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">14</span><span style="color: rgb(12, 154, 154);"></span><span> </span><span style="color: rgb(153, 6, 154);">set</span><span> </span><span style="color: rgb(14, 154, 0);">time-zone-name</span><span>=Africa/Nairobi
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">15</span><span></span><span style="color: rgb(136, 136, 136);">#</span><span>
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">16</span>
</code></pre>
									</div>
									</div>
								</div>
							</div>
							<div class="accordion-item previous-active">
								<h2 class="accordion-header text-body d-flex justify-content-between" id="accordionCustomIconTwo">
									<button type="button" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#accordionCustomIcon-3" aria-controls="accordionCustomIcon-3">
										<i class="ri-heart-fill me-2 ri-20px"></i>
										Hotspot - Config
									</button>
								</h2>
								<div id="accordionCustomIcon-3" class="accordion-collapse collapse" data-bs-parent="#accordionCustomIcon">
									<div class="accordion-body">
									<div class="position-relative">
										<button class="btn btn-sm btn-primary position-absolute top-0 end-0 m-2" onclick="copyCode()">
											Copy
										</button>
									<pre highlighter="hljs" style="display: block; overflow-x: auto; padding: 0.5em; background: rgb(240, 240, 240); color: rgb(68, 68, 68);"><code id="configCodeH" class="language-routeros" style="white-space: pre;"><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">1</span><span style="color: rgb(136, 136, 136);">#Hotspot settings (Change the Interface "EKINPAY-BRIDGE" and "address-pool" to match that of your router)</span><span>
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">2</span><span></span><span style="color: rgb(12, 154, 154);">/interface bridge
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">3</span><span style="color: rgb(12, 154, 154);"></span><span> </span><span style="color: rgb(153, 6, 154);">add</span><span> </span><span style="color: rgb(14, 154, 0);">name</span><span>=EKINPAY-BRIDGE
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">4</span><span></span><span style="color: rgb(12, 154, 154);">/ip hotspot profile
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">5</span><span style="color: rgb(12, 154, 154);"></span><span> </span><span style="color: rgb(153, 6, 154);">add</span><span> </span><span style="color: rgb(14, 154, 0);">dns-name</span><span>=hot.spot </span><span style="color: rgb(14, 154, 0);">hotspot-address</span><span>=10.201.0.1 </span><span style="color: rgb(14, 154, 0);">login-by</span><span>=\
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">6</span><span>    mac,http-chap,http-pap </span><span style="color: rgb(14, 154, 0);">mac-auth-mode</span><span>=mac-as-username-and-password </span><span style="color: rgb(14, 154, 0);">name</span><span>=\
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">7</span><span>    theEKINPAY </span><span style="color: rgb(14, 154, 0);">use-radius</span><span>=</span><span style="color: rgb(120, 169, 96);">yes</span><span>
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">8</span><span></span><span style="color: rgb(12, 154, 154);">/ip pool
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">9</span><span style="color: rgb(12, 154, 154);"></span><span> </span><span style="color: rgb(153, 6, 154);">add</span><span> </span><span style="color: rgb(14, 154, 0);">name</span><span>=hotspot-pool </span><span style="color: rgb(14, 154, 0);">ranges</span><span>=10.201.0.2-10.201.255.254
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">10</span><span></span><span style="color: rgb(12, 154, 154);">/ip dhcp-server
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">11</span><span style="color: rgb(12, 154, 154);"></span><span> </span><span style="color: rgb(153, 6, 154);">add</span><span> </span><span style="color: rgb(14, 154, 0);">address-pool</span><span>=hotspot-pool </span><span style="color: rgb(14, 154, 0);">disabled</span><span>=</span><span style="color: rgb(120, 169, 96);">no</span><span> </span><span style="color: rgb(14, 154, 0);">interface</span><span>=EKINPAY-BRIDGE </span><span style="color: rgb(14, 154, 0);">lease-time</span><span>=1h </span><span style="color: rgb(14, 154, 0);">name</span><span>=HOTSPOT_DHCP </span><span style="color: rgb(14, 154, 0);">conflict-detection</span><span>=</span><span style="color: rgb(120, 169, 96);">no</span><span> </span><span style="color: rgb(14, 154, 0);">add-arp</span><span>=</span><span style="color: rgb(120, 169, 96);">yes</span><span>
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">12</span><span></span><span style="color: rgb(12, 154, 154);">/ip hotspot
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">13</span><span style="color: rgb(12, 154, 154);"></span><span> </span><span style="color: rgb(153, 6, 154);">add</span><span> </span><span style="color: rgb(14, 154, 0);">address-pool</span><span>=hotspot-pool </span><span style="color: rgb(14, 154, 0);">disabled</span><span>=</span><span style="color: rgb(120, 169, 96);">no</span><span> </span><span style="color: rgb(14, 154, 0);">interface</span><span>=EKINPAY-BRIDGE </span><span style="color: rgb(14, 154, 0);">name</span><span>=hotspot </span><span style="color: rgb(14, 154, 0);">profile</span><span>=theEKINPAY
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">14</span><span></span><span style="color: rgb(12, 154, 154);">/ip hotspot </span><span>ip-binding
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">15</span><span> </span><span style="color: rgb(153, 6, 154);">add</span><span> </span><span style="color: rgb(14, 154, 0);">address</span><span>=10.201.0.1/16 </span><span style="color: rgb(14, 154, 0);">server</span><span>=hotspot
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">16</span><span> </span><span style="color: rgb(153, 6, 154);">add</span><span> </span><span style="color: rgb(14, 154, 0);">address</span><span>=0.0.0.0/0  </span><span style="color: rgb(14, 154, 0);">server</span><span>=hotspot </span><span style="color: rgb(14, 154, 0);">type</span><span>=blocked
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">17</span><span></span><span style="color: rgb(12, 154, 154);">/ip address
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">18</span><span style="color: rgb(12, 154, 154);"></span><span> </span><span style="color: rgb(153, 6, 154);">add</span><span> </span><span style="color: rgb(14, 154, 0);">address</span><span>=10.201.0.1/16 </span><span style="color: rgb(14, 154, 0);">comment</span><span>=</span><span style="color: rgb(136, 0, 0);">"hotspot network"</span><span> </span><span style="color: rgb(14, 154, 0);">interface</span><span>=EKINPAY-BRIDGE </span><span style="color: rgb(14, 154, 0);">network</span><span>=10.201.0.0
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">19</span><span></span><span style="color: rgb(12, 154, 154);">/ip dhcp-server network
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">20</span><span style="color: rgb(12, 154, 154);"></span><span> </span><span style="color: rgb(153, 6, 154);">add</span><span> </span><span style="color: rgb(14, 154, 0);">address</span><span>=10.201.0.0/16 </span><span style="color: rgb(14, 154, 0);">comment</span><span>=</span><span style="color: rgb(136, 0, 0);">"hotspot network"</span><span> </span><span style="color: rgb(14, 154, 0);">gateway</span><span>=10.201.0.1
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">21</span><span></span><span style="color: rgb(12, 154, 154);">/ip firewall address-list
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">22</span><span style="color: rgb(12, 154, 154);"></span><span> </span><span style="color: rgb(153, 6, 154);">add</span><span> </span><span style="color: rgb(14, 154, 0);">list</span><span>=ALLOWED_USERS </span><span style="color: rgb(14, 154, 0);">address</span><span>=10.201.0.0/16
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">23</span><span></span><span style="color: rgb(12, 154, 154);">/ip firewall nat </span><span>
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">24</span><span> </span><span style="color: rgb(153, 6, 154);">add</span><span> </span><span style="color: rgb(14, 154, 0);">action</span><span>=masquerade </span><span style="color: rgb(14, 154, 0);">chain</span><span>=srcnat </span><span style="color: rgb(14, 154, 0);">src-address-list</span><span>=ALLOWED_USERS
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">25</span><span></span><span style="color: rgb(12, 154, 154);">/ip hotspot </span><span>walled-garden
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">26</span><span></span><span style="color: rgb(153, 6, 154);">add</span><span> </span><span style="color: rgb(14, 154, 0);">dst-host</span><span>=*.ekinpay.com
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">27</span><span></span><span style="color: rgb(136, 136, 136);">#</span><span>
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">28</span>
</code></pre>
									</div>
								</div>
								</div>
							</div>
							<div class="accordion-item previous-active">
								<h2 class="accordion-header text-body d-flex justify-content-between" id="accordionCustomIconTwo">
									<button type="button" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#accordionCustomIcon-4" aria-controls="accordionCustomIcon-4">
										<i class="ri-heart-fill me-2 ri-20px"></i>
										PPPoE - Config
									</button>
								</h2>
								<div id="accordionCustomIcon-4" class="accordion-collapse collapse" data-bs-parent="#accordionCustomIcon">
									<div class="accordion-body">
									<div class="position-relative">
										<button class="btn btn-sm btn-primary position-absolute top-0 end-0 m-2" onclick="copyCode()">
											Copy
										</button>
									<pre highlighter="hljs" style="display: block; overflow-x: auto; padding: 0.5em; background: rgb(240, 240, 240); color: rgb(68, 68, 68);"><code id="configCodeP" class="language-routeros" style="white-space: pre;"><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 2.25em; padding-right: 1em; text-align: right; user-select: none;">1</span><span style="color: rgb(136, 136, 136);">#PPPoE Settings (Change the Interface "PPPoE-Server" and "PPPoE-Pool" to match that of your router)</span><span>
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 2.25em; padding-right: 1em; text-align: right; user-select: none;">2</span><span></span><span style="color: rgb(12, 154, 154);">/interface bridge
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 2.25em; padding-right: 1em; text-align: right; user-select: none;">3</span><span style="color: rgb(12, 154, 154);"></span><span> </span><span style="color: rgb(153, 6, 154);">add</span><span> </span><span style="color: rgb(14, 154, 0);">name</span><span>=EKINPAY-BRIDGE
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 2.25em; padding-right: 1em; text-align: right; user-select: none;">4</span><span></span><span style="color: rgb(12, 154, 154);">/ip firewall address-list
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 2.25em; padding-right: 1em; text-align: right; user-select: none;">5</span><span style="color: rgb(12, 154, 154);"></span><span> </span><span style="color: rgb(153, 6, 154);">add</span><span> </span><span style="color: rgb(14, 154, 0);">list</span><span>=ALLOWED_USERS  </span><span style="color: rgb(14, 154, 0);">address</span><span>=172.16.0.0/16
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 2.25em; padding-right: 1em; text-align: right; user-select: none;">6</span><span> </span><span style="color: rgb(153, 6, 154);">add</span><span> </span><span style="color: rgb(14, 154, 0);">list</span><span>=DISABLED_USERS </span><span style="color: rgb(14, 154, 0);">address</span><span>=90.90.0.0/16 
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 2.25em; padding-right: 1em; text-align: right; user-select: none;">7</span><span></span><span style="color: rgb(12, 154, 154);">/ip firewall nat </span><span>
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 2.25em; padding-right: 1em; text-align: right; user-select: none;">8</span><span> </span><span style="color: rgb(153, 6, 154);">add</span><span> </span><span style="color: rgb(14, 154, 0);">action</span><span>=masquerade </span><span style="color: rgb(14, 154, 0);">chain</span><span>=srcnat </span><span style="color: rgb(14, 154, 0);">src-address-list</span><span>=ALLOWED_USERS
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 2.25em; padding-right: 1em; text-align: right; user-select: none;">9</span><span></span><span style="color: rgb(12, 154, 154);">/ip pool
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 2.25em; padding-right: 1em; text-align: right; user-select: none;">10</span><span style="color: rgb(12, 154, 154);"></span><span> </span><span style="color: rgb(153, 6, 154);">add</span><span> </span><span style="color: rgb(14, 154, 0);">name</span><span>=EXPIRED_POOL </span><span style="color: rgb(14, 154, 0);">ranges</span><span>=90.90.0.2-90.90.255.254
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 2.25em; padding-right: 1em; text-align: right; user-select: none;">11</span><span> </span><span style="color: rgb(153, 6, 154);">add</span><span> </span><span style="color: rgb(14, 154, 0);">name</span><span>=PPPoE-Pool </span><span style="color: rgb(14, 154, 0);">ranges</span><span>=172.16.0.2-172.16.255.254
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 2.25em; padding-right: 1em; text-align: right; user-select: none;">12</span><span></span><span style="color: rgb(12, 154, 154);">/ppp profile
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 2.25em; padding-right: 1em; text-align: right; user-select: none;">13</span><span style="color: rgb(12, 154, 154);"></span><span> </span><span style="color: rgb(153, 6, 154);">add</span><span> </span><span style="color: rgb(14, 154, 0);">dns-server</span><span>=8.8.8.8,8.8.4.4 </span><span style="color: rgb(14, 154, 0);">local-address</span><span>=172.16.0.1 </span><span style="color: rgb(14, 154, 0);">name</span><span>=ppp </span><span style="color: rgb(14, 154, 0);">remote-address</span><span>=PPPoE-Pool
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 2.25em; padding-right: 1em; text-align: right; user-select: none;">14</span><span></span><span style="color: rgb(12, 154, 154);">/interface </span><span>pppoe-server</span><span style="color: rgb(12, 154, 154);"> server
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 2.25em; padding-right: 1em; text-align: right; user-select: none;">15</span><span style="color: rgb(12, 154, 154);"></span><span> </span><span style="color: rgb(153, 6, 154);">add</span><span> </span><span style="color: rgb(14, 154, 0);">authentication</span><span>=pap </span><span style="color: rgb(14, 154, 0);">default-profile</span><span>=ppp </span><span style="color: rgb(14, 154, 0);">disabled</span><span>=</span><span style="color: rgb(120, 169, 96);">no</span><span> </span><span style="color: rgb(14, 154, 0);">interface</span><span>=EKINPAY-BRIDGE \
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 2.25em; padding-right: 1em; text-align: right; user-select: none;">16</span><span>    </span><span style="color: rgb(14, 154, 0);">max-mru</span><span>=1492 </span><span style="color: rgb(14, 154, 0);">max-mtu</span><span>=1492 </span><span style="color: rgb(14, 154, 0);">mrru</span><span>=1600 </span><span style="color: rgb(14, 154, 0);">one-session-per-host</span><span>=</span><span style="color: rgb(120, 169, 96);">yes</span><span> \
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 2.25em; padding-right: 1em; text-align: right; user-select: none;">17</span><span>    </span><span style="color: rgb(14, 154, 0);">keepalive-timeout</span><span>=60 \
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 2.25em; padding-right: 1em; text-align: right; user-select: none;">18</span><span>    </span><span style="color: rgb(14, 154, 0);">service-name</span><span>=PPPoE-Server
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 2.25em; padding-right: 1em; text-align: right; user-select: none;">19</span><span></span><span style="color: rgb(12, 154, 154);">/ppp aaa
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 2.25em; padding-right: 1em; text-align: right; user-select: none;">20</span><span style="color: rgb(12, 154, 154);"></span><span> </span><span style="color: rgb(153, 6, 154);">set</span><span> </span><span style="color: rgb(14, 154, 0);">use-radius</span><span>=</span><span style="color: rgb(120, 169, 96);">yes</span><span>
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 2.25em; padding-right: 1em; text-align: right; user-select: none;">21</span><span></span><span style="color: rgb(136, 136, 136);">#</span></code></pre>
									</div>
								</div>
				</div>
							</div>
							<div class="accordion-item previous-active">
								<h2 class="accordion-header text-body d-flex justify-content-between" id="accordionCustomIconTwo">
									<button type="button" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#accordionCustomIcon-5" aria-controls="accordionCustomIcon-5">
										<i class="ri-heart-fill me-2 ri-20px"></i>
										PPPoE Expiry - Config
									</button>
								</h2>
								<div id="accordionCustomIcon-5" class="accordion-collapse collapse" data-bs-parent="#accordionCustomIcon">
									<div class="accordion-body">
									<div class="position-relative">
										<button class="btn btn-sm btn-primary position-absolute top-0 end-0 m-2" onclick="copyCode()">
											Copy
										</button>
									<pre highlighter="hljs" style="display: block; overflow-x: auto; padding: 0.5em; background: rgb(240, 240, 240); color: rgb(68, 68, 68);"><code id="configCodeE" class="language-routeros" style="white-space: pre;"><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">1</span><span style="color: rgb(136, 136, 136);">#Mikrotik Non-payment page (Dont change anything here)</span><span>
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">2</span><span></span><span style="color: rgb(12, 154, 154);">/ip firewall address-list
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">3</span><span style="color: rgb(12, 154, 154);"></span><span style="color: rgb(153, 6, 154);">add</span><span> </span><span style="color: rgb(14, 154, 0);">address</span><span>=redirect.ekinpay.com </span><span style="color: rgb(14, 154, 0);">list</span><span>=</span><span style="color: rgb(136, 0, 0);">"EKINPAY_REDIRECT_IP"</span><span> </span><span style="color: rgb(14, 154, 0);">comment</span><span>=</span><span style="color: rgb(136, 0, 0);">"-- DON'T REMOVE ::: EKINPAY EXPIRED USERS --"</span><span>
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">4</span><span></span><span style="color: rgb(12, 154, 154);">/ip firewall filter
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">5</span><span style="color: rgb(12, 154, 154);"></span><span style="color: rgb(153, 6, 154);">add</span><span> </span><span style="color: rgb(14, 154, 0);">action</span><span>=reject </span><span style="color: rgb(14, 154, 0);">chain</span><span>=forward </span><span style="color: rgb(14, 154, 0);">dst-port</span><span>=!80,3346 </span><span style="color: rgb(14, 154, 0);">protocol</span><span>=tcp </span><span style="color: rgb(14, 154, 0);">reject-with</span><span>=\
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">6</span><span>    icmp-network-unreachable </span><span style="color: rgb(14, 154, 0);">src-address-list</span><span>=DISABLED_USERS </span><span style="color: rgb(14, 154, 0);">comment</span><span>=</span><span style="color: rgb(136, 0, 0);">"-- DON'T REMOVE ::: EKINPAY EXPIRED USERS --"</span><span>
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">7</span><span></span><span style="color: rgb(153, 6, 154);">add</span><span> </span><span style="color: rgb(14, 154, 0);">action</span><span>=accept </span><span style="color: rgb(14, 154, 0);">chain</span><span>=forward </span><span style="color: rgb(14, 154, 0);">dst-port</span><span>=53 \
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">8</span><span>    </span><span style="color: rgb(14, 154, 0);">protocol</span><span>=tcp </span><span style="color: rgb(14, 154, 0);">src-address-list</span><span>=DISABLED_USERS </span><span style="color: rgb(14, 154, 0);">comment</span><span>=</span><span style="color: rgb(136, 0, 0);">"-- DON'T REMOVE ::: EKINPAY EXPIRED USERS --"</span><span>
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">9</span><span></span><span style="color: rgb(153, 6, 154);">add</span><span> </span><span style="color: rgb(14, 154, 0);">action</span><span>=accept </span><span style="color: rgb(14, 154, 0);">chain</span><span>=forward </span><span style="color: rgb(14, 154, 0);">dst-port</span><span>=53 </span><span style="color: rgb(14, 154, 0);">protocol</span><span>=udp </span><span style="color: rgb(14, 154, 0);">src-address-list</span><span>=DISABLED_USERS \
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">10</span><span>    </span><span style="color: rgb(14, 154, 0);">comment</span><span>=</span><span style="color: rgb(136, 0, 0);">"-- DON'T REMOVE ::: EKINPAY EXPIRED USERS --"</span><span>
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">11</span><span></span><span style="color: rgb(153, 6, 154);">add</span><span> </span><span style="color: rgb(14, 154, 0);">action</span><span>=drop </span><span style="color: rgb(14, 154, 0);">chain</span><span>=forward </span><span style="color: rgb(14, 154, 0);">src-address-list</span><span>=DISABLED_USERS </span><span style="color: rgb(14, 154, 0);">comment</span><span>=</span><span style="color: rgb(136, 0, 0);">"-- DON'T REMOVE ::: EKINPAY EXPIRED USERS --"</span><span>
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">12</span><span></span><span style="color: rgb(12, 154, 154);">/ip firewall nat
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">13</span><span style="color: rgb(12, 154, 154);"></span><span style="color: rgb(153, 6, 154);">add</span><span> </span><span style="color: rgb(14, 154, 0);">action</span><span>=masquerade </span><span style="color: rgb(14, 154, 0);">chain</span><span>=srcnat </span><span style="color: rgb(14, 154, 0);">dst-address</span><span>=8.8.8.8 \
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">14</span><span>    </span><span style="color: rgb(14, 154, 0);">src-address-list</span><span>=DISABLED_USERS </span><span style="color: rgb(14, 154, 0);">comment</span><span>=</span><span style="color: rgb(136, 0, 0);">"-- DON'T REMOVE ::: EKINPAY EXPIRED USERS --"</span><span>
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">15</span><span></span><span style="color: rgb(153, 6, 154);">add</span><span> </span><span style="color: rgb(14, 154, 0);">action</span><span>=masquerade </span><span style="color: rgb(14, 154, 0);">chain</span><span>=srcnat </span><span style="color: rgb(14, 154, 0);">dst-address</span><span>=8.8.4.4 \
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">16</span><span>    </span><span style="color: rgb(14, 154, 0);">src-address-list</span><span>=DISABLED_USERS </span><span style="color: rgb(14, 154, 0);">comment</span><span>=</span><span style="color: rgb(136, 0, 0);">"-- DON'T REMOVE ::: EKINPAY EXPIRED USERS --"</span><span>
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">17</span><span></span><span style="color: rgb(153, 6, 154);">add</span><span> </span><span style="color: rgb(14, 154, 0);">action</span><span>=redirect </span><span style="color: rgb(14, 154, 0);">chain</span><span>=dstnat </span><span style="color: rgb(14, 154, 0);">dst-port</span><span>=80 </span><span style="color: rgb(14, 154, 0);">protocol</span><span>=tcp \
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">18</span><span>    </span><span style="color: rgb(14, 154, 0);">src-address-list</span><span>=DISABLED_USERS </span><span style="color: rgb(14, 154, 0);">to-ports</span><span>=3346 </span><span style="color: rgb(14, 154, 0);">comment</span><span>=</span><span style="color: rgb(136, 0, 0);">"-- DON'T REMOVE ::: EKINPAY EXPIRED USERS --"</span><span>
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">19</span><span></span><span style="color: rgb(153, 6, 154);">add</span><span> </span><span style="color: rgb(14, 154, 0);">action</span><span>=dst-nat </span><span style="color: rgb(14, 154, 0);">chain</span><span>=dstnat </span><span style="color: rgb(14, 154, 0);">dst-port</span><span>=80 </span><span style="color: rgb(14, 154, 0);">protocol</span><span>=tcp \
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">20</span><span>    </span><span style="color: rgb(14, 154, 0);">src-address-list</span><span>=DISABLED_USERS </span><span style="color: rgb(14, 154, 0);">to-addresses</span><span>=13.245.222.41 </span><span style="color: rgb(14, 154, 0);">comment</span><span>=</span><span style="color: rgb(136, 0, 0);">"-- DON'T REMOVE ::: EKINPAY EXPIRED USERS --"</span><span> </span><span style="color: rgb(14, 154, 0);">disabled</span><span>=</span><span style="color: rgb(120, 169, 96);">yes</span><span>
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">21</span><span></span><span style="color: rgb(153, 6, 154);">add</span><span> </span><span style="color: rgb(14, 154, 0);">action</span><span>=masquerade </span><span style="color: rgb(14, 154, 0);">chain</span><span>=srcnat </span><span style="color: rgb(14, 154, 0);">dst-address-list</span><span>=EKINPAY_REDIRECT_IP \
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">22</span><span>    </span><span style="color: rgb(14, 154, 0);">src-address-list</span><span>=DISABLED_USERS </span><span style="color: rgb(14, 154, 0);">comment</span><span>=</span><span style="color: rgb(136, 0, 0);">"-- DON'T REMOVE ::: EKINPAY EXPIRED USERS --"</span><span> </span><span style="color: rgb(14, 154, 0);">disabled</span><span>=</span><span style="color: rgb(120, 169, 96);">yes</span><span>
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">23</span><span></span><span style="color: rgb(12, 154, 154);">/ip proxy
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">24</span><span style="color: rgb(12, 154, 154);"></span><span style="color: rgb(153, 6, 154);">set</span><span> </span><span style="color: rgb(14, 154, 0);">enabled</span><span>=</span><span style="color: rgb(120, 169, 96);">yes</span><span> </span><span style="color: rgb(14, 154, 0);">max-cache-size</span><span>=none </span><span style="color: rgb(14, 154, 0);">parent-proxy</span><span>=0.0.0.0 </span><span style="color: rgb(14, 154, 0);">port</span><span>=3346 </span><span style="color: rgb(14, 154, 0);">src-address</span><span>=0.0.0.0
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">25</span><span></span><span style="color: rgb(12, 154, 154);">/ip proxy </span><span>access
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">26</span><span></span><span style="color: rgb(153, 6, 154);">add</span><span> </span><span style="color: rgb(14, 154, 0);">action</span><span>=deny </span><span style="color: rgb(14, 154, 0);">dst-host</span><span>=!*.ekinpay.com </span><span style="color: rgb(14, 154, 0);">redirect-to</span><span>=\
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">27</span>    redirect.ekinpay.com/expired/{{$nas['nasname']}}
<span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">28</span><span></span><span style="color: rgb(136, 136, 136);">####v7.X</span><span>
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">29</span><span></span><span style="color: rgb(153, 6, 154);">add</span><span> </span><span style="color: rgb(14, 154, 0);">action</span><span>=redirect </span><span style="color: rgb(14, 154, 0);">dst-host</span><span>=!*.ekinpay.com </span><span style="color: rgb(14, 154, 0);">action-data</span><span>=\
</span><span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">30</span>    redirect.ekinpay.com/expired/{{$nas['nasname']}} 
<span class="comment linenumber react-syntax-highlighter-line-number" style="display: inline-block; min-width: 3.25em; padding-right: 1em; text-align: right; user-select: none;">31</span>    ####
</code></pre>
									</div>
								</div>
								</div>
							</div>
						</div>
					</div>
				</div>
              <!--/ Accordion With Custom Button -->
    		</div>
  		</div>
  	</div>
</div>
@endsection
<script>
function copyCode() {
    var codeElement = document.getElementById("configCode7");
    var clonedCode = codeElement.cloneNode(true); // Clone the code block

    // Remove line number spans
    var lineNumbers = clonedCode.querySelectorAll(".comment.linenumber");
    lineNumbers.forEach(span => span.remove());

    var textToCopy = clonedCode.innerText.trim(); // Get clean text content

    var tempTextArea = document.createElement("textarea");
    tempTextArea.value = textToCopy;
    document.body.appendChild(tempTextArea);
    tempTextArea.select();
    document.execCommand("copy");
    document.body.removeChild(tempTextArea);

    alert("Code copied successfully!");
}
function copyCode() {
    var codeElement = document.getElementById("configCode");
    var clonedCode = codeElement.cloneNode(true); // Clone the code block

    // Remove line number spans
    var lineNumbers = clonedCode.querySelectorAll(".comment.linenumber");
    lineNumbers.forEach(span => span.remove());

    var textToCopy = clonedCode.innerText.trim(); // Get clean text content

    var tempTextArea = document.createElement("textarea");
    tempTextArea.value = textToCopy;
    document.body.appendChild(tempTextArea);
    tempTextArea.select();
    document.execCommand("copy");
    document.body.removeChild(tempTextArea);

    alert("Code copied successfully!");
}
function copyCode() {
    var codeElement = document.getElementById("configCodeH");
    var clonedCode = codeElement.cloneNode(true); // Clone the code block

    // Remove line number spans
    var lineNumbers = clonedCode.querySelectorAll(".comment.linenumber");
    lineNumbers.forEach(span => span.remove());

    var textToCopy = clonedCode.innerText.trim(); // Get clean text content

    var tempTextArea = document.createElement("textarea");
    tempTextArea.value = textToCopy;
    document.body.appendChild(tempTextArea);
    tempTextArea.select();
    document.execCommand("copy");
    document.body.removeChild(tempTextArea);

    alert("Code copied successfully!");
}
function copyCode() {
    var codeElement = document.getElementById("configCodeP");
    var clonedCode = codeElement.cloneNode(true); // Clone the code block

    // Remove line number spans
    var lineNumbers = clonedCode.querySelectorAll(".comment.linenumber");
    lineNumbers.forEach(span => span.remove());

    var textToCopy = clonedCode.innerText.trim(); // Get clean text content

    var tempTextArea = document.createElement("textarea");
    tempTextArea.value = textToCopy;
    document.body.appendChild(tempTextArea);
    tempTextArea.select();
    document.execCommand("copy");
    document.body.removeChild(tempTextArea);

    alert("Code copied successfully!");
}
function copyCode() {
    var codeElement = document.getElementById("configCodeE");
    var clonedCode = codeElement.cloneNode(true); // Clone the code block

    // Remove line number spans
    var lineNumbers = clonedCode.querySelectorAll(".comment.linenumber");
    lineNumbers.forEach(span => span.remove());

    var textToCopy = clonedCode.innerText.trim(); // Get clean text content

    var tempTextArea = document.createElement("textarea");
    tempTextArea.value = textToCopy;
    document.body.appendChild(tempTextArea);
    tempTextArea.select();
    document.execCommand("copy");
    document.body.removeChild(tempTextArea);

    alert("Code copied successfully!");
}
</script>