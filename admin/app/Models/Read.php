<?php

namespace App\Models;

use App\Site;
use Illuminate\Database\Eloquent\Model;

class Read extends Model
{

    public static function modifyItemsArr($itemsArr) {

        if (\App\Site::inst('SITEKEY') == 'uscongress') {
            $q = "SELECT id, title FROM cats WHERE title = 'Democrat' OR title = 'Republican' OR title = 'Independent'";
            $catsArr = \DB::select($q, []);
            foreach($catsArr as $catsObj) {
                $dbArr = \App\Models\Read::getItemsArrWithCatsId($catsObj->id);
                foreach($dbArr as $key => $row) {
                    foreach($itemsArr as $j => $itemsObj) {
                        if ($row->items_id == $itemsObj->items_id) {
                            $suffix = '';
                            if ($catsObj->title == 'Democrat') {
                                $suffix = ' - D';
                            } else if ($catsObj->title == 'Republican') {
                                $suffix = ' - R';
                            } else if ($catsObj->title == 'Independent') {
                                $suffix = ' - I';
                            }
                            $itemsArr[$j]->title = $itemsArr[$j]->title . $suffix;
                        }
                    }
                }
            }
        }

        return $itemsArr;

    }

    /*
     * Any items.title that starts with a # is a hashtag
     */
    public static function getHashtagItemsArr($catsId = false) {

        $r = \DB::table('items')
            ->select("items.id as items_id", "items.title as title")
            ->join('items_cats', 'items.id', 'items_cats.items_id')
            ->join('cats', 'cats.id', 'items_cats.cats_id');
        if ($catsId) {
            $r = $r->where('cats.id', $catsId)->where("cats.deactivated", "=", 0);
        }
        $r = $r->where('items.title', 'like', '#%')
            ->where("items.deactivated", "=", 0)
            ->get()->toArray();

        return $r;

    }

    public static function getSocialMediaAccounts($itemsArr) {
        foreach($itemsArr as $itemsId => $arr) {
            $r = SocialMediaAccounts::select("username", "site")
                ->where('items_id', $itemsId)
                ->where('is_active', 1)
                ->get();
            $itemsArr[$itemsId]->social_media_accounts_arr = [];
            foreach($r as $obj) {
                $url = "//" . $obj->site . "/" . $obj->username;
                if ($obj->site == "yelp.com") {
                    $url = "//" . $obj->site . "/biz/" . $obj->username;
                }
                $itemsArr[$itemsId]->social_media_accounts_arr[$obj->site] = $url;
            }
        }
        return $itemsArr;
    }

    public static function getContactInfo($itemsArr)
    {

        if (!empty(\App\Site::inst('USES_CONTACT_INFO'))) {
            foreach($itemsArr as $itemsId => $arr) {
                $q = "SELECT * FROM contact_info WHERE items_id = ?";
                $r = \DB::select($q, [$itemsId]);
                if (is_array($r) && isset($r[0]) && !empty($r[0])) {
                    $r = $r[0];
                    $itemsArr[$itemsId]->website = $r->website;
                    $itemsArr[$itemsId]->address = self::formatAddress($r);
                    $itemsArr[$itemsId]->lat = $r->lat;
                    $itemsArr[$itemsId]->lon = $r->lon;
                    $itemsArr[$itemsId]->phone = $r->phone_number;
                    $hoursObj = json_decode($r->hours);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $hoursObj = '';
                    }
                    $itemsArr[$itemsId]->hours = $hoursObj;
                }
            }
        }

