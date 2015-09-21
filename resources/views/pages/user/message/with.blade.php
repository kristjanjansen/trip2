@extends('layouts.main')

@section('title')

    {{ trans('message.index.with.title', ['user' => $user->name, 'user_with' => $user_with->name]) }}

@stop

@section('navbar.bottom')
    
    @if (\Auth::check() && \Auth::user()->hasRoleOrOwner('superuser', $user->id))
        
        <div class="utils-border-bottom">
            
            @include('component.user.menu', ['user' => $user])
        
        </div>

    @endif

@stop

@section('content')

@if (count($messages))

@foreach ($messages as $message)

    <div

        id="message-{{ $message->id }}"
        class="utils-border-bottom @if ($message->read) utils-read @endif"

    >

    @include('component.row', [
        'image' => $message->fromUser->imagePreset(),
        'text' => trans('message.index.with.row.text', [
            'user' => $message->fromUser->name,
            'created_at' => view('component.date.long', ['date' => $message->created_at])
        ]),
        'body' => nl2br($message->body),
        'options' => '-narrow -small'
    ])

    </div>

@endforeach

@endif

    @include('component.message.create', [
        'user_from' => $user,
        'user_to' => $user_with
    ])

@stop