<?php

namespace App\Http\Controllers;

use RedditAPI;
use App\Models\Reddit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RedditController extends Controller
{

    public function __construct() {
        $env = \Config::get('app.env');
        if ($env == 'production') {
            exit("This can only be called in cron or local environment, not production");
        }
    }


    /*
     * Setting up a subreddit as a feed; add the subreddit name (eg. reddit.com/r/49ers would be 49ers) as Main Account
     * Set the Main Account to a category (eg. AFC East)
     * beneath the Main Account, click
     * Social Media Accounts: add
     * Fill out form and use subreddit name with the preceding /r/  (eg. /r/49ers) and set it to 'reddit.com'
     */
    public function read (Request $request) {

        if ($request->onlyconvert) {
            $reddit = new Reddit();
            $reddit->convertFeedToSocialMedia();
        } else {
            //foreach(array("top", "hot") as $order) {
            foreach(array("hot") as $order) {
                // get each item from db
                $smaArr = DB::table('social_media_accounts')
                    ->where("site", "=", "reddit.com")
                    ->where("is_active", "=", 1)
                    ->select()->pluck('username')->toArray();
                // get json for each each item
                $feedArr = [];
                $optArr = [
                    CURLOPT_HTTPHEADER => ["Accept: application/json"],
                    CURLOPT_USERAGENT => "User-Agent: ezcg.com\r\n"
                ];
                foreach($smaArr as $key => $sma) {
                    //$filename = "https://reddit.com/r/politics/top/.json";
                    $filename = "https://reddit.com/r/" . $sma . "/" . $order . "/.json";
                    //\Log::warning(__METHOD__ . " ++++++++++++++ calling " . $filename);
                    try {
                        //$json = Utility::curlGet($filename, 20, $optArr);
                        $json = file_get_contents($filename);
                        $obj = json_decode($json);
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            \Log::warning(__METHOD__ . " json error " . json_last_error() . " " . json_last_error_msg() . " " . $json );
                            continue;
                        }
                        foreach($obj->data->children as $dataObj) {
                            $feedArr[] = $dataObj->data;
                        }
                    } catch(Exception $e) {
                        \Log::error(__METHOD__ . " " . $e->getMessage());
                    }
                    $reddit = new Reddit();
                    $reddit->saveFeed($feedArr);
                    //\Log::warning(__METHOD__ . " ++++++++++++++ finished and saved " . $filename);
                }

            }
        }
    }


}
