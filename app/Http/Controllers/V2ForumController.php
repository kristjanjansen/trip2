<?php

namespace App\Http\Controllers;

use App;
use Request;
use App\User;
use App\Image;
use App\Topic;
use App\Content;
use Carbon\Carbon;
use App\Destination;
use App\UnreadContent;

class V2ForumController extends Controller
{
    public function forumIndex()
    {
        return $this->index('forum');
    }

    public function buysellIndex()
    {
        return $this->index('buysell');
    }

    public function expatIndex()
    {
        return $this->index('expat');
    }

    public function miscIndex()
    {
        return $this->index('misc');
    }

    private function index($forumType)
    {
        $currentDestination = Request::get('destination');
        $currentTopic = Request::get('topic');

        $forums = Content::getLatestPagedItems($forumType, false, $currentDestination, $currentTopic, 'updated_at');
        $destinations = Destination::select('id', 'name')->get();
        $topics = Topic::select('id', 'name')->orderBy('name', 'asc')->get();

        //dd($forums->first()->unread);

        $flights = Content::getLatestItems('flight', 3);
        $travelmates = Content::getLatestItems('travelmate', 3);
        $news = Content::getLatestItems('news', 1);

        return layout('2col')

            ->with('title', trans("content.$forumType.index.title"))
            ->with('head_title', trans("content.$forumType.index.title"))
            ->with('head_description', trans("site.description.$forumType"))
            ->with('head_image', Image::getSocial())

            ->with('background', component('BackgroundMap'))
            ->with('color', 'gray')

            ->with('header', region('ForumHeader', collect()
                ->push(component('Title')
                    ->is('gray')
                    ->with('title', trans("content.$forumType.index.title"))
                )
                ->push(component('BlockHorizontal')
                    ->with('content', region('ForumLinks'))
                )
                ->pushWhen($forumType != 'misc', region(
                    'FilterHorizontal',
                    $destinations,
                    $topics,
                    $currentDestination,
                    $currentTopic,
                    $forums->currentPage(),
                    'forum.index'
                ))
            ))

            ->with('content', collect()
                ->merge($forums->map(function ($forum) {
                    return region('ForumRow', $forum);
                }))
                ->push(region('Paginator', $forums, $currentDestination, $currentTopic))
            )

            ->with('sidebar', collect()
                ->push(region('ForumAbout', $forumType))
                ->push(component('Promo')->with('promo', 'sidebar_small'))
                ->push(component('Promo')->with('promo', 'sidebar_large'))
            )

            ->with('bottom', collect()
                ->push(region('ForumBottom', $flights, $travelmates, $news))
                ->push(component('Promo')->with('promo', 'footer'))
            )

            ->with('footer', region('FooterLight'))

            ->render();
    }

    public function followIndex($user_id)
    {
        $user = User::findOrFail($user_id);
        $follows = $user->follows;

        $flights = Content::getLatestItems('flight', 3);
        $travelmates = Content::getLatestItems('travelmate', 3);
        $news = Content::getLatestItems('news', 1);

        return layout('2col')

            ->with('background', component('BackgroundMap'))
            ->with('color', 'gray')

            ->with('header', region('ForumHeader', collect()
                ->push(component('Title')
                    ->is('gray')
                    ->with('title', trans('follow.index.title'))
                )
                ->push(component('BlockHorizontal')
                    ->with('content', region('ForumLinks'))
                )
            ))

            ->with('content', collect()
                ->pushWhen($follows->count() == 0, component('Title')
                    ->with('title', trans('follow.index.empty'))
                )
                ->merge($user->follows->map(function ($follow) {
                    return region('ForumRow', $follow->followable);
                }))
            )

            ->with('sidebar', collect()
                ->push(component('Promo')->with('promo', 'sidebar_small'))
                ->push(component('Promo')->with('promo', 'sidebar_large'))
            )

            ->with('bottom', collect()
                ->push(region('ForumBottom', $flights, $travelmates, $news))
                ->push(component('Promo')->with('promo', 'footer'))
            )

            ->with('footer', region('FooterLight'))

            ->render();
    }

