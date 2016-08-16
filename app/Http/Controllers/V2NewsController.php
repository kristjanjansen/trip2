<?php

namespace App\Http\Controllers;

use App\Content;

class V2NewsController extends Controller
{
    public function index()
    {
        $posts = Content::whereType('news')->latest()->whereStatus(1)->take(20)->get();

        return view('v2.layouts.1col')
            ->with('content', collect()
                ->merge($posts->map(function ($post) {
                    return region('NewsRow', $post);
                }))
            );
    }

    public function show($id)
    {
        $post = Content::
            with(
                'images',
                'user',
                'user.images',
                'comments',
                'comments.user',
                'destinations',
                'topics'
            )
            ->whereStatus(1)
            ->find($id);


        return view('v2.layouts.1col')
            ->with('header', region('NewsMasthead', $post))
            ->with('content', collect()
                ->push(component('Body')->is('responsive')->with('body', $post->vars()->body))
                ->merge($post->comments->map(function ($comment) {
                    return region('Comment', $comment);
                }))
               // ->push(region('CommentCreateForm', $post))
            )
            ->with('footer', region('Footer'));
    }

    public function edit($id)
    {
        $post = Content::whereType('news')
           ->whereStatus(1)
           ->findOrFail($id);

        return view('v2.layouts.fullpage')
            ->with('content', collect()
                ->push(component('Editor')->with('post', $post))
            );
    }
}
