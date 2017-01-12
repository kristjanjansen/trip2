<?php

namespace App\Http\Regions;

class ForumHeader
{
    public function render($title = '', $meta = '', $meta2 = '')
    {
        return component('HeaderLight')
            ->with('background', component('BackgroundMap'))
            ->with('title', $title)
            ->with('meta', $meta)
            ->with('meta2', $meta2)
            ->with('navbar', component('Navbar')
                ->with('search', component('NavbarSearch'))
                ->with('logo', component('Icon')
                    ->with('icon', 'tripee_logo_dark')
                    ->with('width', 200)
                    ->with('height', 150)
                )
                ->with('navbar_desktop', region('NavbarDesktop'))
                ->with('navbar_mobile', region('NavbarMobile'))
            );
    }
}
