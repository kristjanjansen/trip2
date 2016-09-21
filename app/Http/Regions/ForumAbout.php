<?php

namespace App\Http\Regions;

class ForumAbout
{
    public function render($is = '')
    {
        $type = 'forum';
        $user = auth()->user();

        return component('Block')
            ->is($is)
            ->with('content', collect()
                ->push(component('Body')
                    ->with('body', trans("site.description.$type"))
                )
                ->pushWhen($user && $user->hasRole('admin'), component('Button')
                    ->with('title', trans("content.$type.create.title"))
                    ->with('route', route('content.create', [$type]))
                )
            );
    }
}
