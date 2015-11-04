<nav class="c-nav {{ $modifiers or '' }}">

    <ul class="c-nav__list">

        @include('component.nav', [
            'menu' => 'header',
            'items' => config('menu.header')
        ])

        @if(auth()->user() && ! auth()->user()->hasRole('admin'))

            @include('component.nav', [
                'menu' => 'auth',
                'items' => [
                    'user' => [
                        'route' => route('user.show', [auth()->user()]),
                        'title' =>  auth()->user()->name,
                        'profile' => [
                            'image' => \App\Image::getRandom(),
                        ],
                        'children' => [
                            [
                                'title' => 'Profile',
                                'route' => route('user.show', [auth()->user()]),
                            ],
                            [
                                'title' => 'Messages',
                                'route' => '#',
                                'badge' => [
                                    'modifiers' => 'm-blue',
                                    'count' => '2'
                                ]
                            ],
                            [
                                'title' => 'Change profile',
                                'route' => '#',
                            ],
                            [
                                'title' => 'Logout',
                                'route' => route('login.logout'),
                            ],
                        ]
                    ],
                ],
            ])

        @elseif(auth()->user() && auth()->user()->hasRole('admin'))

            @include('component.nav', [
                'menu' => 'auth',
                'items' => [
                    'user' => [
                        'route' => route('user.show', [auth()->user()]),
                        'title' =>  auth()->user()->name,
                        'children' => [
                            [
                                'title' => 'Admin',
                                'route' => route('content.index', ['internal'])
                            ],
                            [
                                'title' => 'Logout',
                                'route' => route('login.logout'),
                            ],
                        ]
                    ],
                ],
            ])

        @else

            @include('component.nav', [
                'menu' => 'auth',
                'items' => [
                    'register' => [
                        'route' => route('register.form'),
                    ],
                    'login' => [
                        'route' => route('login.form')
                    ],
                ],
            ])

        @endif

    </ul>

</nav>

