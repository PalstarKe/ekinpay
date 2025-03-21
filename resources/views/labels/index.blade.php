@extends('layouts/layoutMaster')

@section('page-title')
    {{__('Manage Labels')}}
@endsection

@section('content')

    <div class="row">
        @can('create label')
            <div class="float-end">
                <a href="#" data-size="md" data-url="{{ route('labels.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Create Labels')}}" class="btn btn-sm btn-primary">
                    <i class="ti ti-plus"></i> {{__('Create Labels')}}
                </a>
            </div>
        @endcan
        @include('layouts.crm_setup')
        <div class="col-lg-9">
            <div class="row justify-content-center">

                <div class="p-3 card">
                    <ul class="nav nav-pills nav-fill" id="pills-tab" role="tablist">
                        @php($i=0)
                        @foreach($pipelines as $key => $pipeline)
                            <li class="nav-item" role="presentation">
                                <button class="nav-link @if($i==0) active @endif" id="pills-user-tab-1" data-bs-toggle="pill"
                                        data-bs-target="#tab{{$key}}" type="button">{{$pipeline['name']}}
                                </button>
                            </li>
                            @php($i++)
                        @endforeach
                    </ul>
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="tab-content" id="pills-tabContent">
                            @php($i=0)
                            @foreach($pipelines as $key => $pipeline)
                                <div class="tab-pane fade show @if($i==0) active @endif" id="tab{{$key}}" role="tabpanel" aria-labelledby="pills-user-tab-1">
                                    <ul class="list-group sortable">
                                        @foreach ($pipeline['labels'] as $label)
                                            <li class="list-group-item" data-id="{{$label->id}}">
                                                <span class="text-sm text-dark">{{$label->name}}</span>
                                                <span class="float-end">

                                                @can('edit label')
                                                        <div class="action-btn me-2">
                                                        <a href="#" class="mx-3 btn btn-sm align-items-center bg-info" data-url="{{ URL::to('labels/'.$label->id.'/edit') }}" data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-title="{{__('Edit Labels')}}">
                                                            <i class="ti ti-pencil text-white"></i>
                                                        </a>
                                                    </div>
                                                    @endcan
                                                    @if(count($pipeline['labels']))
                                                        @can('delete label')
                                                            <div class="action-btn ">
                                                                {!! Form::open(['method' => 'DELETE', 'route' => ['labels.destroy', $label->id]]) !!}
                                                                <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para bg-danger" data-bs-toggle="tooltip" title="{{__('Delete')}}"><i class="ti ti-trash text-white"></i></a>
                                                                {!! Form::close() !!}
                                                            </div>
                                                        @endcan
                                                    @endif
                                            </span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                @php($i++)
                            @endforeach
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>


@endsection
