<?php

namespace App\Http\Regions;

class FlightRow
{
    public function render($flight)
    {
        $user = auth()->user();

        return component('FlightRow')
            ->with('route', route('flight.show', [$flight->slug]))
            ->with('icon', component('Icon')
                ->is('blue')
                ->with('icon', 'icon-tickets')
                ->with('size', 'xl')
            )
            ->with('title', $flight->vars()->title)
            ->with('meta', component('Meta')->with('items', collect()
                    ->push(component('MetaLink')
                        ->with('title', $flight->vars()->created_at)
                    )
                    ->pushWhen($user && $user->hasRole('admin'), component('MetaLink')
                        ->with('title', trans('comment.action.edit.title'))
                        ->with('route', route('flight.edit', [$flight]))
                    )
                    ->merge($flight->destinations->map(function ($destination) {
                        return component('Tag')
                            ->is('orange')
                            ->with('title', $destination->name)
                            ->with('route', route('destination.showSlug', [$destination->slug]));
                    }))
                    ->merge($flight->topics->map(function ($tag) {
                        return component('Tag')->with('title', $tag->name);
                    }))
                )
            );
    }
}
