{{Form::open(array('url'=>'customer','method'=>'post', 'class'=>'needs-validation', 'novalidate'))}}
<div class="modal-body">
    <div class="row">
        <div class="col-lg-6 col-md-4 col-sm-6">
            <div class="form-group">
                {{Form::label('fullname',__('Full Name'),array('class'=>'form-label')) }}<x-required></x-required>
                {{Form::text('fullname',null,array('class'=>'form-control','required'=>'required' ,'placeholder'=>__('Enter Full Name')))}}
            </div>
        </div>
        <div class="col-lg-6 col-md-4 col-sm-6">
            <div class="form-group">
                {{Form::label('contact',__('Contact'),['class'=>'form-label'])}}<x-required></x-required>
                {{Form::number('contact',null,array('class'=>'form-control','required'=>'required' , 'placeholder'=>__('Enter Contact')))}}
            </div>
        </div>
        <div class="col-lg-12 col-md-12 col-sm-6">
            <div class="form-group">
                {{Form::label('email',__('Email'),['class'=>'form-label'])}}
                {{Form::email('email',null,array('class'=>'form-control' , 'placeholder'=>__('Enter email')))}}

            </div>
        </div>
        <div class="col-lg-6 col-md-4 col-sm-6">
            <div class="form-group">
                {{Form::label('account',__('Account'),array('class'=>'form-label')) }}<x-required></x-required>
                {{ Form::text('account', $customerN, ['class' => 'form-control', 'required' => 'required', 'readonly' => 'readonly', 'placeholder' => __('Enter Account')]) }}
            </div>
        </div>
        <div class="col-lg-6 col-md-4 col-sm-6">
            <div class="form-group">
                {{Form::label('username',__('Old Username'),['class'=>'form-label'])}}
                {{Form::text('username',null,array('class'=>'form-control', 'placeholder'=>__('Enter Old Username')))}}
            </div>
        </div>
        <div class="col-lg-6 col-md-4 col-sm-6">
            <div class="form-group">
                {{Form::label('password',__('Secret'),['class'=>'form-label'])}}<x-required></x-required>
                {{ Form::text('password', $customerN, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter Password')]) }}
            </div>
        </div>
        <div class="col-lg-6 col-md-4 col-sm-6">
            <div class="form-group">
                {{Form::label('housenumber',__('House Number'),['class'=>'form-label'])}}
                {{Form::text('housenumber',null,array('class'=>'form-control' , 'placeholder' => __('B5')))}}
            </div>
        </div>
        <div class="col-lg-6 col-md-4 col-sm-6">
            <div class="form-group">
                {{Form::label('apartment',__('Apartmnent'),array('class'=>'form-label')) }}
                {{Form::text('apartment',null,array('class'=>'form-control','placeholder'=>__('Future Flats')))}}
            </div>
        </div>
        <div class="col-lg-6 col-md-4 col-sm-6">
            <div class="form-group">
                {{Form::label('location',__('Location'),['class'=>'form-label'])}}
                {{Form::text('location',null,array('class'=>'form-control', 'placeholder'=>__('Nairobi')))}}
            </div>
        </div>
        <div class="col-lg-6 col-md-4 col-sm-6">
            <div class="form-group">
                {{Form::label('service',__('Service'),['class'=>'form-label'])}}<x-required></x-required>
                {!! Form::select('service', $arrType, null,array('class' => 'form-control select','required'=>'required', 'readonly'=>'readonly')) !!}
            </div>
        </div>
        <div class="col-lg-6 col-md-4 col-sm-6">
            <div class="form-group">
                {{Form::label('package',__('Select Package'),['class'=>'form-label'])}}<x-required></x-required>
                {!! Form::select('package', array_combine($arrPackage, $arrPackage), null, ['class' => 'form-control select', 'required' => 'required']) !!}
            </div>
        </div>
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
                {{Form::label('charges',__('Installation Fee'),['class'=>'form-label'])}}
                {{Form::number('charges',null,array('class'=>'form-control', 'placeholder'=>__('Installation Fee')))}}
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-secondary" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
</div>


{{Form::close()}}
