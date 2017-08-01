<?php

namespace Tests\Feature;

use App\User;
use App\Content;
use Carbon\Carbon;
use Tests\BrowserKitTestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class TravelmateTest extends BrowserKitTestCase
{
    use DatabaseTransactions;

    public function test_regular_user_can_create_and_edit_travelmate()
    {
        $regular_user_creating_travelmate = factory(User::class)->create();

        $start_at = Carbon::now()->addMonths(1)->startOfMonth()->toDateTimeString();

        $this->actingAs($regular_user_creating_travelmate)
            //->visit('reisikaaslased')
            //->click(trans('content.travelmate.create.title'))
            //->seePageIs('travelmate/create')
            ->visit('travelmate/create2')
            ->type('Hello travelmate title', 'title')
            ->type('Hello travelmate body', 'body')
            ->type($start_at, 'start_at')
            ->type('From here to eternity', 'duration')
            ->press(trans('content.create.submit.title'))
            ->seePageIs('reisikaaslased')
            ->see('Hello travelmate title')
            ->seeInDatabase('contents', [
                'user_id' => $regular_user_creating_travelmate->id,
                'title' => 'Hello travelmate title',
                'body' => 'Hello travelmate body',
                'type' => 'travelmate',
                'status' => 1,
                'start_at' => $start_at,
                'duration' => 'From here to eternity',
            ]);

        $content = Content::whereTitle('Hello travelmate title')->first();
        $edited_start_at = Carbon::now()->addMonths(2)->startOfMonth()->toDateTimeString();

        $this->actingAs($regular_user_creating_travelmate)
            //->visit("reisikaaslased/$content->slug")
            //->click(trans('content.action.edit.title'))
            //->seePageIs("travelmate/$content->id/edit")
            ->visit("travelmate/$content->id/edit2")
            ->type('Hola travelmate titulo', 'title')
            ->type('Hola travelmate cuerpo', 'body')
            ->type('Hasta la eternidad', 'duration')
            ->type($edited_start_at, 'start_at')
            ->press(trans('content.edit.submit.title'))
            ->seePageIs("reisikaaslased/$content->slug")
            ->see('Hola travelmate titulo')
            ->seeInDatabase('contents', [
                'user_id' => $regular_user_creating_travelmate->id,
                'title' => 'Hola travelmate titulo',
                'body' => 'Hola travelmate cuerpo',
                'type' => 'travelmate',
                'status' => 1,
                'start_at' => $edited_start_at,
                'duration' => 'Hasta la eternidad',
            ]);
    }

    /*
    public function test_regular_user_cannot_edit_other_user_content()
    {
        $creator_user = factory(User::class)->create();
        $visitor_user = factory(User::class)->create();
        $datetime = Carbon::now()->addMonth(1)->toDateTimeString();
        $year = Carbon::parse($datetime)->year;
        $month = Carbon::parse($datetime)->month;
        $day = Carbon::parse($datetime)->day;

        // creator create content
        $this->actingAs($creator_user)
            ->visit('reisikaaslased')
            ->click(trans('content.travelmate.create.title'))
            ->seePageIs('travelmate/create')
            ->type('Creator title travelmate', 'title')
            ->type('Creator body travelmate', 'body')
            ->select($year, 'start_at_year')
            ->select($month, 'start_at_month')
            ->select($day, 'start_at_day')
            ->press(trans('content.create.submit.title'))
            ->see('Creator title travelmate')
            ->seeInDatabase('contents', [
                'user_id' => $creator_user->id,
                'title' => 'Creator title travelmate',
                'start_at' => $year.'-'.$month.'-'.$day.' 00:00:00',
                'type' => 'travelmate',
                'status' => 1,
            ]);

        // visitor view content
        $content_id = $this->getContentIdByTitleType('Creator title travelmate');
        $this->actingAs($visitor_user);
        $response = $this->call('GET', "content/travelmate/$content_id/edit");
        $this->visit("content/travelmate/$content_id")
            ->dontSeeInElement('form', trans('content.action.edit.title'))
            ->assertEquals(404, $response->status());
    }

    public function test_regular_user_can_create_content()
    {
        $regular_user = factory(User::class)->create();
        $datetime = Carbon::now()->addMonth(1)->toDateTimeString();
        $year = Carbon::parse($datetime)->year;
        $month = Carbon::parse($datetime)->month;
        $day = Carbon::parse($datetime)->day;

        $this->actingAs($regular_user)
            ->visit('reisikaaslased')
            ->click(trans('content.travelmate.create.title'))
            ->seePageIs('travelmate/create')
            ->type('Hello title', 'title')
            ->type('Hello body', 'body')
            ->select($year, 'start_at_year')
            ->select($month, 'start_at_month')
            ->select($day, 'start_at_day')
            ->press(trans('content.create.submit.title'))
            ->see('Hello title')
            //->see(str_limit($regular_user->name, 24))
            ->see($regular_user->name)
            ->seeInDatabase('contents', [
                'user_id' => $regular_user->id,
                'title' => 'Hello title',
                'start_at' => $year.'-'.$month.'-'.$day.' 00:00:00',
                'type' => 'travelmate',
                'status' => 1,
            ]);

        $content = Content::whereTitle('Hello title')->first();

        $new_datetime = Carbon::now()->addMonth(2)->toDateTimeString();
        $year = Carbon::parse($new_datetime)->year;
        $month = Carbon::parse($new_datetime)->month;
        $day = Carbon::parse($new_datetime)->day;

        $this->actingAs($regular_user)
                ->visit(config('sluggable.contentTypeMapping')['travelmate'].'/'.$content->slug)
                ->click(trans('content.action.edit.title'))
                ->seePageIs("travelmate/$content->id/edit")
                ->type('Hola titulo', 'title')
                ->type('Hola boditula', 'body')
                ->select($year, 'start_at_year')
                ->select($month, 'start_at_month')
                ->select($day, 'start_at_day')
                ->press(trans('content.edit.submit.title'))
                ->seePageIs(config('sluggable.contentTypeMapping')['travelmate'].'/'.$content->slug)
                ->see('Hola titulo')
                ->seeInDatabase('contents', [
                    'user_id' => $regular_user->id,
                    'title' => 'Hola titulo',
                    'start_at' => $year.'-'.$month.'-'.$day.' 00:00:00',
                    'type' => 'travelmate',
                    'status' => 1,
                ]);
    }

    public function test_admin_user_can_edit_content()
    {
        $creator_user = factory(User::class)->create();
        $editor_user = factory(User::class)->create([
            'role' => 'admin',
            'verified' => 1,
        ]);

        $datetime = Carbon::now()->addMonth(1)->toDateTimeString();
        $year = Carbon::parse($datetime)->year;
        $month = Carbon::parse($datetime)->month;
        $day = Carbon::parse($datetime)->day;

        // creator create content
        $this->actingAs($creator_user)
            ->visit('reisikaaslased')
            ->click(trans('content.travelmate.create.title'))
            ->seePageIs('travelmate/create')
            ->type('Creator title travelmate', 'title')
            ->type('Creator body travelmate', 'body')
            ->select($year, 'start_at_year')
            ->select($month, 'start_at_month')
            ->select($day, 'start_at_day')
            ->press(trans('content.create.submit.title'))
            ->see('Creator title travelmate')
            ->seeInDatabase('contents', [
                'user_id' => $creator_user->id,
                'start_at' => $year.'-'.$month.'-'.$day.' 00:00:00',
                'title' => 'Creator title travelmate',
                'body' => 'Creator body travelmate',
                'type' => 'travelmate',
                'status' => 1,
            ]);

        $datetime = Carbon::now()->addMonth(2)->toDateTimeString();
        $year = Carbon::parse($datetime)->year;
        $month = Carbon::parse($datetime)->month;
        $day = Carbon::parse($datetime)->day;

        // editor edit content
        $content_id = $this->getContentIdByTitleType('Creator title travelmate');
        $this->actingAs($editor_user)
            ->visit("content/travelmate/$content_id")
            ->click(trans('content.action.edit.title'))
            ->seePageIs("travelmate/$content_id/edit")
            ->type('Editor title travelmate', 'title')
            ->type('Editor body travelmate', 'body')
            ->select($year, 'start_at_year')
            ->select($month, 'start_at_month')
            ->select($day, 'start_at_day')
            ->press(trans('content.edit.submit.title'))
            ->seeInDatabase('contents', [
                'user_id' => $creator_user->id,
                'start_at' => $year.'-'.$month.'-'.$day.' 00:00:00',
                'title' => 'Editor title travelmate',
                'body' => 'Editor body travelmate',
                'type' => 'travelmate',
                'status' => 1,
            ]);
    }

    private function getContentIdByTitleType($title)
    {
        return Content::whereTitle($title)->first()->id;
    }
    */
}
