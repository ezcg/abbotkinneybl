<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

abstract class Feed extends Model
{

    abstract public function getFeed($id = '');

    abstract public function saveFeed(array $feedArr);

    abstract public function convertFeedToSocialMedia();

    abstract public function getUnconvertedFeed();

}