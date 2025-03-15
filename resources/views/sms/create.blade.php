{{ Form::open(array('url' => 'sms', 'enctype' => "multipart/form-data", 'class'=>'needs-validation', 'novalidate')) }}
    <div class="modal-body">
        <div class="row g-2">
            <div class="form-group col-md-12">
                {{ Form::label('type', __('Template Name'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::text('type', null, ['class' => 'form-control', 'required' => true, 'placeholder' => __('Enter Template Name')]) }}
            </div>
            <div class="form-group col-md-12">
                <div class="text-light small fw-medium">Placeholders</div>
                    <div class="demo-inline-spacing">
                        <span class="badge bg-label-info">{username}</span>
                        <span class="badge bg-label-info">{contact}</span>
                        <span class="badge bg-label-info">{account}</span>
                        <span class="badge bg-label-info">{balance}</span>
                        <span class="badge bg-label-info">{amount}</span>
                        <span class="badge bg-label-info">{company}</span>
                        <span class="badge bg-label-info">{support}</span>
                        <span class="badge bg-label-info">{package}</span>
                        <span class="badge bg-label-info">{expiry}</span>
                        <span class="badge bg-label-info">{paybill}</span>
                    </div>
                </div>
            </div>
            <div class="form-group col-md-12">
                {{ Form::label('template', __('Template'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::textarea('template', null,['class'=>'form-control','rows'=>'2', 'placeholder' => __('Enter Template')]) }}
            </div>
        </div>
    </div>
    
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="btn btn-secondary" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
    </div>
{{ Form::close() }}

