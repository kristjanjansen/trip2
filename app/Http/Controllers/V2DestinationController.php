<?php

namespace App\Http\Controllers;

use App\Poll;
use App\User;
use App\Image;
use App\Content;
use App\Destination;
use Illuminate\Http\Request;

class V2DestinationController extends Controller
{
    public function show($id)
    {
        $destination = Destination::findOrFail($id);

        return redirect(route('destination.showSlug', $destination->slug), 301);
    }

    public function showSlug($slug)
    {
        $destination = Destination::findBySlugOrFail($slug);

        $photos = Content::getLatestPagedItems('photo', 9, $destination->id);
        $forums = Content::getLatestPagedItems('forum', 8, $destination->id);

        $flights = Content::getLatestPagedItems('flight', 6, $destination->id);
        $travelmates = Content::getLatestPagedItems('travelmate', 6, $destination->id);
        $news = Content::getLatestPagedItems('news', 2, $destination->id);

        $polls = Poll::getUnansweredPollsByDestinationId($destination->id);
        $poll_field = null;
        $poll_results = [];
        $poll = null;

        if ($polls->isNotEmpty() && request()->user()) {
            $poll = $polls->first();
            $poll_field = $poll->poll_fields->first();
        } else {
            $polls = Poll::getPollsByDestinationId($destination->id);

            if ($polls->isNotEmpty()) {
                $poll = $polls->first();
                $poll_field = $poll->poll_fields->first();
                $poll_results = $poll_field->getParsedResults();
            }
        }

        $loggedUser = request()->user();

        return layout('2col')

            ->with('head_description', trans('site.description.destination', [
                'name' => $destination->vars()->name,
            ]))

            ->with('title', $destination->vars()->name)

            ->with('background', component('BackgroundMap'))
            ->with('color', 'yellow')

            ->with('header', region('DestinationHeader', $destination, $loggedUser))

            ->with('top', region(
                'PhotoRow',
                $photos->count() ? $photos : collect(),
                collect()
                    ->pushWhen(
                        $photos->count(),
                        component('Button')
                            ->is('transparent')
                            ->with('title', trans('content.photo.more'))
                            ->with('route', route(
                                'photo.index',
                                ['destination' => $destination->id]
                            ))
                    )
                    ->pushWhen(
                        $loggedUser && $loggedUser->hasRole('regular'),
                        component('Button')
                            ->is('transparent')
                            ->with('title', trans('content.photo.create.title'))
                            ->with('route', route('photo.create'))
                    )

            ))

            ->with('content', collect()
                ->push(component('Block')
                    ->with('title', trans('destination.show.forum.title'))
                    ->with('route', route('forum.index', ['destination' => $destination]))
                    ->with('content', $forums->map(function ($forum) {
                        return region('ForumRow', $forum);
                    })
                    )
                )
                ->push(component('Promo')->with('promo', 'body'))
            )

            ->with('sidebar', collect()
                ->when($poll_field && $poll, function ($collection) use ($poll_field, $poll_results, $poll) {
                    $options = json_decode($poll_field->options, true);

                    if (isset($options['image_id'])) {
                        $image = Image::findOrFail($options['image_id']);
                        $image_small = $image->preset('xsmall_square');
                        $image_large = $image->preset('large');
                    }

                    return $collection->push(component('Block')
                        ->is('gray')
                        ->with('content', collect()
                            ->push(component('Title')
                                ->with('title', trans('content.poll.edit.poll'))
                                ->is('small')
                            )
                            ->push(component('PollAnswer')
                                ->with('options', json_decode($poll_field->options, true))
                                ->with('type', $poll_field->type)
                                ->with('id', $poll->id)
                                ->with('results', $poll_results)
                                ->with('image_small', isset($image_small) ? $image_small : '')
                                ->with('image_large', isset($image_large) ? $image_large : '')
                                ->with('answer_trans', trans('content.poll.answer'))
                                ->with('select_error', $poll_field->type == 'radio' ?
                                    trans('content.poll.answer.error.select.one') :
                                    trans('content.poll.answer.error.select.multiple')
                                )
                                ->with('save_error', trans('content.poll.answer.error.save'))
                            )
                        )
                    );
                })
                ->push(component('Promo')->with('promo', 'sidebar_small'))
                ->push(component('Promo')->with('promo', 'sidebar_large'))
            )

            ->with('bottom', collect()
                ->push(region('DestinationBottom', $flights, $travelmates, $news, $destination))
                ->push(component('Promo')->with('promo', 'footer'))
            )

            ->with('footer', region('Footer'))

            ->render();
    }

    public function edit($id)
    {
        $destination = Destination::findOrFail($id);

        return layout('1col')

            ->with('header', region('Header', collect()
                ->push(component('EditorScript'))
                ->push(component('Title')
                    ->is('white')
                    ->is('large')
                    ->with('title', $destination->name)
                    ->with('route', route('destination.show', $destination->id))
                )
            ))

            ->with('content', collect()
                ->push(component('Title')
                    ->with('title', trans('content.destionation.edit.title'))
                )
                ->push(component('Form')
                    ->with('route', route('destination.update', [$destination]))
                    ->with('fields', collect()
                        ->push(component('FormEditor')
                            ->with('title', trans('content.destination.edit.description'))
                            ->with('name', 'description')
                            ->with('value', [old('description', $destination->description)])
                            ->with('rows', 10)
                        )
                        ->push(component('FormTextfield')
                            ->with('title', trans('content.destination.edit.user'))
                            ->with('name', 'user')
                            ->with('value', ($destination->user ? $destination->user->name : ''))
                        )
                        ->push(component('FormButton')
                            ->with('title', trans('content.edit.submit.title'))
                        )
                    )
                )
            )

            ->with('footer', region('Footer'))

            ->render();
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'user' => 'exists:users,name',
        ];

        $this->validate($request, $rules);

        $destination = Destination::findOrFail($id);

        $destination->description = $request->description;

        if ($request->user && $request->user != '') {
            $user = User::where('name', $request->user)->first();
            $destination->user_id = $user->id;
        }

        $destination->save();

        return redirect(route('destination.show', $destination));
    }
}
