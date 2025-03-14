

{{ Form::open(['route' => 'customer.import', 'method' => 'post', 'enctype' => 'multipart/form-data', 'class' => 'needs-validation', 'novalidate', 'id' => 'upload_form']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-12 mb-3">
            {{ Form::label('file', __('Download sample customer CSV file'), ['class' => 'form-label']) }}
            <a href="{{ asset(Storage::url('uploads/sample')).'/sample-customer.csv' }}" download class="btn btn-sm btn-primary">
                <i class="ti ti-download"></i> {{ __('Download') }}
            </a>
        </div>
        <div class="col-md-12">
            {{ Form::label('file', __('Select CSV File'), ['class' => 'form-label']) }}
            <div class="choose-file form-group">
                <input type="file" class="form-control" name="file" id="file" required>
                <p class="upload_file"></p>
                <input type="hidden" name="table" id="table" value="customers">
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    <button type="submit" class="btn btn-primary">{{ __('Upload') }}</button>
</div>
{{ Form::close() }}
