@extends('layouts.main')

@section('title'){{ $destination->name }}@stop

@section('header')
    @include('component.header',[
        'modifiers' => 'm-alternative'
    ])
@stop

@section('masthead.nav')
    @include('component.masthead.nav', [
        'nav_previous_title' => ($previous_destination ? $previous_destination->name : ''),
        'nav_previous_route' => ($previous_destination ? route('destination.show', [$previous_destination]) : null),
        'nav_next_title' => ($next_destination ? $next_destination->name : ''),
        'nav_next_route' => ($next_destination ? route('destination.show', [$next_destination]) : null),
        'modifiers' => 'm-yellow'
    ])
@stop

@section('content')

<div class="r-destination">
    <div class="r-destination__extra m-yellow">

        @if (\Auth::user())

            @include('component.destination.extra', [
                'items' => [
                    [
                        'icon' => (count($destination->usersHaveBeen()->where('user_id', \Auth::user()->id))
                            ?
                                'icon-pin-filled'
                            :
                                'icon-pin'
                        ),
                        'title' => $destination->usersHaveBeen()->count(),
                        'modifiers' => (count($destination->usersHaveBeen()->where('user_id', \Auth::user()->id))
                            ?
                                'm-active'
                            :
                                ''
                        ),
                        'text' => (count($destination->usersHaveBeen()->where('user_id', \Auth::user()->id))
                            ?
                                trans('destination.show.user.button.havenotbeen')
                            :
                                trans('destination.show.user.button.havebeen')
                        ),
                        'route' => route('flag.toggle', ['destination', $destination, 'havebeen'])
                    ],
                    [
                        'icon' => (count($destination->usersWantsToGo()->where('user_id', \Auth::user()->id))
                            ?
                                'icon-star-filled'
                            :
                                'icon-star'
                        ),
                        'title' => $destination->usersWantsToGo()->count(),
                        'modifiers' => (count($destination->usersWantsToGo()->where('user_id', \Auth::user()->id))
                            ?
                                'm-active'
                            :
                                ''
                        ),
                        'text' => (count($destination->usersWantsToGo()->where('user_id', \Auth::user()->id))
                            ?
                                trans('destination.show.user.button.dontwanttogo')
                            :
                                trans('destination.show.user.button.wanttogo')
                        ),
                        'route' => route('flag.toggle', ['destination', $destination, 'wantstogo'])
                    ]
                ]
            ])

        @else

            @include('component.destination.extra', [
                'items' => [
                    [
                        'icon' => 'icon-pin',
                        'title' => $destination->usersHaveBeen()->count(),
                        'text' => trans('destination.show.user.button.havebeen'),
                        'route' => ''
                    ],
                    [
                        'icon' => 'icon-star',
                        'title' => $destination->usersWantsToGo()->count(),
                        'text' => trans('destination.show.user.button.wanttogo'),
                        'route' => ''
                    ]
                ]
            ])

        @endif
    </div>

    <div class="r-destination__masthead">
        @include('component.masthead', [
            'modifiers' => 'm-alternative',
            'subtitle' => (isset($parent_destination) ? $parent_destination->name : null),
            'subtitle_route' => (isset($parent_destination) ? route('destination.show', [$parent_destination]) : null),
            'image' =>
                (isset($features['photos']) && count($features['photos']['contents'])
                    ?
                        $features['photos']['contents']->random(1)->imagePreset('large')
                    :
                        \App\Image::getRandom()
                )
        ])
    </div>

    <div class="r-destination__about m-yellow">
        @if ((isset($features['flights']) && count($features['flights']['contents'])) || (isset($destination_info) && $destination_info) && config("destinations.$destination_info->id") && count(config("destinations.$destination_info->id")))
        <div class="r-destination__about-wrap">
            @if (isset($features['flights']) && count($features['flights']['contents']))
                <div class="r-destination__about-column m-first">
                    <div class="r-destination__title-block m-white m-distribute">
                        @include('component.title', [
                            'modifiers' => 'm-yellow',
                            'title' => trans('destination.show.good.offer')
                        ])

                        @include('component.link', [
                            'modifiers' => 'm-tiny',
                            'title' => trans('destination.show.link.view.all'),
                            'route' => route('content.index', ['flights'])
                        ])
                    </div>

                    @foreach ($features['flights']['contents'] as $flight)
                        @include('component.card', [
                            'modifiers' => 'm-yellow m-small',
                            'route' => route('content.show', [$flight->type, $flight]),
                            'title' => str_limit($flight->title, 50).' '.$flight->price.' '.config('site.currency.symbol'),
                            'image' => $flight->imagePreset(),
                        ])
                    @endforeach
                </div>
            @endif

            @if (isset($destination_info) && $destination_info)
                <div class="r-destination__about-column
                    @if (isset($features['flights']) && count($features['flights']['contents']))
                        m-last
                    @endif
                ">
                    @if (config("destinations.$destination_info->id") && count(config("destinations.$destination_info->id")))
                        @include('component.destination.info',[
                            'modifiers' => 'm-yellow',
                            'text' => $destination_info->name,
                            'definitions' => [
                                [
                                    'term' => trans('destination.show.about.capital'),
                                    'definition' => config("destinations.$destination_info->id.capital")
                                ],
                                [
                                    'term' => trans('destination.show.about.area'),
                                    'definition' => config("destinations.$destination_info->id.area").' km²'
                                ],
                                [
                                    'term' => trans('destination.show.about.population'),
                                    'definition' => (round(config("destinations.$destination_info->id.population") / 1000000, 2) != 0 ? round(config("destinations.$destination_info->id.population") / 1000000, 2) : round(config("destinations.$destination_info->id.population") / 1000000, 4)).' '.trans('destination.show.about.population.milion')
                                ],
                                [
                                    'term' => trans('destination.show.about.currency'),
                                    'definition' => config("destinations.$destination_info->id.currencyCode")
                                ],
                                [
                                    'term' => trans('destination.show.about.callingCode'),
                                    'definition' => '+'.config("destinations.$destination_info->id.callingCode")
                                ],
                            ]
                        ])
                    @endif
                </div>
            @endif
        </div>
        @endif
    </div>

    <div class="r-destination__content">

    @if (isset($features['flights2']) && count($features['flights2']['contents']))
        <div class="r-destination__content-wrap m-padding">
    @else
        <div class="r-destination__content-wrap">
    @endif

            @if ((isset($popular_destinations) && count($popular_destinations)) || (isset($features['forum_posts']) && count($features['forum_posts']['contents'])))

                <div class="r-destination__content-about">
                    <div class="r-destination__content-about-column m-first">
                        @include('component.promo', ['promo' => 'sidebar_small'])
                    </div>

                    <div class="r-destination__content-about-column m-middle">
                        @if (isset($features['forum_posts']) && count($features['forum_posts']['contents']))
                            <div class="r-destination__content-title m-flex">
                                @include('component.title', [
                                    'modifiers' => 'm-yellow',
                                    'title' => trans('destination.show.forum.title')
                                ])

                                <div class="r-destination__content-title-link">
                                    @include('component.link', [
                                        'modifiers' => 'm-icon m-small',
                                        'title' => $destination->name .' '.trans('destination.show.forum.button.title'),
                                        'route' => route('content.index', 'forum').'?destination='.$destination->id,
                                        'icon' => 'icon-arrow-right'
                                    ])
                                </div>
                            </div>

                            @include('region.content.forum.list', [
                                'items' => $features['forum_posts']['contents'],
                                'modifiers' => [
                                    'main' => 'm-compact'
                                ]
                            ])
                        @else
                            <p>&nbsp;</p>
                        @endif
                    </div>

                    <div class="r-destination__content-about-column m-last">
                        @if (isset($popular_destinations) && count($popular_destinations))
                            <div class="r-destination__content-title">
                                @include('component.title', [
                                    'modifiers' => 'm-yellow',
                                    'title' => trans('destination.show.popular.title', [
                                        'destination' => $root_destination->name
                                    ])
                                ])
                            </div>

                            @include('component.list', [
                                'modifiers' => 'm-dot m-yellow',
                                'items' => $popular_destinations->transform(function($destination) {
                                    return [
                                        'title' => $destination->name,
                                        'route' => route('destination.show', [$destination])
                                    ];
                                })
                            ])
                        @else
                            <p>&nbsp;</p>
                        @endif
                    </div>
                </div>
            @endif

            @if (isset($features['photos']) && count($features['photos']['contents']))
                <div class="r-destination__content-gallery">
                    <div class="r-destination__gallery-wrap">
                        <div class="r-destination__gallery-title">
                            <div class="r-destination__gallery-title-wrap">
                                @include('component.title', [
                                    'modifiers' => 'm-yellow',
                                    'title' => trans('destination.show.gallery.title')
                                ])
                            </div>
                        </div>

                        @include('component.gallery', [
                            'modal' => [
                                'modifiers' => 'm-yellow',
                            ],
                            'items' => $features['photos']['contents']->transform(function($photo) {
                                return [
                                    'image' => $photo->imagePreset(),
                                    'image_large' => $photo->imagePreset('large'),
                                    'route' => route('content.show', [$photo->type, $photo]),
                                    'alt' => $photo->title,
                                    'tags' => $photo->destinations->transform(function($destination) {
                                        return [
                                            'title' => $destination->name,
                                            'modifiers' => ['m-orange', 'm-red', 'm-gray', 'm-blue'][rand(0,3)],
                                            'route' => route('destination.show', $destination)
                                        ];
                                    })
                                ];
                            })
                        ])

                    </div>
                </div>
            @endif

            <div class="r-destination__content-news">
                <div class="r-destination__content-news-wrap">
                    <div class="r-destination__content-news-column m-first">
                    @if (isset($features['news']) && count($features['news']['contents']))
                        <div class="r-destination__content-title">
                            @include('component.title', [
                                'modifiers' => 'm-yellow',
                                'title' => trans('destination.show.news.title')
                            ])
                        </div>
                        <div class="r-destination__content-news-block">
                            <div class="c-columns m-2-cols m-space">
                                @foreach ($features['news']['contents'] as $new)
                                    <div class="c-columns__item">
                                        @include('component.news', [
                                            'title' => $new->title,
                                            'modifiers' => 'm-smaller',
                                            'route' => route('content.show', [$new->type, $new]),
                                            'date' => $new->created_at,
                                            'image' => $new->imagePreset()
                                        ])
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                        <div class="r-block m-mobile-hide">
                            @include('component.promo', ['promo' => 'body'])
                        </div>
                    </div>

                    <div class="r-destination__content-news-column m-last">
                        <div class="r-block">
                            @include('component.promo', ['promo' => 'sidebar_large'])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (isset($features['flights2']) && count($features['flights2']['contents']))

    <div class="r-destination__flights">
        <div class="r-destination__flights-wrap">
            <div class="c-columns m-{{ count($features['flights2']['contents']) }}-cols">
                @foreach ($features['flights2']['contents'] as $key => $flight)
                    <div class="c-columns__item">
                        @include('component.destination', [
                            'modifiers' => ['m-purple', 'm-yellow', 'm-red'][$key],
                            'title' => $flight->destination ? $flight->destination->name : null,
                            'title_route' => $flight->destination ? route('destination.show', $flight->destination) : null,
                            'subtitle' => $flight->parent_destination ? $flight->parent_destination->name : null,
                            'subtitle_route' => $flight->parent_destination ? route('destination.show', $flight->parent_destination) : null
                        ])

                        @include('component.card', [
                            'modifiers' => ['m-purple', 'm-yellow', 'm-red'][$key],
                            'route' => route('content.show', [$flight->type, $flight]),
                            'title' => $flight->title.' '.$flight->price.' '.config('site.currency.symbol'),
                            'image' => $flight->imagePreset(count($features['flights2']['contents']) == 1 ? 'large' : '')
                        ])
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    @endif

    @if (isset($features['travel_mates']) && count($features['travel_mates']['contents']))
        <div class="r-destination__travelmates">
            <div class="r-destination__travelmates-wrap">
                <div class="r-destination__content-title">
                    @include('component.title', [
                        'title' => trans('frontpage.index.travelmate.title'),
                        'modifiers' => 'm-yellow'
                    ])
                </div>

                @include('region.content.travelmate.list', [
                    'items' => $features['travel_mates']['contents']
                ])
            </div>
        </div>
    @endif

    <div class="r-destination__footer-promo">
        <div class="r-destination__footer-promo-wrap">
            @include('component.promo', ['promo' => 'footer'])
        </div>
    </div>
</div>

@stop

@section('footer')
    @include('component.footer', [
        'modifiers' => 'm-alternative',
        'image' => \App\Image::getRandom()
    ])
@stop
