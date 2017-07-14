<?php

namespace App\Utils;

use Markdown;
use App\Image;

class BodyFormatter
{
    protected $body;

    public function __construct($body)
    {
        $this->body = $body;
    }

    public function markdown()
    {
        $this->body = Markdown::parse($this->body);

        return $this;
    }

    public function links()
    {
        $this->body = str_replace(' www.', ' http://', $this->body);

        // Modified version of
        // http://stackoverflow.com/a/5289151
        // and http://stackoverflow.com/a/12590772

        $pattern = "/(?i)\b((?:https?:\/\/|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:'\".,<>?«»“”‘’]))(?![^<>]*>)/i";

        if ($filteredBody = preg_replace($pattern, '<a href="$1">$1</a>', $this->body)) {
            $this->body = $filteredBody;
        }

        if ($filteredBody = preg_replace('/(<a href="(http|https):(?!\/\/(?:www\.)?trip\.ee)[^"]+")>/is', '\\1 target="_blank">', $this->body)) {
            $this->body = $filteredBody;
        }

        return $this;
    }

    public function images()
    {
        $imagePattern = '/\[\[([0-9]+)\]\]/';

        if (preg_match_all($imagePattern, $this->body, $matches)) {
            foreach ($matches[1] as $match) {
                if ($image = Image::find($match)) {
                    $this->body = str_replace(
                        "[[$image->id]]",
                        '<img src="'.$image->preset('large').'" />',
                        $this->body
                    );
                }
            }
        }

        return $this;
    }

    public function format()
    {
        return $this
            ->markdown()
            ->links()
            ->images()
            ->body;
    }
}
