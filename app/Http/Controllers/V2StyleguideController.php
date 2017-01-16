<?php

namespace App\Http\Controllers;

use Request;
use Response;
use App\Image;
use App\Content;

class V2StyleguideController extends Controller
{
    public function index()
    {
        session()->keep('info');

        $user = auth()->user();

        return layout('1col')

            ->with('content', collect()

                ->push(component('Form')
                    ->with('route', route('styleguide.form'))
                    ->with('fields', collect()
                        ->push(component('FormTextarea')
                            ->with('name', 'body')
                        )
                        ->push(component('FormButton')
                            ->with('title', trans('comment.create.submit.title'))
                        )
                    )
                )

                ->push(component('Title')
                    ->with('title', 'Uue Trip.ee eelvaade')
                )

                ->push(component('MetaLink')
                    ->with('title', 'Error 401')
                    ->with('route', route('v2.error.show', [401]))
                )
                ->push(component('MetaLink')
                    ->with('title', 'Error 404')
                    ->with('route', route('v2.error.show', [404]))
                )
                ->push(component('MetaLink')
                    ->with('title', 'Error 500')
                    ->with('route', route('v2.error.show', [500]))
                )
                ->push(component('MetaLink')
                    ->with('title', 'Error 503')
                    ->with('route', route('v2.error.show', [503]))
                )
                ->push(component('MetaLink')
                    ->with('title', 'Esikülg')
                    ->with('route', route('v2.frontpage.index'))
                )
                ->push(component('MetaLink')
                    ->with('title', 'Uudised')
                    ->with('route', route('v2.news.index'))
                )
                ->push(component('MetaLink')
                    ->with('title', 'Lennupakkumised')
                    ->with('route', route('v2.flight.index'))
                )
                ->push(component('MetaLink')
                    ->with('title', 'Foorum')
                    ->with('route', route('v2.forum.index'))
                )
                ->push(component('MetaLink')
                    ->with('title', 'Reisikaaslased')
                    ->with('route', route('v2.travelmate.index'))
                )
                ->push(component('MetaLink')
                    ->with('title', 'Staatilised lehed')
                    ->with('route', route('v2.static.index'))
                )
                ->pushWhen($user && $user->hasRole('admin'), component('MetaLink')
                    ->with('title', 'Toimetuse foorum')
                    ->with('route', route('v2.internal.index'))
                )
                ->push('<a id="aaa">AAA</a>')

                ->push(region('Footer'))

            )

            ->render();
    }

    public function form()
    {
        return backToAnchor('#aaa')->with('title', 'Yo');
    }

    public function store()
    {
        $image = Request::file('image');

        $imagename = 'image-'.rand(1, 3).'.'.$image->getClientOriginalExtension();

        return Response::json([
            'image' => $imagename,
        ]);
    }
}
