<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SocialMedia extends Model
{
    protected $fillable = ['username', 'source_user_id', 'text', 'site', 'created_at', 'source_id', 'link', 'hashtag'];

    // The categories are associated with items, not social_media nor social_media_accounts,
    // so if searching by category, there must be a chain of inner joins
    // that leads to items from category id and sma and sm related to the items in that category are retrieved
    /*
     * @param $catsId - the category id that is immediately above the items
     * @param $getHidden - when displaying sm in the admin, we want deleted aka hidden, but not when getting for public
     */
    public static function getSocialMediaWithCatsId($catsId, $getHidden = 0) {
        DB::enableQueryLog();
        $r = DB::table("items_cats")
            ->select('social_media.*', 'items.title', 'items.id as items_id')
            ->join("items", "items.id", "=", "items_cats.items_id")
            ->join("cats", "cats.id", "=", "items_cats.cats_id")
            ->join("social_media_accounts", "social_media_accounts.items_id", "=", "items.id")
            ->join("social_media", "social_media.source_user_id", "=", "social_media_accounts.source_user_id")
            ->where("items_cats.cats_id", "=", $catsId);
        if ($getHidden == 0) {
            $r->where("social_media.deleted", "=", 0);
        }
        $r->where("social_media_accounts.is_active", "=", 1)
        ->where("items.deactivated", "=",0)
        ->where("cats.deactivated", "=",0)->get();
        $x=DB::getQueryLog();
        return $r;
    }
    
    public static function getHashtagSocialMediaWithCatsId($catsId = false, $includeDeleted = 1, $groupBy = 'source_id') {

        $itemsArr = Read::getHashtagItemsArr($catsId);

        $r = DB::table("social_media")
            ->select("social_media.*", "items.id as items_id", "items.title as title")
            ->leftJoin("social_media_accounts as sma", "sma.source_user_id", "=", "social_media.source_user_id")
            ->join("items", "items.title", "=", "social_media.hashtag")
            ->whereNull("sma.source_user_id");

        if ($includeDeleted != 1) {
            // when viewing in admin, we may want all sm or not
            $r = $r->where('deleted', "=", 0);
        } else {
            $r = $r->where('deleted', "!=", 2);
        }
        $r = $r->where(function($q) use ($itemsArr) {
            foreach($itemsArr as $obj) {
                $q->orWhere(function($query) use ($obj) {
                    $query->where("social_media.hashtag", "=", $obj->title);
                });
            }
        })->groupBy($groupBy);

        return $r;

    }

}
