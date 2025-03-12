@extends('layouts/layoutMaster')
@php
    $dir = asset(Storage::url('uploads/plan'));
@endphp
@section('page-title')
    {{ __('Manage Plan') }}
@endsection
@push('css-page')
<style>
    /* 20.01  */
    .price-card .list-unstyled .theme-avtar{
        width: 20px ;
        margin-right: 5px !important;
    }

        .request-btn .btn{
        padding: 8px 12px !important;
    }

    @media screen and (max-width:991px){
        .plan_card{
            width: 50%;
        }
    }
    @media screen and (max-width:767px){
        .plan_card{
            width: 100%;
        }
        .plan_card .price-card{
            height: auto ;
            margin-bottom: 0;
        }
    }
    @media screen and (max-width:481px){
        .plan_card .card-body .row .col-6{
            width: 100%;
        }
        .plan_card .card-body .row .col-6:not(:first-of-type) .list-unstyled{
            margin:0 0 20px!important;
        }
        .plan_card .card-body .row .col-6:first-of-type .list-unstyled{
            margin:20px 0 7px!important;
        }
        .plan_card .price-card{
            max-height: unset;
        }
    }
/* 20.01  */
</style>
@endpush

@section('content')

    <div class="row">
    <div class="float-end mb-3"> 
        @can('create plan')
            <a href="#" data-size="lg" data-url="{{ route('plans.create') }}" data-ajax-popup="true"
                data-bs-toggle="tooltip" title="{{ __('Create') }}" data-title="{{ __('Create New Plan') }}"
                class="btn btn-sm btn-primary">
                <i class="ti ti-plus"></i> {{ __('Create New Plan') }}
            </a>
        @endcan
    </div>
    @foreach ($plans as $plan)
        <div class="col-xl-4 col-lg-4 order-1 order-md-0">
            <div class="card mb-6 border border-2 border-primary rounded primary-shadow"> <!-- Applied theme styling -->
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <span class="badge bg-primary">{{ $plan->name }}</span>
                        <div class="d-flex justify-content-center">
                            <sub class="h5 pricing-currency mb-auto mt-1 text-primary">
                                {{ isset($admin_payment_setting['currency_symbol']) ? $admin_payment_setting['currency_symbol'] : '$' }}
                            </sub>
                            <h3 class="mb-0 text-primary">{{ number_format($plan->price) }}</h3>
                            <sub class="h6 pricing-duration mt-auto mb-3 fw-normal">
                                /{{ __(\App\Models\Plan::$arrDuration[$plan->duration]) }}
                            </sub>
                        </div>
                    </div>

                    @if (\Auth::user()->type == 'company' && \Auth::user()->plan == $plan->id)
                        <div class="d-flex flex-row-reverse m-0 p-0 active-tag">
                            <span class="align-items-right">
                                <i class="f-10 lh-1 fas fa-circle text-primary"></i>
                                <span class="ms-2">{{ __('Active') }}</span>
                            </span>
                        </div>
                    @endif

                    @if (\Auth::user()->type == 'super admin')
                    {{--@if (\Auth::user()->type == 'super admin' && $plan->price > 0)--}}
                        <div class="d-flex flex-row-reverse m-0 p-0 active-tag">
                            <div class="form-check form-switch custom-switch-v1 float-end">
                                <input type="checkbox" name="plan_disable"
                                    class="form-check-input input-primary is_disable" value="1"
                                    data-id='{{ $plan->id }}'
                                    data-name="{{ __('plan') }}"
                                    {{ $plan->is_disable == 1 ? 'checked' : '' }}>
                                <label class="form-check-label" for="plan_disable"></label>
                            </div>
                        </div>
                    @endif

                    <ul class="list-unstyled g-2 my-6">
                        {{--<li class="mb-2 d-flex align-items-center">
                            <i class="ti ti-checks ti-18px text-secondary me-2"></i>
                            <span>{{ $plan->max_users == -1 ? __('Unlimited') : $plan->max_users }} {{ __('Users') }}</span>
                        </li>--}}
                        <li class="mb-2 d-flex align-items-center">
                            <i class="ti ti-checks ti-18px text-primary me-2"></i>
                            <span>{{ __('Both PPPoE & Hotspot') }}</span>
                        </li>
                        <li class="mb-2 d-flex align-items-center">
                            <i class="ti ti-checks ti-18px text-primary me-2"></i>
                            <span>{{ $plan->max_customers == -1 ? __('Unlimited') : $plan->max_customers }} {{ __('Customers') }}</span>
                        </li>
                        <li class="mb-2 d-flex align-items-center">
                            <i class="ti ti-checks ti-18px text-primary me-2"></i>
                            <span>{{ __('Free Mikrotik Remote Access') }}</span>
                        </li>
                        <li class="mb-2 d-flex align-items-center">
                            <i class="ti ti-checks ti-18px text-primary me-2"></i>
                            <span>{{ __('Hotspot Mpesa StK Push') }}</span>
                        </li>
                        <li class="mb-2 d-flex align-items-center">
                            <i class="ti ti-checks ti-18px text-primary me-2"></i>
                            <span>{{ __('PPPoE Mpesa StK Push') }}</span>
                        </li>
                        <li class="mb-2 d-flex align-items-center">
                            <i class="ti ti-checks ti-18px text-primary me-2"></i>
                            <span>{{ __('Multiple Payments Gateways') }}</span>
                        </li>
                    </ul>

                    @if (\Auth::user()->type == 'super admin')
                        <div class="d-flex align-items-center justify-content-center">
                            <a title="{{ __('Edit Plan') }}" href="#" class="btn btn-primary btn-icon m-1 badge"
                            data-url="{{ route('plans.edit', $plan->id) }}" data-ajax-popup="true"
                            data-title="{{ __('Edit Plan') }}" data-size="lg" data-toggle="tooltip"
                            data-original-title="{{ __('Edit') }}">
                                <i class="ti ti-edit"></i>
                            </a>
                            {{-- @if($plan->price > 0) --}}
                                {!! Form::open([
                                    'method' => 'DELETE',
                                    'route' => ['plans.destroy', $plan->id],
                                    'id' => 'delete-form-' . $plan->id,
                                ]) !!}
                                <a href="#!" class="bs-pass-para btn-icon mx-2 btn btn-danger btn-icon m-1 badge" data-bs-toggle="tooltip"
                                data-bs-original-title="{{ __('Delete') }}">
                                    <i class="ti ti-trash"></i>
                                </a>
                                {!! Form::close() !!}
                            {{-- @endif --}}
                        </div>
                    @endif

                    @if (\Auth::user()->type != 'super admin')
                        <div class="request-btn">
                            <div class="d-flex align-items-center">
                            @if (
                                $plan->price > 0 &&
                                \Auth::user()->trial_plan == 0 &&
                                \Auth::user()->plan != $plan->id && $plan->trial == 1)
                                <a href="{{ route('plan.trial', \Illuminate\Support\Facades\Crypt::encrypt($plan->id)) }}"
                                class="btn btn-lg btn-primary m-2">{{ __('Start Free Trial') }}</a>
                            @endif
                            @if ($plan->id != \Auth::user()->plan)
                                @if ($plan->price > 0)
                                    <a href="{{ route('stripe', \Illuminate\Support\Facades\Crypt::encrypt($plan->id)) }}"
                                    class="btn btn-lg btn-primary m-2">{{ __('Buy Plan') }}</a>
                                @endif
                            @endif
                            </div>
                        </div>
                    @endif

                    @if (\Auth::user()->type == 'company' && \Auth::user()->trial_expire_date)
                        @if (\Auth::user()->type == 'company' && \Auth::user()->trial_plan == $plan->id)
                            <p class="display-total-time mb-0">
                                {{ __('Plan Trial Expiry : ') }}
                                {{ !empty(\Auth::user()->trial_expire_date) ? \Auth::user()->dateFormat(\Auth::user()->trial_expire_date) : 'lifetime' }}
                            </p>
                        @endif
                    @else
                        @if (\Auth::user()->type == 'company' && \Auth::user()->plan == $plan->id)
                            <p class="display-total-time mb-0">
                                {{ __('Plan Expiry : ') }}
                                {{ !empty(\Auth::user()->plan_expire_date) ? \Auth::user()->dateFormat(\Auth::user()->plan_expire_date) : 'lifetime' }}
                            </p>
                        @endif
                    @endif

                </div>
            </div>
        </div>
    @endforeach
    </div>
@endsection

@push('script-page')
    <script>
        $(document).on('change', '#trial', function() {
            if ($(this).is(':checked')) {
                $('.plan_div').removeClass('d-none');
                $('#trial_days').attr("required", true);

            } else {
                $('.plan_div').addClass('d-none');
                $('#trial_days').removeAttr("required");
            }
        });
    </script>

    <script>
        $(document).on("click", ".is_disable", function() {

        var id = $(this).attr('data-id');
        var is_disable = ($(this).is(':checked')) ? $(this).val() : 0;

        $.ajax({
            url: '{{ route('plan.disable') }}',
            type: 'POST',
            data: {
                "is_disable": is_disable,
                "id": id,
                "_token": "{{ csrf_token() }}",
            },
            success: function(data) {
                if (data.success) {
                    show_toastr('success', data.success);
                } else {
                    show_toastr('error', data.error);

                }

            }
        });
    });
</script>
@endpush
