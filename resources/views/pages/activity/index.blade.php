@extends('layouts.medium')

@section('title')
    {{ trans('user.activity.index.title', ['user' => $user->name]) }}
@stop

@section('content.medium')

@foreach ($items as $item)
    

    <div class="utils-border-bottom">

    @if ($item->type == 'photo')

        <div class="utils-padding-bottom">

        @include('component.card', [
            'image' => $item->imagePath(),
            'options' => '-empty -wide'
        ])

        </div>

    @endif


    @if (isset($item->type)) 

        @include('component.row', [
            'image' => $item->user->imagePath(),
            'text' => trans('user.activity.index.row.content', [
                'user' => $item->user->name,
                'title' => '<a href="'
                    . route('content.show', [$item->type, $item->id])
                    . '">'
                    . $item->title
                    . '</a>',
                'created_at' => $item->created_at->diffForHumans()
            ])
        ])

    @else

        @include('component.row', [
            'image' => $item->user->imagePath(),
            'text' => trans('user.activity.index.row.comment', [
                'user' => $item->user->name,
                'title' => '<a href="'
                    . route('content.show', [$item->content->type, $item->content->id])
                    . '">'
                    . $item->content->title
                    . '</a>',
                'comment_title' => '<a href="'
                    . route('content.show', [$item->content->type, $item->content->id, '#content-' . $item->id])
                    . '">'
                    . $item->title
                    . '</a>',
                'created_at' => $item->created_at->diffForHumans()
            ])
        ])

    @endif

    </div>

@endforeach

@stop