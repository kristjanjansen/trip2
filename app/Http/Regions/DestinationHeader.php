<?php

namespace App\Http\Regions;

class DestinationHeader
{
    public function render($destination, $user)
    {
        $parents = $destination->getAncestors();
        $childrens = $destination->getImmediateDescendants()->sortBy('name');

        $body = $destination->description ? $destination->description : $destination->vars()->description;
        if ($body && $destination->user) {
            $body .=
                ' (<a href="'
                .route('user.show', [$destination->user])
                .'">'
                .$destination->user->name
                .'</a>)';
        }

        return component('HeaderLight')
            ->with('navbar', component('Navbar')
                ->is('white')
                ->with('search', component('NavbarSearch')->is('white'))
                ->with('logo', component('Icon')
                    ->with('icon', 'tripee_logo')
                    ->with('width', 200)
                    ->with('height', 150)
                )
                ->with('navbar_desktop', region('NavbarDesktop', 'white'))
                ->with('navbar_mobile', region('NavbarMobile', 'white'))
            )
            ->with('content', collect()
                ->push(region('DestinationParents', $parents))
                ->push(component('Title')
                    ->is('white')
                    ->is('large')
                    ->with('title', $destination->name)
                )
                ->pushWhen($user && $user->hasRole('admin'),
                    component('MetaLink')
                        ->is('white')
                        ->with('title', trans('content.action.edit.title'))
                        ->with('route', route('destination.edit', [$destination]))
                )
                ->pushWhen($body, component('Body')
                    ->is('white')
                    ->is('responsive')
                    ->with('body', $body)
                )
                ->pushWhen($childrens->count(), component('Meta')
                    ->is('large')
                    ->with('items', $childrens->map(function ($children) {
                        return component('Tag')
                            ->is('white')
                            ->is('large')
                            ->with('title', $children->name)
                            ->with('route', route('destination.showSlug', [$children->slug]));
                    }))
                )
                ->push(component('BlockHorizontal')
                    ->is('between')
                    ->is('bottom')
                    ->with('content', collect()
                        ->push(region('DestinationFacts', $destination))
                        ->push(region('DestinationStat', $destination))
                    )
                )
            );
    }
}
