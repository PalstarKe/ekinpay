{{ Form::open(array('url' => 'packages', 'enctype' => "multipart/form-data", 'class'=>'needs-validation', 'novalidate')) }}
    <div class="modal-body">
        <div class="row g-2">
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('device', __('Device'), ['class'=>'form-label']) }}<x-required></x-required>
                    {!! Form::select('device', $arrDevices, null, ['class' => 'form-control select','required'=>'required']) !!}
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('name_plan', __('Plan Name'), ['class'=>'form-label']) }}<x-required></x-required>
                    {{ Form::text('name_plan', null, ['class' => 'form-control', 'required' => true, 'placeholder' => __('Enter Plan Name')]) }}
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('price', __('Plan Price'), ['class'=>'form-label']) }}<x-required></x-required>
                    {{ Form::number('price', '', ['class' => 'form-control', 'required' => 'required', 'placeholder'=>__('Enter Plan Price')]) }}
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('type', __('Plan Type'), ['class'=>'form-label']) }}<x-required></x-required>
                    {!! Form::select('type', $arrType, null, ['class' => 'form-control select','required'=>'required']) !!}
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('shared_users', __('Shared Users'), ['class'=>'form-label']) }}<x-required></x-required>
                    {{ Form::text('shared_users', null, ['class' => 'form-control', 'placeholder' => __('Enter Shared Users')]) }}
                </div>
            </div>

            {{-- Plan Validity & Unit --}}
            <div class="col-md-3">
                <div class="form-group">
                    {{ Form::label('validity', __('Plan Validity'), ['class'=>'form-label']) }}<x-required></x-required>
                    {{ Form::number('validity', '', ['class' => 'form-control','required'=>'required', 'placeholder'=>__('Validity')]) }}
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {{ Form::label('validity_unit', __('Validity Unit'), ['class'=>'form-label']) }}<x-required></x-required>
                    {!! Form::select('validity_unit', $arrValidity, null, ['class' => 'form-control select','required'=>'required']) !!}
                </div>
            </div>

            {{-- Download + Upload in one row --}}
            <div class="col-md-3">
                <div class="form-group">
                    {{ Form::label('rate_down', __('Download Speed'), ['class'=>'form-label']) }}<x-required></x-required>
                    {{ Form::number('rate_down', '', ['class' => 'form-control','required'=>'required','step'=>'0.01' , 'placeholder'=>__('Download Speed')]) }}
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {{ Form::label('rate_down_unit', __('Speed Unit'), ['class'=>'form-label']) }}<x-required></x-required>
                    {!! Form::select('rate_down_unit', $arrSpeed, null, ['class' => 'form-control select','required'=>'required']) !!}
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {{ Form::label('rate_up', __('Upload Speed'), ['class'=>'form-label']) }}<x-required></x-required>
                    {{ Form::number('rate_up', '', ['class' => 'form-control','required'=>'required','step'=>'0.01' , 'placeholder'=>__('Upload Speed')]) }}
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {{ Form::label('rate_up_unit', __('Speed Unit'), ['class'=>'form-label']) }}<x-required></x-required>
                    {!! Form::select('rate_up_unit', $arrSpeed, null, ['class' => 'form-control select','required'=>'required']) !!}
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('tax_value', __('Tax Percentage (Optional)'), ['class'=>'form-label']) }}
                    {{ Form::number('tax_value', '', ['class' => 'form-control', 'placeholder'=>__('16% VAT')]) }}
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('tax_type', __('Tax Type (Optional)'), ['class'=>'form-label']) }}
                    {!! Form::select('tax_type', $arrTax, null, ['class' => 'form-control select']) !!}
                </div>
            </div>
            <div class="form-group col-md-6">
                <label for="burst" class="form-label">{{ __('Burst is enable(on/off)') }}</label>
                <div class="form-check form-switch custom-switch-v1 float-end">
                    <input type="checkbox" name="enable_burst" class="form-check-input input-primary pointer" value="1" id="enable_burst">
                    <label class="form-check-label" for="enable_burst"></label>
                </div>
            </div>
            <div id="burst_input" class="burst_div d-none row g-2">
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
                <div class="form-group col-md-12">
                    {{ Form::label('burst_limit_at', __('Limit At'), ['class' => 'form-label']) }}<x-required></x-required>
                    {{ Form::text('burst_limit_at', null, ['class' => 'form-control', 'placeholder' => __('Enter Burst Limit At')]) }}
                </div>
                {{ Form::hidden('burst', null, ['id' => 'burst_combined']) }}
            </div> 
            <div class="form-group col-md-6">
                <label for="burst" class="form-label">{{ __('Enable FUP(on/off)') }}</label>
                <div class="form-check form-switch custom-switch-v1 float-end">
                    <input type="checkbox" name="enable_fup" class="form-check-input input-primary pointer" value="1" id="enable_fup">
                    <label class="form-check-label" for="enable_fup"></label>
                </div>
            </div>
            <div id="fup_input" class="fup_div d-none">
                {{-- FUP Limit and Unit --}}
                <div class="row g-2">
                    <div class="col-md-6">
                        {{ Form::label('fup_limit', __('FUP Data Limit'), ['class' => 'form-label']) }}<x-required></x-required>
                        {{ Form::number('fup_limit', '', ['class' => 'form-control', 'required' => 'required', 'step' => '0.01', 'placeholder' => __('FUP Data Limit')]) }}
                    </div>
                    <div class="col-md-6">
                        {{ Form::label('fup_unit', __('FUP Data Unit'), ['class' => 'form-label']) }}<x-required></x-required>
                        {!! Form::select('fup_unit', $arrfup, null, ['class' => 'form-control select', 'required' => 'required']) !!}
                    </div>
                </div>

                <div class="row g-2 mt-2">
                    {{-- Download Speed --}}
                    <div class="col-md-3">
                        {{ Form::label('fup_down_speed', __('FUP Download'), ['class' => 'form-label']) }}<x-required></x-required>
                        {{ Form::number('fup_down_speed', '', [
                            'class' => 'form-control',
                            'required' => 'required',
                            'step' => '0.01',
                            'placeholder' => __('Download Rate')
                        ]) }}
                    </div>

                    {{-- Download Unit --}}
                    <div class="col-md-3">
                        {{ Form::label('fup_down_unit', __('Unit'), ['class' => 'form-label']) }}<x-required></x-required>
                        {!! Form::select('fup_down_unit', $arrSpeed, null, [
                            'class' => 'form-control select',
                            'required' => 'required'
                        ]) !!}
                    </div>

                    {{-- Upload Speed --}}
                    <div class="col-md-3">
                        {{ Form::label('fup_up_speed', __('FUP Upload'), ['class' => 'form-label']) }}<x-required></x-required>
                        {{ Form::number('fup_up_speed', '', [
                            'class' => 'form-control',
                            'required' => 'required',
                            'step' => '0.01',
                            'placeholder' => __('Upload Rate')
                        ]) }}
                    </div>

                    {{-- Upload Unit --}}
                    <div class="col-md-3">
                        {{ Form::label('fup_up_unit', __('Unit'), ['class' => 'form-label']) }}<x-required></x-required>
                        {!! Form::select('fup_up_unit', $arrSpeed, null, [
                            'class' => 'form-control select',
                            'required' => 'required'
                        ]) !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="btn btn-secondary" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
    </div>
{{ Form::close() }}

