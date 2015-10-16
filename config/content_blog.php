<?php

return [

    'frontpage' => [

        'show' => true,
        'with' => [],
        'latest' => 'created_at',
        'take' => 1,

    ],

    'index' => [

        'with' => ['user', 'comments', 'destinations', 'topics'],
        'latest' => 'created_at',
        'paginate' => 25,
    ],

    'edit' => [

        'fields' => [
            'title' => [
                'type' => 'text',
                'large' => true,
            ],
            'body' => [
                'type' => 'textarea',
            ],
            'destinations' => [
                'type' => 'destinations',
            ],
            'url' => [
                'type' => 'url',
            ],
            'submit' => [
                'type' => 'submit',
            ],
        ],

        'validate' => [

            'title' => 'required',
            'url' => 'url',

        ],

    ],

];
