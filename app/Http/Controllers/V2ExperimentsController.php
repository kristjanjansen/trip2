<?php

namespace App\Http\Controllers;

use App\Content;

class V2ExperimentsController extends Controller
{
    public function test()
    {
        return layout('1colnarrow')
            ->with('color', 'gray')
            ->with('background', component('BackgroundMap'))
            ->with('header', region('StaticHeader'))

            ->with('top', collect()
                ->push(component('Title')
                    ->is('center')
                    ->is('large')
                    ->with('title', trans('Logi sisse'))
            ))

            ->with('content', collect(range(0, 20))->map(function ($i) {
                return '<br>';
            }))

            ->with('bottom', collect(range(0, 3))->map(function ($i) {
                return '<br>';
            }))

            ->with('footer', region('FooterLight'))

            ->render();
    }

    public function index()
    {
        $user = auth()->user();

        return layout('1col')

            ->with('content', collect()

                ->push(component('Form')->with('fields', collect()
                    ->push(component('FormRadio')
                        ->with('name', 'type')
                        ->with('value', 'travelmate')
                        ->with('options', collect()
                            ->push(['id' => 'forum', 'name' => 'Foorum'])
                            ->push(['id' => 'travelmate', 'name' => 'Travelmate'])
                        )
                    )
                ))

                ->push(component('Title')
                    ->with('title', 'Blog')
                )
                ->push(component('MetaLink')
                    ->with('title', 'Blog: index')
                    ->with('route', route('experiments.blog.index'))
                )
                ->push(component('MetaLink')
                    ->with('title', 'Blog: show')
                    ->with('route', route('experiments.blog.show'))
                )
                ->push(component('MetaLink')
                    ->with('title', 'Blog: edit')
                    ->with('route', route('experiments.blog.edit'))
                )
                ->push(component('MetaLink')
                    ->with('title', 'Blog: profile')
                    ->with('route', route('experiments.blog.profile'))
                )

                ->push(component('Title')
                    ->with('title', 'Vealehed')
                )
                ->push(component('MetaLink')
                    ->with('title', 'Error 401')
                    ->with('route', route('error.show', [401]))
                )
                ->push(component('MetaLink')
                    ->with('title', 'Error 404')
                    ->with('route', route('error.show', [404]))
                )
                ->push(component('MetaLink')
                    ->with('title', 'Error 500')
                    ->with('route', route('error.show', [500]))
                )
                ->push(component('MetaLink')
                    ->with('title', 'Error 503')
                    ->with('route', route('error.show', [503]))
                )

            )

            ->render();
    }
}
