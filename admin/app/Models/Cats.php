<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cats extends Model
{
    protected $fillable = ['title', 'description', 'level', 'rank', 'deactivated'];

    protected $table = 'cats';

    public function items()
    {
        return $this->hasMany('App\Models\Items');
    }
    public function catspandc()
    {
        return $this->hasMany('App\Models\CatsPandC');
    }

    /*
     * Returns id => title array usually for the dropdown to filter by category
     */
    public static function getCatsIdAndTitleArr() {

        $catsArr = \DB::table('cats')
            ->orderBy("title", "asc")
            ->pluck('title', 'id')
            ->toArray();
        return $catsArr;
    }

    public static function isHashtagCategory($catsId) {
        if (empty($catsId)) {
            return 0;
        }
        $catsTitle = self::select("title")->where('cats.id', $catsId)->get()->pluck('title')->first();
        if (strtolower($catsTitle) == 'hashtag' || strtolower($catsTitle) == 'hashtags') {
            return 1;
        }
        return 0;
    }

    public static function getHashtagCategory() {
        $hashtagCategoryArr = self::select("*")->where('cats.title', "hashtags")->first();
        if ($hashtagCategoryArr) {
            $hashtagCategoryArr = $hashtagCategoryArr->toArray();
        }
        return $hashtagCategoryArr;
    }
    public static function getHashtagCategoryId() {
        $id = self::select("*")->where('cats.title', "hashtags")->get()->pluck("id")->first();
        return $id;
    }
}
