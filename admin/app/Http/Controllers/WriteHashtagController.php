<?php

namespace App\Http\Controllers;

use App\Models\Read;
use App\Site;
use Illuminate\Http\Request;
use Validator, Input, Redirect, Session;
use \App\Models\AWSS3;

class WriteHashtagController extends Controller
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
    public function items(Request $request)
    {

        if (!Site::inst('USES_HASHTAG_CATEGORIES')) {
            return;
        }

        // get items under hashtag category
        $itemsArr = Read::getHashtagItemsArr();
        if (empty($itemsArr)) {
            \Log::error(__METHOD__ . " did not find any items");
            return;
        }
        // number of social media entities per item. eg. 5 tweets by thebrigvenice
        $offset = 0;
        $limit = \App\Site::inst('NUM_SINGLE_ITEM_CARDS');
        $itemsArr = Read::getSocialMediaWithItemsArr($itemsArr, $offset, $limit, $hashtag = 1);

        // save all social_media per item to s3
        foreach($itemsArr as $i => $obj) {
            $filename = "items_id_" . $obj->items_id . ".json";
            AWSS3::saveJSONToS3($itemsArr[$i], $filename);
        }

        Read::writeCategoryJsonToS3();

    }

    public function category() {

        if (!Site::inst('USES_HASHTAG_CATEGORIES')) {
            return;
        }

        // The feed is always saved in the context of the catgory. It is never saved by items id, only cats id
        // Either 1 content card per each item inside of a category, or 20 content cards per each item inside of a category

        // save all 20 social_media per item to s3
        $catsId = \App\Models\Cats::select("id")->where("title", "like", "hashtag%")->get()->pluck("id")->first();
        if (!$catsId) {
            return;
        }
        
        // get items under hashtag category
        $itemsArr = Read::getHashtagItemsArr();
        $itemsArr = Read::getSocialMediaAccounts($itemsArr);
        $offset = 0;
        $limit = 20;
        $itemsArr = Read::getSocialMediaWithItemsArr($itemsArr, $offset, $limit, $isHashtag = 1);

        $filename = "category_" . $catsId . "_20.json";
        AWSS3::saveJSONToS3($itemsArr, $filename);

        // save the default social_media content per item in the default json file
        $configLimit = \App\Site::inst('NUM_ENTITIES_PER_ITEM');
        if (empty($configLimit)) {
            throw new \Exception(__METHOD__ . ' NUM_ENTITIES_PER_ITEM must be a non-zero number in Sites config class');
        }
        foreach($itemsArr as $i => $obj) {
            $itemsArr[$i]->social_media = array_splice($itemsArr[$i]->social_media, 0, $configLimit);
        }
        $filename = "category_" . $catsId . ".json";

        AWSS3::saveJSONToS3($itemsArr, $filename);

        Read::writeCategoryJsonToS3();
        
    }

}
