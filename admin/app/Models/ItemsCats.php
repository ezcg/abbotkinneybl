<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemsCats extends Model
{
    protected $fillable = ['cats_id', 'items_id'];

    protected $table = 'items_cats';

    public function items()
    {
        return $this->hasMany('App\Models\Items');
    }

    public function getItemsCats($itemsColl)
    {

        $itemsIdArr = array_column($itemsColl->toArray()['data'], 'id');
        $itemsCatsColl = array();
        if (count($itemsIdArr)) {
            $itemsCatsColl = \DB::table('items_cats')
                ->whereIn('items_id', $itemsIdArr)->get();
        }
        return $itemsCatsColl;
    }

    public static function getCatsIdsInUse()
    {
        $q = "SELECT cats_id FROM items_cats GROUP BY items_cats.cats_id";
        $r = \DB::select($q, []);
        $catsIdArr = array_column($r, 'cats_id');
        return $catsIdArr;
    }
    public static function getAllCatsIds()
    {
        $q = "SELECT id FROM cats";
        $r = \DB::select($q, []);
        $catsIdArr = array_column($r, 'id');
        return $catsIdArr;
    }

    public static function getActiveCatsIds()
    {
        $q = "SELECT id FROM cats WHERE deactivated = 0";
        $r = \DB::select($q, []);
        $catsIdArr = array_column($r, 'id');
        return $catsIdArr;
    }
}
