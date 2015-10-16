<?php

return [

    'name' => 'Trip2',

    'allowedtags' => '<b><i><strong><em><a><br><ul><ol><li><img><iframe><h4>',

    'currency' => [

        'symbol' => '€',

    ],

    'cache' => [
        'frontpage' => 60 * 10,
        'destination' => 60 * 10,
        'content' => [
            'index' => 60 * 10,
            'show' => 60 * 10,
        ],
        'user' => 60 * 10,
    ],
];
