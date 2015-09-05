@extends('layouts.main')

@section('title')
    
    {{ trans("image.index.title") }}

@stop

@section('content')

    <div class="row">
  
        @foreach ($images as $index => $image)

            <div class="col-xs-4 col-sm-2 utils-padding-bottom">
                
                <div class="utils-padding-bottom">

                @include('component.card', [
                    'image' => $image->preset(),
                    'options' => '-noshade'
                ])
                
                </div>

                <div class="form-group">

                {!! Form::text('id', "[[$image->id]]", [
                    'class' => 'form-control input-md',
                ]) !!}

                </div>

                {{ $image->filename }}

            </div>
            
            @if (($index + 1) % 6 == 0) </div><div class="row"> @endif

        @endforeach

    </div>

    {!! $images->render() !!}

@stop

