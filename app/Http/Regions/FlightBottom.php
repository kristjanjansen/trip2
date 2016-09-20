<?php

namespace App\Http\Regions;

class FlightBottom
{
    public function render($flights)
    {
        return component('Grid4')
            ->with('items', $flights->map(function ($flight) {
                return region('FlightCard', $flight);
            }));
    }
}
