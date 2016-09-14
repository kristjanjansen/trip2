<?php

namespace App\Http\Regions;

class NewsHeader
{
    public function render($new)
    {
        $user = auth()->user();

        return component('NewsHeader')
            ->with('title', $new->title)
            ->with('background', $new->getHeadImage())
            ->with('navbar', component('Navbar')
                ->with('search', component('NavbarSearch')->is('white'))
                ->with('logo', component('Icon')
                    ->with('icon', 'tripee_logo_plain_dark')
                    ->with('width', 80)
                    ->with('height', 30)
                )
                ->with('navbar_desktop', region('NavbarDesktop', 'white'))
                ->with('navbar_mobile', region('NavbarMobile', 'white'))
            )
            ->with('meta', component('Meta')
                ->with('items', collect()
                    ->push(component('UserImage')
                        ->with('route', route('user.show', [$new->user]))
                        ->with('image', $new->user->imagePreset('small_square'))
                        ->with('rank', $new->user->vars()->rank)
                    )
                    ->push(component('MetaLink')
                        ->with('title', $new->user->vars()->name)
                        ->with('route', route('user.show', [$new->user]))
                    )
                    ->push(component('MetaLink')
                        ->with('title', $new->vars()->created_at)
                    )
                    ->merge($new->destinations->map(function ($tag) {
                        return component('Tag')->is('orange')->with('title', $tag->name);
                    }))
                    ->merge($new->topics->map(function ($tag) {
                        return component('Tag')->with('title', $tag->name);
                    }))
                    ->pushWhen($user && $user->hasRole('admin'), component('MetaLink')
                        ->with('title', trans('content.action.edit.title'))
                        ->with('route', route('content.edit', [$new->type, $new]))
                    )
                    ->pushWhen($user && $user->hasRole('admin'), component('MetaLink')
                        ->with('title', trans('content.action.edit.title').' v2')
                        ->with('route', route('news.edit.v2', [$new]))
                    )
                    ->pushWhen($user && $user->hasRole('admin'), component('Form')
                            ->with('route', route('content.status', [
                                $new->type,
                                $new,
                                (1 - $new->status),
                            ]))
                            ->with('method', 'PUT')
                            ->with('fields', collect()
                                ->push(component('FormLink')
                                    ->with('title', trans("content.action.status.$new->status.title"))
                                )
                            )
                    )
                )
            );
    }
}
