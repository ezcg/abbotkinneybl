<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as Model;
use Illuminate\Http\Request;
use \DB;


class SocialMediaAccounts extends Model
{
    protected $fillable = ['id', 'items_id', 'source_user_id', 'username', 'site', 'is_active', 'is_primary', 'use_avatar','avatar', 'name'];
    protected $table = 'social_media_accounts';

    public function items()
    {
        return $this->hasMany('App\Models\Items');
    }


    public static function insertInto($smaObj)
    {
        $q = "INSERT INTO social_media_accounts (source_user_id, username, site, avatar, created_at, updated_at) 
                          VALUES (?, ?, ?, ?, NOW(), NOW())
                          ON DUPLICATE KEY UPDATE avatar = ?, username = ?";
        $r = Db::insert($q, [
            $smaObj->source_user_id,
            $smaObj->username,
            $smaObj->site,
            $smaObj->avatar,
            $smaObj->avatar,
            $smaObj->username
        ]);
        return $r;
    }

    /*
     * Get social media accounts already associated with item
     */
    public function getAssocAccountsArr($itemsColl, $itemsObj)
    {

        $socialMediaAssocAccountsArr = [];
        $itemsIdArr = array_column($itemsColl->toArray()['data'], 'id');
        if (count($itemsIdArr)) {

            $socialMediaAssocAccountsColl = \DB::table('social_media_accounts')
                ->whereIn('items_id', $itemsIdArr)->get();
            $socialMediaAssocAccountsArr = $socialMediaAssocAccountsColl->toArray();
        }

        return $socialMediaAssocAccountsArr;

    }

    public static function getSocialMediaAccountsWithItemsId($itemsId)
    {

        $r = DB::table("social_media_accounts")
            ->join('items', 'social_media_accounts.items_id', '=', 'items.id')
            ->where("social_media_accounts.items_id", "=", $itemsId)
            ->get(['social_media_accounts.id AS sma_id', 'items.*', 'social_media_accounts.*']);
        return $r;

    }

    public static function updateAvatarWithSourceUserIdAndSite($avatar, $sourceUserId, $site)
    {
        $sourceUserId = strval($sourceUserId);
        $q = "UPDATE social_media_accounts SET avatar = ? WHERE source_user_id = ? AND site = ?";
        $r = \DB::update($q, [$avatar, $sourceUserId, $site]);
        return $r;
    }

    /* Get all the items that do not have a row in social_media_accounts for a site. eg. get all items
       that do not have a twitter account associated with it
    */
    public static function getItemsThatDoNotHaveSocialMediaAccount($site = '') {

        $r = DB::table("social_media_accounts")
            ->rightjoin('items', 'social_media_accounts.items_id', '=', 'items.id')
            ->whereNull("items_id");
        if ($site) {
            $r = $r->where('site', '=', $site);
        }
        $r = $r->get(['items.*']);
        return $r;

    }

    /*
     * @param $site - 'twitter.com', etc
     */
    public static function getSocialMediaAccountsThatDoNotHaveItems($site = '')
    {

        $r = DB::table("social_media_accounts")
            ->leftjoin('items', 'social_media_accounts.items_id', '=', 'items.id')
            ->whereNull("items.id");
        if ($site) {
            $r = $r->where('site', '=', $site);
        }
        $r = $r->get(['social_media_accounts.id AS sma_id', 'items.*', 'social_media_accounts.*']);
        return $r;

    }

    public static function getSocialMediaAccountWithUsernameAndSite($username, $site)
    {
        $r = DB::table("social_media_accounts")->where("username", "=", $username)->where("site", "=", $site)->get();
        return $r;
    }

    public static function getSocialMediaAccountWithIdAndSite($sourceUserId, $site)
    {
        $r = DB::table("social_media_accounts")->where("source_user_id", "=", $sourceUserId)->where("site", "=", $site)->get();
        return $r;
    }


    public static function getRowWithSourceUserIdAndSite($sourceUserId, $site)
    {
        $r = DB::table("social_media_accounts")
            ->where("source_user_id", "=", $sourceUserId)
            ->where("site", "=", $site)
            ->get();
        return $r;
    }

    public static function updateSocialMediaAccountsItemsIdWithId($itemsId, $id)
    {
        $q = "UPDATE social_media_accounts SET items_id = ?, ";
        // This method gets called as part of adding an item using the social media account username, so these are turned on:
        $q.= "is_active=1, is_primary=1, use_avatar=1 ";
        $q.= "WHERE id = ?";
        $r = \DB::update($q, [$itemsId, $id]);
        return $r;
    }

    public static function getActiveSocialMediaAccountsWithItemsId($itemsId) {
        $r = DB::table("social_media_accounts")
            ->where('is_active', '>', 0)
            ->where('use_avatar', '>', 0)
            ->where('is_primary', '>', 0)
            ->where('items_id', '=', $itemsId)
            ->get();
        return $r;
    }

    public static function updateSocialMediaAccountStatus(SocialMediaAccounts $SocialMediaAccounts) {

        // See if there is already an active, primary and use avatar sma for this item_id
        $r = self::getActiveSocialMediaAccountsWithItemsId($SocialMediaAccounts->items_id);
        $num = $r->count();
        if ($num) {
            $SocialMediaAccounts->is_primary = 0;
            $SocialMediaAccounts->use_avatar = 0;
            $SocialMediaAccounts->is_active = 1;
        } else {
            $SocialMediaAccounts->is_primary = 1;
            $SocialMediaAccounts->use_avatar = 1;
            $SocialMediaAccounts->is_active = 1;
        }
        // Unique constraint violation
        //$SocialMediaAccounts->save();
        $q = "UPDATE social_media_accounts SET is_active = ?, use_avatar= ?, is_primary = ?, items_id = ? 
              WHERE source_user_id = ?";
        $paramsArr = array(
            $SocialMediaAccounts->is_active,
            $SocialMediaAccounts->use_avatar,
            $SocialMediaAccounts->is_primary,
            $SocialMediaAccounts->items_id,
            $SocialMediaAccounts->source_user_id
        );
        $r = \DB::update($q, $paramsArr);
        return $r;
    }

}