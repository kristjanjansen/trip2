<?php

namespace App\Http\Controllers;

use App\User;
use App\Message;

class V2MessageController extends Controller
{
    public function index($user_id)
    {
        $user = User::findorFail($user_id);
        $messages = collect($user->messages());

        return layout('1col')

            ->with('header', region('UserHeader', $user))

            ->with('content', collect()
                ->push(component('Title')
                    ->with('title', trans('message.index.title'))
                )
                ->merge($messages->map(function ($message) use ($user) {
                    return region('MessageRow', $message, $user);
                }))
            )

            ->with('footer', region('Footer'))

            ->render();
    }

    public function indexWith($user_id, $user_id_with)
    {
        $user = User::findorFail($user_id);
        $user_with = User::findorFail($user_id_with);

        $messages = $user->messagesWith($user_id_with);

        // Mark messages as read

        $messageIds = $user
            ->messagesWith($user_id_with)
            ->where('user_id_to', $user->id)
            ->keyBy('id')
            ->keys()
            ->toArray();

        Message::whereIn('id', $messageIds)->update(['read' => 1]);

        return layout('1col')

            ->with('header', region('UserHeader', $user))

            ->with('content', collect()
                ->push(component('Title')
                    ->with('title', trans('message.index.row.description', [
                        'user' => $user_with->vars()->name,
                        'created_at' => $messages->last()->vars()->created_at,
                    ]))
                )
                ->merge($messages->map(function ($message) use ($user) {
                    return region('MessageWithRow', $message);
                }))
            )

            ->with('footer', region('Footer'))

            ->render();
    }
}
