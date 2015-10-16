<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\User;
use App\Content;
use App\Image;

class AdminTest extends TestCase
{
    use DatabaseTransactions;

    public function test_unlogged_or_regular_user_can_see_admin_menu()
    {
        $user1 = factory(App\User::class)->create([
                'verified' => 'true',
                'role' => 'regular',
        ]);

        // Unlogged user

        $this->visit('/')
            ->dontSee(trans('menu.auth.admin'));

        // Regular user

        $this->actingAs($user1)
            ->visit('/')
            ->dontSee(trans('menu.auth.admin'));
    }

    public function test_unlogged_or_regular_user_can_not_see_images()
    {

        // Unlogged user

        $response = $this->call('GET', 'admin/image');
        $this->assertEquals(401, $response->status());

        // Regular user

        $user1 = factory(App\User::class)->create([
                'verified' => 'true',
                'role' => 'regular',
        ]);

        $response = $this->actingAs($user1)
            ->call('GET', 'admin/image');
        $this->assertEquals(401, $response->status());
    }

    public function test_admin_user_can_see_images()
    {
        $user1 = factory(App\User::class)->create([
                'verified' => 'true',
                'role' => 'admin',
        ]);

        $this->actingAs($user1)
            ->visit('user/'.$user1->id)
            ->seeLink(trans('menu.auth.admin'))
            ->click(trans('menu.auth.admin'))
            ->seeLink(trans('menu.admin.image'))
            ->click(trans('menu.admin.image'))
            ->seePageIs('admin/image')
            ->see(trans('admin.image.index.title'));
    }

    public function test_admin_user_can_post_photos()
    {
        $user1 = factory(App\User::class)->create(['role' => 'admin']);

        $this->actingAs($user1)
            ->visit('admin/image')
            ->attach(storage_path().'/tests/test.jpg', 'image')
            ->press(trans('admin.image.create.submit.title'))
            ->seePageIs('admin/image');

        $filename = $this->getLatestImageFilename($user1->user_id);

        // Check original file exists

        $filepath = config('imagepresets.original.path').$filename;
        $this->assertTrue(file_exists($filepath));
        unlink($filepath);

        // See thumbnails exist

        foreach (['large', 'medium', 'small', 'small_square', 'xsmall_square'] as $preset) {
            $filepath = config("imagepresets.presets.$preset.path").$filename;
            $this->assertTrue(file_exists($filepath));
          //  unlink($filepath);
        }
    }

    public function test_unlogged_or_regular_user_can_not_see_unpublished_content()
    {

        // Unlogged user

        $response = $this->call('GET', 'admin/content');
        $this->assertEquals(401, $response->status());

        // Regular user

        $user1 = factory(App\User::class)->create([
                'verified' => 'true',
                'role' => 'regular',
        ]);

        $response = $this->actingAs($user1)
            ->call('GET', 'admin/content');
        $this->assertEquals(401, $response->status());
    }

    public function test_admin_user_can_see_unpublished_content()
    {
        $user1 = factory(App\User::class)->create([
            'verified' => 'true',
            'role' => 'admin',
        ]);

        $user2 = factory(App\User::class)->create([
            'verified' => 'true',
            'role' => 'admin',
        ]);

        $content1 = factory(App\Content::class)->create([
            'user_id' => $user2->id,
            'title' => 'Hello unpublished',
            'type' => 'forum',
            'status' => 0,
        ]);

        $this->actingAs($user1)
            ->visit('user/'.$user1->id)
            ->seeLink(trans('menu.auth.admin'))
            ->click(trans('menu.auth.admin'))
            ->seeLink(trans('menu.admin.content'))
            ->click(trans('menu.admin.content'))
            ->seePageIs('admin/content')
            ->see(trans('admin.content.index.title'))
            ->seeLink('Hello unpublished')
            ->click('Hello unpublished')
            ->seePageIs('content/'.$content1->type.'/'.$content1->id)
            ->see('Hello unpublished');
    }

    public function test_unlogged_or_regular_user_can_not_see_internal_forum()
    {

        // Unlogged user

        $response = $this->call('GET', 'content/internal');
        $this->assertEquals(401, $response->status());

        // Regular user

        $user1 = factory(App\User::class)->create([
                'verified' => 'true',
                'role' => 'regular',
        ]);

        $response = $this->actingAs($user1)
            ->call('GET', 'content/internal');
        $this->assertEquals(401, $response->status());
    }

    public function test_admin_user_can_see_internal_forum()
    {
        $user1 = factory(App\User::class)->create([
                'verified' => 'true',
                'role' => 'admin',
        ]);

        $user2 = factory(App\User::class)->create([
            'verified' => 'true',
            'role' => 'admin',
        ]);

        $content1 = factory(App\Content::class)->create([
            'user_id' => $user2->id,
            'title' => 'Hello internal',
            'type' => 'internal',
        ]);

        $this->actingAs($user1)
            ->visit('user/'.$user1->id)
            ->seeLink(trans('menu.auth.admin'))
            ->click(trans('menu.auth.admin'))
            ->seePageIs('content/internal')
            ->see(trans('content.internal.index.title'))
            ->seeLink('Hello internal')
            ->click('Hello internal')
            ->seePageIs('content/'.$content1->type.'/'.$content1->id)
            ->see('Hello internal');
    }

    public function getLatestImageFilename($user_id)
    {
        return Image::latest('id')->first()->filename;
    }
}
