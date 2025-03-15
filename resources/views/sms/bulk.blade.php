{{ Form::open(['route' => 'sms.bulk.send', 'method' => 'POST', 'class'=>'needs-validation', 'novalidate']) }}
    <div class="modal-body">
        <div class="row g-2">
            <div class="form-group">
                {{ Form::select('category', [
                    'expired' => 'Expired', 
                    'active' => 'Active', 
                    'suspended' => 'Suspended', 
                    'disabled' => 'Disabled', 
                    'new' => 'New Customers', 
                    'smsgroup' => 'SMS Group'
                ], null, [
                    'class' => 'form-control', 
                    'required', 
                    'onchange' => "document.getElementById('sms_group').style.display = this.value === 'smsgroup' ? 'block' : 'none';"
                ]) }}
            </div>
            <div class="form-group mt-2" id="sms_group" style="display: none;">
                {{ Form::label('sms_group', __('Select SMS Group')) }}
                {{ Form::select('sms_group', $smsGroups->pluck('sms_group', 'sms_group'), null, ['class' => 'form-control']) }}
            </div>
            <div class="form-group mt-3">
                {{ Form::select('message_type', ['template' => 'Use Template', 'custom' => 'Custom Message'], null, [
                    'class' => 'form-control', 
                    'required', 
                    'onchange' => "document.getElementById('template_section').style.display = this.value === 'template' ? 'block' : 'none';
                                document.getElementById('custom_message_section').style.display = this.value === 'custom' ? 'block' : 'none';"
                ]) }}
            </div>
            <div class="form-group mt-2" id="template_section" style="display: none;">
                {{ Form::label('template_id', __('Select Template')) }}
                {{ Form::select('template_id', $smsTemplates->pluck('type', 'id'), null, ['class' => 'form-control']) }}
            </div>
            <div id="custom_message_section" style="display: none;">
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
                <!-- </div> -->
                <!-- <div class="form-group col-md-12">
                    <div class="form-group mt-2" > -->
                        {{ Form::label('message', __('Custom Message')) }}
                        {{ Form::textarea('message', null, ['class' => 'form-control', 'rows' => 3]) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="btn btn-secondary" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Update') }}" class="btn btn-primary">
    </div>
{{ Form::close() }}
