<?php

namespace App\Http\Controllers;

use App\Models\Read;
use Illuminate\Http\Request;
use App\Models\AWSS3;

class WriteCategoryJsonController extends Controller
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
     * @param  \Illuminate\Http\Request  $request
     */
    public function index(Request $request)
    {
        //Read::writeCategoryJsonToS3();dd('done');

        $request->validate([
            'cats_id' => 'nullable|integer',
            'items_id' => 'nullable|integer'
        ]);

        $catsId = !empty($request->cats_id) ? $request->cats_id : false;

        // number of social media entities per item. eg. 5 tweets by thebrigvenice
        $offset = 0;
        $limit = \App\Site::inst('NUM_SINGLE_ITEM_CARDS');

        if (!$catsId) {
            // get cats in use, so from items_cats, not cats table
            $catsIdArr = \App\Models\ItemsCats::getActiveCatsIds();
        } else {
            $catsIdArr[] = $catsId;
        }

        if (empty($catsIdArr)) {
            $str = __METHOD__ . " did not find any active categories.";
            echo $str;
            \Log::error($str);
            return;
        }

        foreach($catsIdArr as $catsId) {
            // get all children, if any, of cats_id
            $r = Read::getChildrenCategories($catsId);

            // if there are no children and $r is a scalar, then get items under $catsId
            if (!is_array($r) && $r !== false) {
                // get items of single category
                $itemsArr = Read::getItemsArrWithCatsId($r);
                if (empty($itemsArr)) {
                    continue;
                }
                $isHashtag = \App\Models\Cats::isHashtagCategory($catsId);
                $itemsArr = Read::getSocialMediaWithItemsArr($itemsArr, $offset, $limit, $isHashtag);
                if (count($itemsArr) == 0) {
                    continue;
                }
                $itemsArr = Read::getContactInfo($itemsArr);
                $itemsArr = Read::getHistory($itemsArr);
                $itemsArr = Read::getSocialMediaAccounts($itemsArr);
                $itemsArr = Read::modifyItemsArr($itemsArr);

                // The feed is always saved in the context of the catgory. It is never saved by items id, only cats id
                // Either 1 content card per each item inside of a category, or max content cards per each item inside of a category

                // save all social_media per item to s3
                $filename = "category_" . $catsId . "_max.json";
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


            }
        }

        \App\Models\Read::writeDivisionCategoryJsonToS3();

    }

}