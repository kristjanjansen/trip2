<?php

namespace App\Http\Controllers;

use App\Content;

class V2AdminController extends Controller
{
    public function unpublishedIndex()
    {
        $contents = Content::whereStatus(0)->latest()->simplePaginate(50);

        return layout('1col')

            ->with('header', region('ForumHeader', collect()
                ->push(component('Title')
                    ->with('title', trans('admin.content.index.title'))
                )
                ->push(component('BlockHorizontal')
                    ->with('content', region('AdminLinks'))
                )
            ))

            ->with('content', collect()
                ->merge($contents->map(function ($content) {
                    return component('Block')->with('content', collect()
                        ->push(component('Title')
                            ->is('small')
                            ->with('title', $content->vars()->title)
                            ->with('route', route("$content->type.show", [$content->slug]))
                        )
                        ->push(component('MetaLink')
                            ->with('title', collect()
                                ->push(trans("content.$content->type.index.title"))
                                ->push($content->user->vars()->name)
                                ->push($content->vars()->created_at)
                                ->implode(' ')
                            )
                        )
                    );
                }))
                ->push(region('Paginator', $contents))
            )

            ->with('footer', region('FooterLight'))

            ->render();
    }
}