        return $itemsArr;

    }

    public static function getHistory($itemsArr)
    {

        if (!empty(\App\Site::inst('USES_HISTORY'))) {
            foreach($itemsArr as $itemsId => $arr) {
                $q = "SELECT * FROM wikipedia WHERE items_id = ? AND deactivated = 0";
                $r = \DB::select($q, [$itemsId]);
                if (is_array($r) && isset($r[0]) && !empty($r[0])) {
                    $r = $r[0];
                    $historyStr = trim(strip_tags($r->description));
                    $historyStrLength = strlen($historyStr);
                    if ($historyStrLength > 220) {
                        // find last full word at around 200 characters
                        $historyStrLength = 220;
                        $historyStr = substr($historyStr, 0, $historyStrLength);
                        do {
                            $char = $historyStr[$historyStrLength - 1];
                            $historyStrLength--;
                        } while($char != " ");
                        $historyStr = substr($historyStr,0, $historyStrLength) . "...";
                    }
                    $itemsArr[$itemsId]->history = $historyStr;
                    $itemsArr[$itemsId]->history_url = $r->url;
                }
            }
        }

        return $itemsArr;

    }

    private static function formatAddress($obj)
    {

        $str = '';
        if (!empty($obj->address)) {
            $obj->address = str_replace("Abbot Kinney Blvd", "Abbot Kinney Bl", $obj->address);
            $str.= $obj->address;
        }
        if (!empty($obj->city)) {
            $obj->city = str_replace("Los Angeles", "LA", $obj->city);
            $str.=", " . $obj->city;
        }
        if (!empty($obj->state)) {
            $str.=", " . $obj->state;
        }
        if (!empty($obj->postal_code)) {
            $str.=", " . $obj->postal_code;
        }

        return $str;

    }

    /*
     * Array(
        [1] => Array        (
            [title] => Bar
            [cats_id] => 1
            [items] => Array (
                [0] => stdClass Object (
                        [items_id] => 5
                        [items_title] => TownhouseTweets
                        [items_description] =>
     */
    public static function getTopLevel()
    {
        $dataArr = [];
        $parentCatsArr = self::getParentOnlyCats();
        foreach($parentCatsArr as $obj) {
            $dataArr[$obj->id]['title'] = $obj->title;
            $dataArr[$obj->id]['cats_id'] = $obj->id;
        }
        return $dataArr;
    }

    public static function getCatsWithChildId($id)
    {
        $q = "SELECT cats.id, cats.title FROM cats
              INNER JOIN cats_p_and_c ON cats.id = cats_p_and_c.child_id
              AND cats_p_and_c.parent_id = ? 
              ";
        $r = \DB::select($q, [$id]);
        return $r;
    }

    public static function getChildren($obj, $childDataArr, $parentId = 0)
    {
        $catsArr = self::getCatsWithChildId($obj->id);
        if (count($catsArr)) {
            foreach($catsArr as $catsObj) {
                $childrenArr = self::getChildren($catsObj, $childDataArr);
                if (count($childDataArr)) {
                    $childDataArr[$catsObj->id]['children'] = $childrenArr;
                } else {
                    $childDataArr[$catsObj->id]['children'] = [];
                }
            }
        } else {

        }

        return $childDataArr;
    }

    public static function getItems($obj, $dataArr)
    {
        $dataArr[$obj->id]['items'] = self::getItemsWithCatsId($obj->id);
        if (count($dataArr[$obj->id]['items'])) {
            foreach($dataArr[$obj->id]['items'] as $i => $itemsObj) {
                $dataArr[$obj->id]['items'][$i]->social_media = self::getSocialMediaWithItemsId($itemsObj->items_id);
            }
        }
        return $dataArr;
    }

    public static function getParentOnlyCats()
    {
        return self::getCatsWithParentId(0);
    }

    public static function getCatsWithParentId($id)
    {
        $q = "SELECT cats.id, cats.title FROM cats
              INNER JOIN cats_p_and_c ON cats.id = cats_p_and_c.child_id
              AND cats_p_and_c.parent_id = ? 
              WHERE cats.deactivated = 0";
        $r = \DB::select($q, [$id]);

        return $r;
    }

    public static function getItemsWithCatsId($id)
    {
        $q = "SELECT i.id as items_id, i.title as items_title, i.description as items_description 
              FROM items i
	          INNER JOIN items_cats ic ON i.id = ic.items_id 
	          WHERE cats_id = ? AND i.deactived = 0 and cats.deactivated = 0";
        $r = \DB::select($q, [$id]);

        return $r;
    }

    public static function getSocialMediaWithItemsId($itemsId, $offset = 0, $limit = 2)
    {
        $q = "SELECT sma.username, sma.avatar, sm.site, sm.link, sm.text, sm.created_at 
              FROM social_media_accounts sma 
              INNER JOIN social_media sm ON sma.source_user_id = sm.source_user_id  
              WHERE 1 = 1 
              AND sma.items_id = ? 
              AND sma.is_active = 1  
              ORDER BY sm.created_at DESC 
              LIMIT $limit OFFSET $offset";
        $r = \DB::select($q, [$itemsId]);
        return $r;
    }

    public static function getChildrenCategories($catsId)
    {
        $o = new \App\Models\CatsPandC();
        $catsArr = $o->getFlattenedHier();
        $r = self::processChildrenCategories($catsArr, $catsId);
        return $r;
    }

    private static function processChildrenCategories($catsArr, $currentCatsId)
    {

        if (!is_array($catsArr)) {
            return false;
        }

        foreach($catsArr as $id => $arr) {

            if ($id == $currentCatsId) {
                return $arr;
            }
            if (!is_array($arr)) {
                continue;
            }
            if (is_array($arr)) {
                $r = self::processChildrenCategories($arr, $currentCatsId);
                if ($r !== false) {
                    return $r;
                }
            }

        }

        return false;

    }

    /*
     * Get items rows that are not deactivated and inside a catgory that is not deactivated
     */
    public static function getActiveItemsArr()
    {

        $q = "SELECT items.title, items_id 
              FROM items
              INNER JOIN items_cats ON items.id = items_cats.items_id 
              INNER JOIN cats c on c.id=cats_id 
              WHERE items.deactivated = 0 AND c.deactivated = 0 
              GROUP BY items_id";
        $r = \DB::select($q, []);
        return $r;
    }

    public static function getItemsArrWithCatsId($catsId)
    {

        $q = "SELECT items.title, items_id 
              FROM items
              INNER JOIN items_cats ON items.id = items_cats.items_id 
              INNER JOIN cats c on c.id=cats_id 
              WHERE cats_id = ? AND items.deactivated = 0 AND c.deactivated = 0";
        $r = \DB::select($q, [$catsId]);
        return $r;
    }

    public static function getItemsArrWithItemsId($itemsId)
    {
        $q = "SELECT id as items_id, title, description  
              FROM items
              WHERE id = ? AND deactivated = 0";
        $r = \DB::select($q, [$itemsId]);
        return $r;
    }

    public static function getSocialMediaWithItemsArr($itemsArr, $offset = 0, $limit = 3, $isHashtag = 0)
    {

        if ($isHashtag) {
            $socialMediaDbArr = self::qHashtagSocialMediaWithItemsArr($itemsArr, $offset, $limit);
        } else {
            $socialMediaDbArr = self::qSocialMediaWithItemsArr($itemsArr, $offset, $limit);
        }
        if (count($socialMediaDbArr) == 0) {
            \Log::warning(__METHOD__ . " line: " . __LINE__ . " no social media found with items_ids: " . json_encode($itemsArr));
            return [];
        }
        $itemsArr = self::sortFinalItemsArr($itemsArr, $socialMediaDbArr);
        $itemsArr = self::setAvatar($itemsArr);
        if (\App\Site::inst('MUST_HAVE_IMAGE')) {
            foreach($itemsArr as $key => $itemObj) {
                foreach($itemObj->social_media as $smKey => $smObj) {
                    if (!strstr($smObj->text, "socialMediaThumb")) {
                        unset($itemObj->social_media[$smKey]);
                    }
                }
                if (count($itemsArr[$key]->social_media) == 0) {
                    unset($itemsArr[$key]);
                }
            }

        }
        return $itemsArr;

    }

    public static function qHashtagSocialMediaWithItemsArr($itemsArr, $offset, $limit)
    {

        if (count($itemsArr) == 0) {
            return false;
        }

        $dbArr = [];
        //$smIdSetArr = [];
        foreach($itemsArr as $key => $itemsObj) {

            $q = "SELECT sm.source_id, sm.source_user_id, sm.username, sm.text, sm.link, sm.site, sm.created_at, sm.site, items.id as items_id, items.title as title     
                  FROM social_media sm
                  LEFT JOIN social_media_accounts sma ON sma.source_user_id = sm.source_user_id
                  INNER JOIN items ON items.title = sm.hashtag   
                  WHERE 1 =1 
                  AND sma.source_user_id IS NULL 
                  AND sm.hashtag = '" . $itemsObj->title . "' 
                  AND sm.deleted = 0 
                  AND sm.created_at > NOW() - interval 30 day ";
//            if (count($smIdSetArr)) {
//                $q.="AND sm.source_id NOT IN (" . implode(",", $smIdSetArr) . ")";
//            }
            $q.= "GROUP BY sm.source_id ";
            $q.= "ORDER BY sm.created_at DESC ";
            $q.= "LIMIT $limit OFFSET $offset";
            $r = \DB::select($q);
            if (count($r)) {
                $dbArr[] = $r;
            }

        }

        return $dbArr;

    }

    public static function qSocialMediaWithItemsArr($itemsArr, $offset, $limit)
    {

        if (count($itemsArr) == 0) {
            return false;
        }

        $dbArr = [];
        foreach($itemsArr as $key => $obj) {

            $q = "SELECT sm.source_id, sm.source_user_id, sm.username, sm.text, sm.link, sm.site, sm.created_at, 
                  sma.items_id, sma.username, sma.site   
                  FROM social_media_accounts sma 
                  INNER JOIN social_media sm on sma.source_user_id = sm.source_user_id 
                  WHERE 1 =1 
                  AND sma.site = sm.site 
                  AND sma.items_id = ?  
                  AND sma.is_active = 1  
                  AND sm.deleted = 0 ";
            if (!empty(\App\Site::inst('USES_YELP'))) {
                $q.="AND ((sm.created_at > NOW() - interval 30 day AND sm.site != 'yelp.com' ) OR (sm.site = 'yelp.com' )) ";
            } else{
                $q.="AND sm.created_at > NOW() - interval 30 day ";
            }
            $q.="ORDER BY sm.created_at DESC 
            LIMIT $limit OFFSET $offset";
            $r = \DB::select($q, array($obj->items_id));
            if (count($r)) {
                $dbArr[] = $r;
            }

        }

        return $dbArr;

    }

    public static function setAvatar($itemsArr)
    {
        if (empty($itemsArr)) {
            return $itemsArr;
        }

        $q = "SELECT avatar, items_id 
              FROM social_media_accounts 
              WHERE 1 = 1 
              AND items_id IN (" . implode(', ', array_keys($itemsArr)) . ") 
              AND use_avatar = 1 AND is_active = 1
              GROUP BY Items_id";
        $r = \DB::select($q);
        if (is_array($r) && !empty($r)) {
            foreach($r as $obj) {
                $itemsArr[$obj->items_id]->avatar = $obj->avatar;
            }
        }
        return $itemsArr;
    }

    /*
     * Sort top level array by created_at date of most recent social media item
     */
    private static function sortFinalItemsArr($itemsArr, $socialMediaDbArr)
    {

        if (empty($socialMediaDbArr)) {
            return $itemsArr;
        }

        $sortArr = [];
        $newItemsArr = [];
        foreach($itemsArr as $key => $itemObj) {
            $itemsId = $itemObj->items_id;
            if (count($socialMediaDbArr) == 0) {
                \Log::warning(__METHOD__ . " line: " . __LINE__ . " no social media found for " . $itemObj->title . " so not adding");
                continue;
            }
            foreach($socialMediaDbArr as $dbRow) {
                // just check the first row and if a match, set all the rows to social_media
                // and set the first social_media row's created_at into sort array
                if ($dbRow[0]->items_id == $itemsId && !empty($dbRow[0]->text)) {
                    $newItemsArr[$itemsId] = $itemObj;
                    $newItemsArr[$itemsId]->social_media = $dbRow;
                    $sortArr[$itemsId] = strtotime($dbRow[0]->created_at);
                    break;
                }
            }

        }


        $finalItemsArr = [];
        if (!empty($sortArr)) {
            arsort($sortArr);
            $count = 0;
            foreach($sortArr as $itemsId => $ut) {
                $finalItemsArr[$itemsId] = $newItemsArr[$itemsId];
                $finalItemsArr[$itemsId]->rank = $count;
                $count++;
            }
        } else {
            $finalItemsArr = $newItemsArr;
        }

        return $finalItemsArr;

    }

