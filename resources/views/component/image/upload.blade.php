<div>

{!! Form::open(array('url' => route('admin.image.store'),'files' => true)) !!}

        <div class="form-group">
            
        {!! Form::file('image') !!}
        {!! Form::submit(trans('image.upload.submit.btn.title'), [
            'class' => 'btn btn-primary btn-md btn-block'
        ]) !!}
            
        </div>           

    {!! Form::close() !!}
</div>