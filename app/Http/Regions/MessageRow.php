<?php

namespace App\Http\Regions;

class MessageRow
{
    public function render($message, $user)
    {
        return component('MessageRow')
            ->with('id', $message->id)
            ->with('title', trans('message.index.row.description', [
                'user' => $message->fromUser->vars()->name,
                'created_at' => $message->vars()->created_at,
            ]))
            ->with('user', component('UserImage')
                ->with('route', route('user.show', [$message->withUser]))
                ->with('image', $message->withUser->vars()->imagePreset('small_square'))
                ->with('rank', $message->withUser->vars()->rank)
                ->with('size', 32)
                ->with('border', 3)
            )
            ->with('route', route(
                'message.index.with',
                [$user, $message->withUser])
            );
    }
}