//    public static function getSocialMediaWithItemId($itemsId, $offset = 0, $limit = 3)
//    {
//
//        $itemsObj = new \stdClass();
//        $itemsObj->items_id = $itemsId;
//        $itemsArr = array($itemsObj);
//        return self::getSocialMediaWithItemsArr($itemsArr, $offset, $limit);
//
//    }

    public static function writeCategoryJsonToS3() {

        $maxLevel = Site::inst('MAX_CATEGORY_LEVEL');
        $r = Cats::select("id", "title", "rank", "image")
            ->where("level", "=", $maxLevel)
            ->where("deactivated", "=", 0)
            ->orderBy("rank", "asc")
            ->get()->toArray();

        // Don't write category if it does not have social media associated with it
        foreach($r as $key => $arr) {
            $isHashtagCategory = \App\Models\Cats::isHashtagCategory($arr['id']);
            if ($isHashtagCategory) {
                $r2 = \App\Models\SocialMedia::getHashtagSocialMediaWithCatsId($arr['id'], $includedDelete = 0);
            } else {
                $r2 = \App\Models\SocialMedia::getSocialMediaWithCatsId($arr['id']);
            }
            if ($r2->count() == 0) {
                \Log::warning(__METHOD__ . " line: " . __LINE__ . " category " . $arr['title'] . " has no members and/or members have no social media so skipping");
                unset($r[$key]);
            }
        }

        $json = json_encode($r);
        $path = "/tmp/";
        $filename = "max_level_category.json";
        $r = file_put_contents($path . $filename, $json);
        if (!$r) {
            \Log::error("Failed to save json $json to $path$filename");
        } else {
            // update json on aws
            $bucket = \App\Site::inst('AWS_BUCKET');
            $aws = new AWSS3();
            $aws->updateS3('json/' . $filename, $path.$filename, $bucket);

        }
        self::writeDivisionCategoryJsonToS3();
    }

    // Level category 2 and the related level category 3 beneath
    // eg. Get NFL divisions( AFC East, NFC East, etc) and the teams below them.
    // OR
    // Parent only category
    public static function writeDivisionCategoryJsonToS3() {

        if (Site::inst('MAX_CATEGORY_LEVEL') == 1) {

            $r = Cats::select("id", "title", "rank", "image")
                ->where("deactivated", "=", 0)
                ->orderBy("rank", "asc")
                ->get()->toArray();

            $divArr = [];
            foreach($r as $key => $arr) {

                // If no social_media associated with cats_id, skip
                $isHashtagCategory = \App\Models\Cats::isHashtagCategory($arr['id']);
                if ($isHashtagCategory) {
                    $r2 = \App\Models\SocialMedia::getHashtagSocialMediaWithCatsId($arr['id'], $includeDeleted = 0, "items.id");
                    // since this is for division_category, we only want row per unique hashtag
                    $itemsArr = $r2->get();
                } else {
                    $r2 = \App\Models\SocialMedia::getSocialMediaWithCatsId($arr['id']);
                    $r2->groupBy("items.id");
                    $itemsArr = $r2->get();
                }

                if ($r2->count() == 0) {
                    \Log::warning(__METHOD__ . " line: " . __LINE__ . " category " . $arr['title'] . " has no members and/or members have no social media so skipping");
                    \Log::warning(__METHOD__ . " ic.cats_id: " . $arr['id']);
                    continue;
                }

                // The above query is a check to see if there is social_media associated with the items_id. However,
                // the avatar cannot be retrieved at the same time as the above check because the used avatar may
                // be associated with a social_media_account that has no social_media.
                // For example, yelp sma but no avatar and social media, twitter sma but no social media and avatar

                if (!$isHashtagCategory) {
                    foreach($itemsArr as $key2 => $obj) {
                        $q = "SELECT avatar FROM social_media_accounts 
                              WHERE items_id = ? AND use_avatar = 1 AND is_active = 1";
                        $r3 = \DB::select($q, [$obj->items_id]);
                        if (count($r3) && isset($r3[0])) {
                            $itemsArr[$key2]->avatar = str_replace("http:", "", $r3[0]->avatar);
                        }
                    }
                }

                $divArr[$key] = [];
                $divArr[$key]['cats_id'] = $arr['id'];
                $divArr[$key]['cats_title'] = $arr['title'];
                $divArr[$key]['image'] = $arr['image'];
                $divArr[$key]['rank'] = $arr['rank'];
                $divArr[$key]['teams'] = $itemsArr;

            }

        } else if (Site::inst('MAX_CATEGORY_LEVEL') > 1) {

            $maxLevel = Site::inst('MAX_CATEGORY_LEVEL');
            // ($maxLevel - 1) is the category parent of the Main Accounts that are its children
            $r = Cats::select("id", "title", "rank", "image")
                ->where("level", "=", $maxLevel - 1)
                ->where("deactivated", "=", 0)
                ->orderBy("rank", "asc")
                ->get()->toArray();

            $divArr = [];
            foreach($r as $key => $arr) {
                $q = "SELECT c.id, c.title, image 
                    FROM cats c 
                    INNER JOIN cats_p_and_c cpc ON c.id = cpc.child_id AND cpc.parent_id = ? AND c.deactivated = 0 ";
                $r = \DB::select($q, [$arr['id']]);
                if (count($r) == 0) {
                    \Log::warning(__METHOD__ . " line: " . __LINE__ . " category " . $arr['title'] . " has no teams so skipping");
                    \Log::warning(__METHOD__ . " " . $q . " cpc.parent_id: " . $arr['id']);
                    continue;
                }
                $divArr[$key] = [];
                $divArr[$key]['cats_id'] = $arr['id'];
                $divArr[$key]['cats_title'] = $arr['title'];
                $divArr[$key]['image'] = $arr['image'];
                $divArr[$key]['rank'] = $arr['rank'];
                $divArr[$key]['teams'] = $r;

            }

        } else {
            \Log::error(__METHOD__ . " MAX_CATEGORY_LEVEL not greater than 0");
            return;
        }

        // If a PHP array does not have any explicitly defined keys OR if all the keys - after being typecast
        // - are not perfectly sequential from 0…X then the PHP array becomes a JSON array [indexed].
        // [{data1,data2}] is what is desired, not [0:{data1},1:{data2}]
        $json = json_encode(array_values($divArr));
        $path = "/tmp/";
        $filename = "division_category.json";
        $r = file_put_contents($path . $filename, $json);
        if (!$r) {
            \Log::error("Failed to save json $json to $path$filename");
        } else {
            // update json on aws
            $bucket = \App\Site::inst('AWS_BUCKET');
            $aws = new AWSS3();
            $aws->updateS3('json/' . $filename, $path.$filename, $bucket);

        }

    }


}
