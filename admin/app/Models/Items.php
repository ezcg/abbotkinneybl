<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Items extends Model
{
    protected $fillable = ['title', 'description','deactivated'];

    protected $table = 'items';

    public function cats()
    {
        return $this->belongsTo('App\Models\Cats');
    }

    public function itemscats()
    {
        return $this->belongsTo('App\Models\ItemsCats');
    }

    public static function isHashtagItemsId($itemsId) {

        if (empty($itemsId)) {
            return false;
        }
        $r = self::select("id")->where("id", "=", $itemsId)->where("title", "like", "#%")->get();
        return $r->count() ? 1 : 0;

    }

    public static function getHashtagItems() {

        $r = self::select("*")->where("title", "like", "#%")->get();
        if ($r) {
            $r = $r->toArray();
        }
        return $r;
    }
    
    public static function getItemsTitleWithItemsId($itemsId) {

        if (empty($itemsId)) {
            return false;
        }
        $title = self::select("title")->where("id", "=", $itemsId)->get()->pluck("title")->first();
        return $title;

    }
    

}
