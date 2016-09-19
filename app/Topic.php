<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
    // Setup

    public $timestamps = false;

    public function content()
    {
        return $this->belongsToMany('App\Content');
    }

    // V2

    public function vars()
    {
        return new V2TopicVars($this);
    }

    // V1

    public static function getNames()
    {
        return self::pluck('name', 'id')->sort();
    }
}
