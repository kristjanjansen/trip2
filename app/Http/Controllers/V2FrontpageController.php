<?php

namespace App\Http\Controllers;

use Cache;
use App\Image;
use App\Content;
use App\Destination;

class V2FrontpageController extends Controller
{
    public function index()
    {
        $loggedUser = auth()->user();

        $flights = Content::getLatestItems('flight', 9, 'id');
        $forums = Content::getLatestItems('forum', 18, 'updated_at', ['unread_content']);
        $news = Content::getLatestItems('news', 6, 'id');
        $shortNews = Content::getLatestItems('shortnews', 4, 'id');
        $blogs = Content::getLatestItems('blog', 3, 'id');
        $photos = Content::getLatestItems('photo', 9, 'id');
        $travelmates = Content::getLatestItems('travelmate', 5, 'id');

        $destinations = Cache::remember('destinations_with_slug', 30, function () {
            return Destination::select('id', 'name', 'slug')->get();
        });

        return layout('frontpage')

            ->with('title', trans('site.about'))
            ->with('head_title', trans('site.about'))
            ->with('head_description', trans('site.description.main'))
            ->with('head_image', Image::getSocial())

            /*->with('promobar', component('PromoBar')
                ->with('title', "Kui vaatad Trippi telefonis ja Chrome'iga, siis võib leht katki olla. Proovi ajutiselt Firefoxi")
                ->render()
             )*/

            ->with('header', region('FrontpageHeader', $destinations))

            ->with('top', collect()
                ->push(region('FrontpageFlight', $flights->take(3)))
                ->push(component('BlockHorizontal')
                    ->is('center')
                    ->with('content', collect()->push(component('Link')
                        ->is('blue')
                        ->with('title', trans('frontpage.index.all.offers'))
                        ->with('route', route('flight.index'))
                    ))
                )
                ->pushWhen(! $loggedUser, '&nbsp;')
                ->pushWhen(! $loggedUser, region('FrontpageAbout'))
            )

            ->with('content', collect()
                ->push(component('BlockTitle')
                    ->with('title', trans('frontpage.index.forum.title'))
                    ->with('route', route('forum.index'))
                )
                ->merge($forums->take($forums->count() / 2)->map(function ($forum) {
                    return region('ForumRow', $forum);
                }))
                ->push(component('Promo')->with('promo', 'body'))
                ->merge($forums->slice($forums->count() / 2)->map(function ($forum) {
                    return region('ForumRow', $forum);
                }))
            )

            ->with('sidebar', collect()
                ->push('&nbsp')
                ->push(component('Block')->with('content', collect()
                    ->merge(collect(['forum', 'buysell', 'expat', 'misc'])
                        ->flatMap(function ($type) use ($loggedUser) {
                            return collect()
                                ->push(component('Link')
                                    ->is('large')
                                    ->with('title', trans("content.$type.index.title"))
                                    ->with('route', route("$type.index"))
                                )
                                ->pushWhen(
                                    ! $loggedUser,
                                    component('Body')
                                        ->is('gray')
                                        ->with('body', trans("site.description.$type"))
                                );
                        })
                    )
                    ->pushWhen($loggedUser, component('Link')
                        ->is('large')
                        ->with('title', trans('menu.user.follow'))
                        ->with('route', route('follow.index', [$loggedUser]))
                    )
                    ->pushWhen(
                        $loggedUser && $loggedUser->hasRole('admin'),
                        component('Link')
                            ->is('large')
                            ->with('title', trans('menu.auth.admin'))
                            ->with('route', route('internal.index'))
                    )
                ))
                ->pushWhen($loggedUser && $loggedUser->hasRole('regular'), component('Button')
                    ->with('title', trans('content.forum.create.title'))
                    ->with('route', route('forum.create', ['forum']))
                )
                ->push(component('Promo')->with('promo', 'sidebar_small'))
                ->push(component('Promo')->with('promo', 'sidebar_large'))
                ->push(component('AffHotelscombined'))
            )

            ->with('bottom1', collect()
                ->merge(region('FrontpageNews', $news))
            )

            ->with('shortNews', collect()
                ->merge(region('FrontpageShortnews', $shortNews))
                ->push(component('BlockTitle')
                    ->with('title', trans('frontpage.index.photo.title'))
                    ->with('route', route('photo.index'))
                )
            )

            ->with('bottom2', region(
                'PhotoRow',
                $photos,
                collect()
                    ->pushWhen(
                        $loggedUser && $loggedUser->hasRole('regular'),
                        component('Button')
                            ->is('transparent')
                            ->with('title', trans('content.photo.create.title'))
                            ->with('route', route('photo.create'))
                    )
            ))

            ->with('bottom3', collect()
                ->push(region('FrontpageBottom', $flights->slice(3), $travelmates))
                ->push(component('Block')
                    ->with('title', trans('frontpage.index.blog.title'))
                    ->with('route', route('blog.index'))
                    ->with('content', collect()
                        ->push(component('Grid3')
                            ->with('items', $blogs->map(function ($blog) {
                                return region('BlogCard', $blog);
                            }))
                        )
                    )
                )
                ->push(component('Promo')->with('promo', 'footer'))
            )

            ->with('footer', region('Footer'))

            ->render();
    }
}
