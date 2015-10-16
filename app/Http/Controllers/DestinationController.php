<?php

namespace App\Http\Controllers;

use View;
use Cache;
use App\Destination;

class DestinationController extends Controller
{
    public function index($id)
    {
        $destination = Destination::with('flags', 'flags.user')
            ->findOrFail($id);

        $types = [
            'news',
            'flight',
            'travelmate',
            'forum',
            'photo',
            'blog',
        ];

        $features = [];

        foreach ($types as $type) {
            $features[$type]['contents'] = $destination->content()
                ->whereType($type)
                ->with(config("content_$type.frontpage.with"))
                ->latest(config("content_$type.frontpage.latest"))
                ->take(config("content_$type.frontpage.take"))
                ->get();
        }

        return response()->view('pages.destination.index', [
            'destination' => $destination,
            'features' => $features,
        ])->header('Cache-Control', 'public, s-maxage='.config('destination.cache'));
    }
}
