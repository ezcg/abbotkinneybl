<?php

namespace App\Models;

use \App\Models\SocialMediaAccounts;
use \App\Models\Hours;
use \DB;

class Yelp  extends Feed
{

    protected $table = 'yelp';
    protected $fillable = ['id', 'biz_id', 'items_id', 'rating', 'text', 'review_url', 'created_at'];

    public function __construct()
    {
        $this->yelpFusion = new YelpFusion();
    }

    public function updateContactInfo($id = '', $action)
    {

        //DB::enableQueryLog();
        $savedArr = [];
        $contactArr = [];
        if ($action == 'missing') {

            // all social_media_accounts where site='yelp.com' that don't have a row in contact info
            $yelpSMAArr = DB::table("social_media_accounts")
            ->leftJoin('contact_info', 'contact_info.biz_id', '=', 'social_media_accounts.username')
            ->where("social_media_accounts.site", "=", "yelp.com")
            ->whereNull("contact_info.biz_id")
            ->where('is_active', '=', 1)
            ->get(["social_media_accounts.*"])
            ->toArray();

        } else {

            $itemsIdArr = DB::table("contact_info")->where("no_yelp_update", "=", 1)->pluck("items_id");

            $yelpSMAArr = DB::table("social_media_accounts")
                ->join("items", 'items.id', '=', 'social_media_accounts.items_id')
                ->where("deactivated", "=", 0)
                ->where('site', '=', 'yelp.com')
                ->where('is_active', '=', 1);
            if (count($itemsIdArr)) {
                $yelpSMAArr->whereNOTIN('items_id', $itemsIdArr);
            }

            if ($id) {
                $yelpSMAArr = $yelpSMAArr->where('source_user_id', '=', $id);
            }
            $yelpSMAArr = $yelpSMAArr->get(['social_media_accounts.*'])->toArray();

        }

        print "<pre>";
        foreach($yelpSMAArr as $obj) {
            try {
                $yelpObj = $this->yelpFusion->bizlookup($obj->username, $this->yelpFusion->getOauthToken(), 0);
            } catch (\Exception $e) {
                \Log::error(__METHOD__ . " " . $e->getMessage());
                print $obj->username . " failed ";
                echo "Sleep for one second to avoid throttling from too many requests.";
                sleep(1);// avoid too many requests
                continue;
            }

            $contactArr[$obj->items_id] = $yelpObj;
            echo $obj->username . " succeeded ";
            echo "\nSleep for one second to avoid throttling from too many requests.\n";
            sleep(1);// avoid too many requests
        }

        $r = $this->saveContactInfo($contactArr);
        if (!empty($r)) {
            $savedArr[] = $r;
        }
        return $savedArr;
    }

    public function saveContactInfo(array $contactArr)
    {

        $finalArr = [];
        foreach($contactArr as $itemsId => $yelpObj) {

            // check if hours are updatable by yelp
            $r = DB::table('hours')->where('items_id', $itemsId)->get();
            $noYelpUpdate = 0;
            $id = 0;
            if ($r && $r->count()) {
                $noYelpUpdate = $r[0]->no_yelp_update;
                $id = $r[0]->id;
            }
            if ($noYelpUpdate == 0) {
                $hoursJson = $this->formatHours($yelpObj);
                if (!empty($hoursJson)) {
                    $hoursModel = new Hours;
                    $hoursModel->id = $id;
                    $hoursModel->items_id = $itemsId;
                    $hoursModel->hours = $hoursJson;
                    $hoursModel->save();
                }
            }

            $r = DB::table('contact_info')
                ->where('items_id', $itemsId)
                ->where("no_yelp_update", "=", 1)
                ->get();
            $noYelpUpdate = $r ? $r->count() : 0;
            if ($noYelpUpdate) {
                continue;
            }

            $arr = [
                'biz_id' => $yelpObj->alias,
                'items_id' => $itemsId,
                'business' => $yelpObj->name,
                'address' => $yelpObj->location->display_address[0],
                'address2' => $yelpObj->location->display_address[1],
                'city' => $yelpObj->location->city,
                'state' => $yelpObj->location->state,
                'postal_code' => $yelpObj->location->zip_code,
                'phone_number' => $yelpObj->phone,
                'lat' => $yelpObj->coordinates->latitude,
                'lon' => $yelpObj->coordinates->longitude
            ];

            DB::table('contact_info')->insert($arr);
            $finalArr[] = $arr;

        }

        return $finalArr;

    }

    private function formatHours($yelpObj)
    {

        $hoursJson = '';
        if (isset($yelpObj->hours[0]->open)) {
            $hoursArr = $yelpObj->hours[0]->open;
            $formattedHours = [];
            foreach($hoursArr as $i => $obj) {
                $day = $this->formatDay($obj->day);
                $hours = $this->formatTime($obj->start);
                $end = $this->formatTime($obj->end);
                if ($end) {
                    $hours.= " - " . $end;
                }
                $formattedHours[$i] = $day . " " . $hours;
            }

            $hoursJson = json_encode($formattedHours);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $hoursJson = '';
            }
        }

