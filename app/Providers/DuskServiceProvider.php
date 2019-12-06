<?php

namespace App\Providers;

use Laravel\Dusk\Browser;
use Illuminate\Support\ServiceProvider;

class DuskServiceProvider extends ServiceProvider
{
    /**
     * Register the Dusk's browser macros.
     *
     * @return void
     */
    public function boot()
    {
        Browser::macro('scrollToId', function ($id = null) {
            $this->script("document.getElementById('$id').scrollIntoView();");

            return $this;
        });

        Browser::macro('scrollToDusk', function ($title = null) {
            $this->script("document.querySelectorAll('[dusk=" . slug($title) . "]')[0].scrollIntoView();");

            return $this;
        });
    }
}
