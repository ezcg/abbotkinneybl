<?php

namespace App\Http\Controllers;

use App\Models\Read;
use Illuminate\Http\Request;
use App\Models\AWSS3;

class WriteItemsJsonController extends Controller
{

    public function __construct() {
        $env = \Config::get('app.env');
        if ($env == 'production') {
            exit("This can only be called in cron or local environment, not production");
        }
    }

    /**
     * Get social media specific to category and write to json files
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        // get items of all categories
        $itemsArr = Read::getActiveItemsArr();
        if (empty($itemsArr)) {
            $str = __METHOD__ . " did not find any active items";
            echo $str;
            \Log::error($str);
            return;
        }
        // number of social media entities per item. eg. 5 tweets by thebrigvenice
        $offset = 0;
        $limit = \App\Site::inst('NUM_SINGLE_ITEM_CARDS');
        $itemsArr = Read::getSocialMediaWithItemsArr($itemsArr, $offset, $limit);
        $itemsArr = Read::getContactInfo($itemsArr);
        $itemsArr = Read::getHistory($itemsArr);
        $itemsArr = Read::getSocialMediaAccounts($itemsArr);
        $itemsArr = Read::modifyItemsArr($itemsArr);

        // save all social_media per item to s3
        foreach($itemsArr as $i => $obj) {
            $filename = "items_id_" . $obj->items_id . ".json";
            AWSS3::saveJSONToS3($itemsArr[$i], $filename);
        }

        // a lookup table in json for items_id => title

        $filename = 'items_lookup.json';
        $lookupArr = [];
        foreach($itemsArr as $i => $obj) {
            $lookupArr[$obj->items_id]['image'] = $obj->avatar;
            $lookupArr[$obj->items_id]['title'] = $obj->title;
        }
        $hashtagItemsArr = Read::getHashtagItemsArr();
        foreach($hashtagItemsArr as $i => $obj) {
            $lookupArr[$obj->items_id]['image'] = '';
            $lookupArr[$obj->items_id]['title'] = $obj->title;
        }

        AWSS3::saveJSONToS3($lookupArr, $filename);

    }

}