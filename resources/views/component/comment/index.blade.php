@if (count($comments))

@foreach ($comments as $index => $comment)

    <div
        id="comment-{{ $comment->id }}"
        class="
        @if (count($comments) == ($index + 1))
            utils-padding-bottom 
        @else
            utils-border-bottom
        @endif
        @if (! $comment->status)
            utils-unpublished
        @endif
    ">

        @include('component.row', [
            'image' => $comment->user->imagePreset('xsmall_square'),
            'image_link' => route('user.show', [$comment->user]),
            'description' => view('component.comment.description', ['comment' => $comment]),
            'actions' => view('component.actions', ['actions' => $comment->getActions()]),
            'extra' => view('component.flags', ['flags' => $comment->getFlags()]),
            'body' => nl2br($comment->body),
            'options' => isset($options) ? '-small ' . $options : '-small' 

        ])

    </div>
    
@endforeach

@endif
