<?php

namespace Tests\Feature;

use App\User;
use App\Content;
use Tests\BrowserKitTestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PrivacyTest extends BrowserKitTestCase
{
    use DatabaseTransactions;

    public function test_unlogged_user_can_access_privacy_page()
    {
        if (! Content::whereSlug('privaatsustingimused')->first()) {
            factory(Content::class)->create([
                'id' => 106740,
                'user_id' => factory(User::class)->create(['role' => 'superuser'])->id,
                'type' => 'page',
                'title' => 'privaatsustingimused',
            ]);
        }

        $this
            ->visit('/')
            ->see('Privaatsustingimused')
            ->click('Privaatsustingimused')
            ->seePageIs('privaatsustingimused')
            ->see('Privaatsustingimused');
    }
}