        return $hoursJson;

    }

    // 0 to 6, representing day of the week from Monday to Sunday
    private function formatDay($num)
    {
        if ($num == 0) {
            return "Mon";
        } else if ($num == 1) {
            return "Tue";
        } else if ($num == 2) {
            return "Wed";
        } else if ($num == 3) {
            return "Thu";
        } else if ($num == 4) {
            return "Fri";
        } else if ($num == 5) {
            return "Sat";
        } else if ($num == 6) {
            return "Sun";
        }
        return $num;
    }

    private function formatTime($str)
    {
        if ($str == "0000" || empty($str)) {
            return "";
        }
        $hour = substr($str, 0, 2);
        $min = substr($str, 2, 4);
        $ut = mktime($hour, $min, 0, date("m"), date("d"), date("Y"));
        $hour = date("h:i a", $ut);
        return $hour;

    }

    /*
     * Get feed from yelp and save it to yelp table
     */
    public function getFeed($source_user_id = '') {

        $this->yelpFusion = new YelpFusion();
        $r = SocialMediaAccounts::where('site', '=', 'yelp.com')
            ->where('is_active', '=', 1);
        if ($source_user_id) {
            $r->where('source_user_id', '=', $source_user_id);
        }

        $yelpFeedArr = [];
        $yelpAccountsArr = $r->get()->toArray();
        $authToken = $this->yelpFusion->getOauthToken();
        foreach($yelpAccountsArr as $arr) {
            try {
                $yelpFeedObj = $this->yelpFusion->bizlookup($arr['username'], $authToken, true);
            } catch(\GuzzleHttp\Exception\ClientException $e) {
                \Log::error(__METHOD__ . " " . $e->getMessage());
                continue;
            }
            $yelpFeedArr[$arr['items_id']] = $yelpFeedObj;
            $yelpFeedArr[$arr['items_id']]->biz_id = $arr['username'];
            //echo "Sleep for one second to avoid throttling from too many requests.";
            sleep(1);// avoid too many requests
        }
        $this->saveFeed($yelpFeedArr);
        return $yelpFeedArr;
    }

    public function saveFeed(array $feedArr) {

        foreach($feedArr as $itemsId => $feedObj) {
            $bizId = $feedObj->biz_id;
            foreach($feedObj->reviews as $obj) {
                $r = DB::table('yelp')->where('id', '=', $obj->id)->get();
                if ($r && $r->count()) {
                    continue;
                }
                $arr = [
                    'id' => $obj->id,
                    'biz_id' => $bizId,
                    'rating' => $obj->rating,
                    'items_id' => $itemsId,
                    'text' => $obj->text,
                    'review_url' => $obj->url,
                    'created_at' => $obj->time_created,
                    'updated_at' => $obj->time_created
                ];
                // error reference YelpFusion when trying to do $this->create($arr);
                DB::table('yelp')->insert($arr);

            }
        }
    }

    public function getUnconvertedFeed()
    {
        $q = "SELECT yelp.* FROM yelp
              LEFT JOIN
              social_media ON social_media.source_id = yelp.id
              AND social_media.site = 'yelp.com' 
              WHERE social_media.id is null";
        $r = DB::select($q);
        return $r;
    }

    /*
     * Convert short urls to full, add hyperlinks to @ and #, convert smart quotes, etc
     */
    public function convertFeedToSocialMedia()
    {

        $minRating = \App\Site::inst('YELP_MIN_RATING');
        $objArr = [];
        $r = $this->getUnconvertedFeed();
        foreach($r as $dbObj) {

            if ($dbObj->rating < $minRating) {
                continue;
            }

            $dbObj->text = Utility::cleanText($dbObj->text);
            $dbObj->text = Utility::tighten($dbObj->text);
            $dbObj->text = "Yelp review: " . $dbObj->text;
            $rating = "Rating: " . $dbObj->rating . " stars";
            $dbObj->text.= " <a href='" . $dbObj->review_url . "' target='_blank'>$rating</a>";
            $objArr[] = $dbObj;
        }

        $this->saveConvertedFeedToSocialMedia($objArr);
        return $objArr;

    }

    private function saveConvertedFeedToSocialMedia($objArr)
    {
        if (!count($objArr)) {
            return;
        }
        foreach($objArr as $obj) {
            $arr = [
                'source_user_id' => $obj->biz_id,
                'source_id' => $obj->id,
                'username' => $obj->biz_id,
                'site' => 'yelp.com',
                'link' => $obj->review_url,
                'text' => iconv("UTF-8", "UTF-8//IGNORE", $obj->text),
                'created_at' => $obj->created_at
            ];
            SocialMedia::updateOrCreate($arr);
        }

    }


}