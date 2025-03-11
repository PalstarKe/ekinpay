{{ Form::open(array('url' => 'packages', 'enctype' => "multipart/form-data", 'class'=>'needs-validation', 'novalidate')) }}
    <div class="modal-body">
        <div class="row g-2">
            <div class="form-group col-md-6">
                {{ Form::label('device', __('Device'),['class'=>'form-label']) }}<x-required></x-required>
                {!! Form::select('device', $arrDevices, null,array('class' => 'form-control select','required'=>'required')) !!}
            </div>

            <div class="form-group col-md-6">
                {{ Form::label('name_plan', __('Plan Name'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::text('name_plan', null, ['class' => 'form-control', 'required' => true, 'placeholder' => __('Enter Plan Name')]) }}
            </div>

            <div class="form-group col-md-6">
                {{ Form::label('price', __('Plan Price'),['class'=>'form-label']) }}<x-required></x-required>
                {{ Form::number('price', '', array('class' => 'form-control','required'=>'required', 'placeholder'=>__('Enter Plan Price'))) }}
            </div>
            <div class="form-group col-md-6">
                {{ Form::label('type', __('Plan Type'),['class'=>'form-label']) }}<x-required></x-required>
                {!! Form::select('type', $arrType, null,array('class' => 'form-control select','required'=>'required')) !!}
            </div>
            <div class="form-group col-md-6">
                {{ Form::label('shared_users', __('Shared Users'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::text('shared_users', null, ['class' => 'form-control', 'placeholder' => __('Enter Shared Users')]) }}
            </div>
            <div class="form-group row col-md-6 g-2">
                <div class="col-md-6">
                    {{ Form::label('validity', __('Plan Validity'),['class'=>'form-label']) }}<x-required></x-required>
                    {{ Form::number('validity', '', array('class' => 'form-control','required'=>'required', 'placeholder'=>__('Plan Validity'))) }}
                </div>
                <div class="col-md-6">
                    {{ Form::label('validity_unit', __('Validity Unit'),['class'=>'form-label']) }}<x-required></x-required>
                    {!! Form::select('validity_unit', $arrValidity, null,array('class' => 'form-control select','required'=>'required')) !!}
                </div>
            </div>

            <div class="form-group row col-md-6 g-2">
                <div class="col-md-6">
                    {{ Form::label('rate_down', __('Download Speed'),['class'=>'form-label']) }}<x-required></x-required>
                    {{ Form::number('rate_down', '', array('class' => 'form-control','required'=>'required','step'=>'0.01' , 'placeholder'=>__('Download Speed'))) }}
                </div>
                <div class="col-md-6">
                    {{ Form::label('rate_down_unit', __('Speed Limit'),['class'=>'form-label']) }}<x-required></x-required>
                    {!! Form::select('rate_down_unit', $arrSpeed, null,array('class' => 'form-control select','required'=>'required')) !!}
                </div>
            </div>
            <div class="form-group row col-md-6 g-2">
                <div class="col-md-6">
                    {{ Form::label('rate_up', __('Upload Speed'),['class'=>'form-label']) }}<x-required></x-required>
                    {{ Form::number('rate_up', '', array('class' => 'form-control','required'=>'required','step'=>'0.01' , 'placeholder'=>__('Upload Speed'))) }}
                </div>
                <div class="col-md-6">
                    {{ Form::label('rate_up_unit', __('Speed Limit'),['class'=>'form-label']) }}<x-required></x-required>
                    {!! Form::select('rate_up_unit', $arrSpeed, null,array('class' => 'form-control select','required'=>'required')) !!}
                </div>
            </div>
            <div class="form-group col-md-6">
                <label for="burst" class="form-label">{{ __('Burst is enable(on/off)') }}</label>
                <div class="form-check form-switch custom-switch-v1 float-end">
                    <input type="checkbox" name="enable_burst" class="form-check-input input-primary pointer" value="1" id="enable_burst" {{ old('enable_burst', $_c['enable_burst'] ?? 0) == 1 ? 'checked' : '' }}>
                    <label class="form-check-label" for="enable_burst"></label>
                </div>
            </div>
            <div id="burst_input" class="burst_div d-none">
                <div class="form-group col-md-6">
                    {{ Form::label('burst_limit', __('Burst Limit'), ['class' => 'form-label']) }}<x-required></x-required>
                    {{ Form::text('burst_limit', null, ['class' => 'form-control', 'placeholder' => __('Enter Burst Limit')]) }}
                </div>
                <div class="form-group col-md-6">
                    {{ Form::label('burst_threshold', __('Burst Threshold'), ['class' => 'form-label']) }}<x-required></x-required>
                    {{ Form::text('burst_threshold', null, ['class' => 'form-control', 'placeholder' => __('Enter Burst Threshold')]) }}
                </div>
                <div class="form-group col-md-6">
                    {{ Form::label('burst_time', __('Burst Time'), ['class' => 'form-label']) }}<x-required></x-required>
                    {{ Form::text('burst_time', null, ['class' => 'form-control', 'placeholder' => __('Enter Burst Time')]) }}
                </div>
                <div class="form-group col-md-6">
                    {{ Form::label('burst_priority', __('Priority'), ['class' => 'form-label']) }}<x-required></x-required>
                    {{ Form::number('burst_priority', null, ['class' => 'form-control', 'placeholder' => __('Enter Burst Priority')]) }}
                </div>
                <div class="form-group col-md-6">
                    {{ Form::label('burst_limit_at', __('Limit At'), ['class' => 'form-label']) }}<x-required></x-required>
                    {{ Form::text('burst_limit_at', null, ['class' => 'form-control', 'placeholder' => __('Enter Burst Limit At')]) }}
                </div>
                {{ Form::hidden('burst', null, ['id' => 'burst_combined']) }}
            </div> 
        </div>
    </div>
    
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="btn btn-secondary" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
    </div>
{{ Form::close() }}

