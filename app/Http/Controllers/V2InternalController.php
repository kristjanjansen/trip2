<?php

namespace App\Http\Controllers;

use Cache;
use App\Content;

class V2InternalController extends Controller
{
    public function index()
    {
        $forums = Content::getLatestPagedItems('internal', false, false, false, 'updated_at');

        return layout('2col')

            ->with('header', region('ForumHeader', collect()
                ->push(component('Title')
                    ->with('title', trans('content.internal.index.title'))
                )
                ->push(component('BlockHorizontal')
                    ->with('content', region('ForumLinks'))
                )
            ))

            ->with('content', collect()
                ->merge($forums->map(function ($forum) {
                    return region('ForumRow', $forum, route('v2.internal.show', [$forum]));
                }))
                ->push(region('Paginator', $forums))
            )

            ->with('sidebar', collect()
                ->push(component('Button')
                    ->with('title', trans('content.internal.create.title'))
                    ->with('route', route('content.create', ['internal']))
                )
            )

            ->with('footer', region('FooterLight'))

            ->render();
    }

    public function show($slug)
    {
        $forum = Content::findOrFail($slug);
        $user = auth()->user();
        $firstUnreadCommentId = $forum->vars()->firstUnreadCommentId;

        // Clear the unread cache

        if ($user) {
            $key = 'new_'.$forum->id.'_'.$user->id;
            Cache::store('permanent')->forget($key);
        }

        return layout('2col')

            ->with('header', region('ForumHeader', collect()
                ->push(component('Title')
                    ->with('title', trans('content.internal.index.title'))
                    ->with('route', route('v2.internal.index'))
                )
            ))

            ->with('content', collect()
                ->push(region('ForumPost', $forum))
                ->merge($forum->comments->map(function ($comment) use ($firstUnreadCommentId) {
                    return region('Comment', $comment, $firstUnreadCommentId, 'inset');
                }))
                ->pushWhen($user && $user->hasRole('regular'), region('CommentCreateForm', $forum, 'inset'))
            )

            ->with('footer', region('FooterLight'))

            ->render();
    }
}
