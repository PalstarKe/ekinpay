{{ Form::open(['url' => route('customer.updateExpiry', ['id' => $customer->id]), 'method' => 'post', 'id' => 'customer_'.$customer->id, 'class' => 'needs-validation', 'novalidate']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-lg-6 col-md-4 col-sm-6">
            <div class="form-group">
                <label for="flatpickr-datetime" class="form-label">Datetime Picker</label>
                <input type="text" class="form-control" placeholder="YYYY-MM-DD HH:MM" id="flatpickr-datetime" />
                {{ Form::label('expiry', __('Expiry Date'),['class'=>'text-type']) }}
                {{ Form::text('expiry', null, array('class' => 'form-control' 'id' => 'flatpickr-datetime')) }}
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-secondary" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
</div>
{{Form::close()}}
