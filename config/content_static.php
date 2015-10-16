<?php

return [

    'index' => [

        'with' => ['destinations', 'topics'],
        'latest' => 'created_at',
        'paginate' => 25,
    ],

    'edit' => [

        'fields' => [
            'title' => [
                'type' => 'text',
                'title' => 'Title',
                'large' => true,
            ],
            'body' => [
                'type' => 'textarea',
                'title' => 'Body',
            ],
            'submit' => [
                'type' => 'submit',
                'title' => 'Add',
            ],
        ],

        'validate' => [

            'title' => 'required',
            'body' => 'required',

        ],

    ],

];