    public function show($slug)
    {
        $user = auth()->user();
        $forum = Content::getItemBySlug($slug, $user);

        // TODO: Why?

        if (! $forum->first()) {
            abort(404);
        }

        $forumType = $forum->type;

        $firstUnreadCommentId = $forum->vars()->firstUnreadCommentId;

        $flights = Content::getLatestItems('flight', 3);
        $travelmates = Content::getLatestItems('travelmate', 3);
        $news = Content::getLatestItems('news', 1);

        // Update unread datetime
        if ($user) {
            $unreadContent = UnreadContent::where('content_id', $forum->id)->where('user_id', $user->id)->first();

            if (! $unreadContent) {
                $unreadContent = new UnreadContent;
                $unreadContent->content_id = $forum->id;
                $unreadContent->user_id = $user->id;
            }

            $unreadContent->read_at = Carbon::now()->toDateTimeString();
            $unreadContent->save();
        }

        $anchor = $forum->comments->count()
            ? '#comment-'.$forum->comments->last()->id
            : '';

        return layout('2col')

            ->with('title', trans('content.forum.index.title'))
            ->with('head_title', $forum->getHeadTitle())
            ->with('head_description', $forum->getHeadDescription())
            ->with('head_image', Image::getSocial())

            ->with('background', component('BackgroundMap'))
            ->with('color', 'gray')

            ->with('header', region('ForumHeader', collect()
                ->push(component('Title')
                    ->is('gray')
                    ->with('title', trans("content.$forum->type.index.title"))
                    ->with('route', route("$forum->type.index"))
                )
                ->push(component('BlockHorizontal')
                    ->with('content', region('ForumLinks'))
                )
            ))

            ->with('top', collect()->pushWhen(
                ! $forum->status,
                component('HeaderUnpublished')
                    ->with('title', trans('content.show.unpublished'))
            ))

            ->with('content', collect()
                ->push(region('ForumPost', $forum, ($forum->type == 'misc' ? 'forum.edit.misc' : 'forum.edit')))
                ->pushWhen(
                    $forum->comments->count() > 1,
                    component('BlockHorizontal')
                        ->is('right')
                        ->with('content', collect()
                            ->push(component('Link')
                                ->with('title', trans('comment.action.latest.comment'))
                                ->with('route', route(
                                    'forum.show', [$forum->slug]).$anchor
                                )
                            )
                    )
                )
                ->merge($forum->comments->map(function ($comment) use ($firstUnreadCommentId) {
                    return region('Comment', $comment, $firstUnreadCommentId, 'inset');
                }))
                ->pushWhen($user && $user->hasRole('regular'), region('CommentCreateForm', $forum, 'inset'))
                ->push(component('Promo')->with('promo', 'body'))
            )

            ->with('sidebar', collect()
                ->push(region('ForumAbout', $forumType))
                ->push(component('Promo')->with('promo', 'sidebar_small'))
                ->push(component('Promo')->with('promo', 'sidebar_large'))
            )

            ->with('bottom', collect()
                ->push(region('ForumBottom', $flights, $travelmates, $news))
                ->push(component('Promo')->with('promo', 'footer'))
            )

            ->with('footer', region('FooterLight'))

            ->render();
    }

    public function create()
    {
        return App::make('App\Http\Controllers\ContentController')
            ->create('forum');
    }

    public function createExperiment()
    {
        $destinations = Destination::select('id', 'name')->orderBy('name', 'asc')->get();
        $topics = Destination::select('id', 'name')->orderBy('name', 'asc')->get();

        return layout('2col')

            ->with('narrow', true)

            ->with('background', component('BackgroundMap'))
            ->with('color', 'gray')

            ->with('header', region('ForumHeader', collect()
                ->push(component('Title')
                    ->is('gray')
                    ->with('title', trans('content.forum.index.title'))
                    ->with('route', route('forum.index'))
                )
                ->push(component('BlockHorizontal')
                    ->with('content', region('ForumLinks'))
                )
            ))

            ->with('content', collect()
                ->push(component('Title')
                    ->with('title', trans('content.forum.create.title'))
                )
                ->push(component('Form')
                    ->with('id', 'ForumCreateForm')
                    //->with('route', route('forum.store'))
                    ->with('fields', collect()
                        ->push(component('FormRadio')
                            ->with('name', 'type')
                            ->with('value', 'forum')
                            ->with('options', collect(['forum', 'buysell', 'expat'])
                                ->map(function ($type) {
                                    return collect()
                                        ->put('id', $type)
                                        ->put('name', trans("content.$type.index.title"));
                                })
                            )
                        )
                        ->push(component('FormTextfield')
                            ->is('large')
                            ->with('title', trans('content.forum.edit.field.title.title'))
                            ->with('name', 'title')
                            ->with('value', old('title'))
                        )
                        ->push(component('FormTextarea')
                            ->with('title', trans('content.forum.edit.field.body.title'))
                            ->with('name', 'body')
                            ->with('value', old('title'))
                            ->with('rows', 20)
                        )
                        ->push(component('FormSelectMultiple')
                            ->with('name', 'destinations')
                            ->with('options', $destinations)
                            ->with('placeholder', trans('content.index.filter.field.destination.title'))
                        )
                        ->push(component('FormSelectMultiple')
                            ->with('name', 'topics')
                            ->with('options', $topics)
                            ->with('placeholder', trans('content.index.filter.field.topic.title'))
                        )
                        ->push(component('FormButton')
                            ->with('disabled', true)
                            ->with('title', trans('content.create.submit.title'))
                        )
                        /*
                        ->push(component('FormButtonProcess')
                            ->with('id', 'ForumCreateForm')
                            ->with('title', trans('content.create.submit.title'))
                            ->with('processingtitle', trans('content.create.submitting.title'))
                        )
                        */

                    )
                )
            )

            ->with('sidebar', collect()
                ->push(component('Block')
                    ->is('gray')
                    ->with('content', collect()
                        ->push(component('Title')
                            ->is('smaller')
                            ->is('red')
                            ->with('title', trans('content.edit.notes.heading'))
                            ->with('route', route('forum.index'))
                        )
                        ->push(component('Body')
                            ->with('body', trans('content.edit.notes.body'))
                        )
                ))
            )

            ->with('footer', region('Footer'))

            ->render();
    }

    public function store()
    {
        return App::make('App\Http\Controllers\ContentController')
            ->store(request(), 'forum');
    }

    public function edit($id)
    {
        return App::make('App\Http\Controllers\ContentController')
            ->edit('forum', $id);
    }

    public function update($id)
    {
        return App::make('App\Http\Controllers\ContentController')
            ->store(request(), 'forum', $id);
    }
}
