@extends('layouts/layoutMaster')
@push('script-page')
    <script>
        $(document).on('click', '.code', function () {
            var type = $(this).val();
            if (type == 'manual') {
                $('#manual').removeClass('d-none');
                $('#manual').addClass('d-block');
                $('#auto').removeClass('d-block');
                $('#auto').addClass('d-none');
            } else {
                $('#auto').removeClass('d-none');
                $('#auto').addClass('d-block');
                $('#manual').removeClass('d-block');
                $('#manual').addClass('d-none');
            }
        });

        $(document).on('click', '#code-generate', function () {
            var length = 10;
            var result = '';
            var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            var charactersLength = characters.length;
            for (var i = 0; i < length; i++) {
                result += characters.charAt(Math.floor(Math.random() * charactersLength));
            }
            $('#auto-code').val(result);
        });
        $(document).on('click', '#manual_code', function () {
            var result = null;
            $('#auto-code').val(result);
        });
        $(document).on('click', '#auto_code', function () {
            var result = null;
            $('#manual-code').val(result);
        });
    </script>
@endpush
@section('page-title')
    {{__('Manage Coupon')}}
@endsection

@section('content')
    <div class="row">
        <div class="float-end mb-2">
            @can('create coupon')
                <a href="#" data-size="lg" data-url="{{ route('coupons.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Create')}}" data-title="{{__('Create New Coupon')}}" class="btn btn-sm btn-primary">
                    <i class="ti ti-plus"></i> {{__('Create New Coupon')}}
                </a>
            @endcan
        </div>
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                                <tr>
                                <th> {{__('Name')}}</th>
                                <th> {{__('Code')}}</th>
                                <th> {{__('Discount (%)')}}</th>
                                <th> {{__('Limit')}}</th>
                                <th> {{__('Used')}}</th>
                                <th> {{__('Action')}}</th>
                            </tr>
                            </thead>

                            <tbody>
                                @foreach ($coupons as $coupon)
                                    <tr class="font-style">
                                        <td>{{ $coupon->name }}</td>
                                        <td>{{ $coupon->code }}</td>
                                        <td>{{ $coupon->discount }}</td>
                                        <td>{{ $coupon->limit }}</td>
                                        <td>{{ $coupon->used_coupon() }}</td>
                                        <td class="Action">
                                            <div class="d-flex align-items-center">
                                                <a href="{{ route('coupons.show', $coupon->id) }}" class="btn btn-sm bg-warning text-white me-2" 
                                                    data-bs-toggle="tooltip" title="{{ __('View') }}">
                                                    <i class="ti ti-eye"></i>
                                                </a>

                                                @can('edit coupon')
                                                    <a href="#" class="btn btn-sm bg-info text-white me-2"
                                                        data-url="{{ route('coupons.edit', $coupon->id) }}" data-ajax-popup="true"
                                                        data-title="{{ __('Edit Coupon') }}" data-bs-toggle="tooltip" title="{{ __('Edit') }}">
                                                        <i class="ti ti-pencil"></i>
                                                    </a>
                                                @endcan

                                                @can('delete coupon')
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['coupons.destroy', $coupon->id], 'id' => 'delete-form-' . $coupon->id, 'class' => 'd-inline']) !!}
                                                        <a href="#" class="btn btn-sm bg-danger text-white" data-bs-toggle="tooltip" title="{{ __('Delete') }}"
                                                            data-confirm="{{ __('Are You Sure?').'|'.__('This action cannot be undone. Do you want to continue?') }}"
                                                            data-confirm-yes="document.getElementById('delete-form-{{ $coupon->id }}').submit();">
                                                            <i class="ti ti-trash"></i>
                                                        </a>
                                                    {!! Form::close() !!}
                                                @endcan
                                            </div>
                                        </td>
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
