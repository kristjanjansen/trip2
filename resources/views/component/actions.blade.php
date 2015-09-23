@if (count($actions))

<div class="component-actions">

    @foreach($actions as $action)

        {!! Form::open([
            'url' => $action['route'],
            'method' => isset($action['method']) ? $action['method'] : 'GET'
        ]) !!}

        {!! Form::submit($action['title'], [
            'class' => 'btn btn-xs btn-default'
        ]) !!}

        {!! Form::close() !!}

    @endforeach

</div>

@endif