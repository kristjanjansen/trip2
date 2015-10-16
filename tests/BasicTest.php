<?php


class BasicTest extends TestCase
{
    public function test_seeing_frontpage_without_logging_in()
    {
        $this->visit('/')
             ->see(config('site.name'));
    }

    public function test_seeing_content_pages_without_logging_in()
    {
        $types = [
            'news',
            'shortnews',
            'flight',
            'travelmate',
            'forum',
            'expat',
            'buysell',
            'photo',
            'blog',
        ];

        foreach ($types as $type) {
            $this->visit('/content/'.$type)
            ->see(config('site.name'));
        }
    }
}
