<?php

namespace App\Http\Controllers;

use Cache;
use Request;
use App\Topic;
use App\Content;
use App\Destination;

class V2ForumController extends Controller
{
    public function index()
    {
        $currentDestination = Request::get('destination');
        $currentTopic = Request::get('topic');

        $forums = Content::getLatestPagedItems('forum', false, $currentDestination, $currentTopic);
        $flights = Content::getLatestItems('flight', 4);
        $destinations = Destination::select('id', 'name')->get();
        $topics = Topic::select('id', 'name')->get();

        return view('v2.layouts.2col')

            ->with('header', region('HeaderLight', trans('content.forum.index.title')))

            ->with('content', collect()
                ->merge($forums->map(function ($forum) {
                    return region('ForumRow', $forum);
                }))
                ->push(region('Paginator', $forums, $currentDestination, $currentTopic))
            )

            ->with('sidebar', collect()
                ->push(component('Block')->with('content', collect()
                    ->push(region(
                        'Filter',
                        $destinations,
                        $topics,
                        $currentDestination,
                        $currentTopic,
                        $forums->currentPage(),
                        'v2.forum.index'
                    ))
                ))
                ->merge(region('ForumLinks'))
                ->push(region('ForumAbout'))
                ->push(component('Promo')->with('promo', 'sidebar_small'))
                ->push(component('Promo')->with('promo', 'sidebar_large'))
            )

            ->with('bottom', collect()
                ->push(region('FlightBottom', $flights))
                ->push(component('Promo')->with('promo', 'footer'))
            )

            ->with('footer', region('FooterLight'));
    }

    public function show($slug)
    {
        $forum = Content::getItemBySlug($slug);
        $forums = Content::getLatestItems('forum', 5);
        $travelmates = Content::getLatestItems('travelmate', 3);
        $user = auth()->user();

        if (auth()->check()) {
            $userId = auth()->user()->id;

        // We check if user has read the post or its comments

        $key = 'new_'.$forum->id.'_'.$userId;

            $newId = Cache::get($key);

        // We iterate over post comments

        $forum->comments->map(function ($comment) use ($newId) {

            // If the comment is the first unread (or newer) comment

            if ($newId > 0 && $comment->id >= $newId) {

                // Mark the comment as new so the view can style the comment accordingly

                $comment->isNew = true;
            }

            return $comment;
        });

        // Mark the post and its comments read

            Cache::forget($key);
        }

        return view('v2.layouts.2col')

            ->with('header', region('HeaderLight', trans('content.forum.index.title')))

            ->with('content', collect()
                ->push(region('ForumPost', $forum))
                ->merge($forum->comments->map(function ($comment) {
                    return region('Comment', $comment);
                }))
                ->pushWhen($user && $user->hasRole('regular'), region('CommentCreateForm', $forum))
            )

            ->with('sidebar', collect()
                ->merge(region('ForumLinks'))
                ->push(region('ForumAbout'))
                ->push(component('Promo')->with('promo', 'sidebar_small'))
                ->merge($forum->destinations->map(function ($destination) {
                    return region('DestinationBar', $destination, $destination->getAncestors());
                }))
                ->push(region('ForumSidebar', $forums))
                ->push(component('Promo')->with('promo', 'sidebar_small'))
                ->push(component('Promo')->with('promo', 'sidebar_large'))
            )

            ->with('bottom', collect()
                ->push(region('ForumBottom', $forums))
                ->push(region('TravelmateBottom', $travelmates))
                ->push(component('Promo')->with('promo', 'footer'))
            )

            ->with('footer', region('FooterLight'));
    }
}
