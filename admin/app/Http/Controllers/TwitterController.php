<?php

namespace App\Http\Controllers;

use App\Models\Tweets as Tweets;
use App\Models\TwitterBlvd as TwitterBlvd;
use Illuminate\Http\Request;
use \Log;
use Twitter;
use Validator, Input, Redirect, Session;
use App\Site;

class TwitterController extends Controller
{

    public function __construct() {
        $env = \Config::get('app.env');
        if ($env == 'production') {
            exit("This can only be called in cron or local environment, not production");
        }
    }

    // Search for places that can be attached to a statuses/update.
    // Given a latitude and a longitude pair, an IP address, or a name, this request will return a list of all the valid
    // places that can be used as the place_id when updating a status.
    public function getgeosearch() {

        $paramArr = [
            "place_id" => "5e97c1a7f94d856f",
            "granularity" => "neighborhood",
            "attribute:street_address" => "abbot kinney boulevard"
        ];
        $r = Twitter::getGeoSearch($paramArr);
        echo (json_encode($r, JSON_PRETTY_PRINT));
    }

    // Venice Beach place_id 5e97c1a7f94d856f

    //
    // Given a latitude and a longitude, searches for up to 20 places that can be used as a place_id when updating a status.
    // This request is an informative call and will deliver generalized results about geography.
    //
    public function getgeoreverse() {

        $paramArr = [
            "granularity" => "neighborhood",
            "attribute:street_address" => "abbot kinney boulevard",
            "lat" => "33.992337",
            "long" => "-118.473154"
        ];
        $r = Twitter::getGeoReverse($paramArr);
        print "<pre>";
        echo (json_encode($r, JSON_PRETTY_PRINT));
    }

    public function getgeo() {

        $id = "5e97c1a7f94d856f";
        $id = "10fa7c865694d002";
        $r = Twitter::getGeo($id);
        print "<pre>";
        echo (json_encode($r, JSON_PRETTY_PRINT));
    }

    ### Trend

//* `getTrendsPlace()` - Returns the top 10 trending topics for a specific WOEID, if trending information is available for it.
//* `getTrendsAvailable()` - Returns the locations that Twitter has trending topic information for.
//* `getTrendsClosest()` - Returns the locations that Twitter has trending topic information for, closest to a specified location.
//getSearch
    /**
     * https://api.twitter.com/1.1/search/tweets.json?q=&geocode=-22.912214,-43.230182,1km&lang=pt&result_type=recent .
     * Returns a collection of relevant Tweets matching a specified query.
     *
     * Parameters :
     * - q
     * - geocode
     * - lang
     * - locale
     * - result_type (mixed|recent|popular)
     * - count (1-100)
     * - until (YYYY-MM-DD)
     * - since_id
     * - max_id
     * - include_entities (0|1)
     * - callback
     */
    public function gethashtags() {

        if (!Site::inst('USES_HASHTAG_CATEGORIES')) {
            return;
        }


// Venice Blvd and AK "33.9884768,-118.4669999"
// Brooks and AK 33.990594,-118.477836
// California and AK 33.9920015,-118.4745882

        // Geolocalization: the search operator “near” isn’t available in the API, but there is a more precise way to restrict your query by a given location using the geocode parameter specified with the template “latitude,longitude,radius”, for example, “37.781157,-122.398720,1mi”. When conducting geo searches, the search API will first attempt to find Tweets which have lat/long within the queried geocode, and in case of not having success, it will attempt to find Tweets created by users whose profile location can be reverse geocoded into a lat/long within the queried geocode, meaning that is possible to receive Tweets which do not include lat/long information.

        $hashtagArr = \App\Models\Read::getHashtagItemsArr();
        // could add geocoding to paramArr
        // "geocode" => "33.9920015,-118.4745882,1mi",

        foreach($hashtagArr as $hashtagObj) {

            $paramArr = [
                "q" => $hashtagObj->title,
                "lang" => "en",
                "include_entities" => 1,
                "result_type" => 'recent',
                'count' => 100,
                'tweet_mode' => 'extended',
                'exclude_replies' => 1
            ];

            $tweetsObj = Twitter::getSearch($paramArr);
//$tweetsStr = file_get_contents("/tmp/tweet.txt");
//$tweetsArr = json_decode($tweetsStr);
            if (property_exists($tweetsObj, "statuses")) {
                $tweetsArr = $tweetsObj->statuses;
                $retrieved = count($tweetsArr);
                echo $hashtagObj->title . " retrieved $retrieved<br>";
                $tweetsModel = new Tweets;
                $tweetsArr = $tweetsModel->removeAlreadyAdded($tweetsArr);
                $tweetsArr = $tweetsModel->removeIfSocialMediaAccountExists($tweetsArr);
                $remaining = count($tweetsArr);
                echo $hashtagObj->title . " $remaining remaining after removeAlreadyAdded and removeIfSocialMediaAccountExists called<br>";
                if (count($tweetsArr)) {
                    $tweetsModel->saveFeed($tweetsArr, $hashtag = $hashtagObj->title);
                    $tweetsModel->convertFeedToSocialMedia($hashtag = $hashtagObj->title);
                } else {
                    echo $hashtagObj->title . " No tweets retrieved after removing already saved tweets<br>";
                }
            } else {
                echo $hashtagObj->title . "property statuses not found in tweetsObj<br>";
            }

        }

    }

    public function index() {
        $str = file_get_contents("/var/app/current/public/tweet.json");
        $tweetArr = json_decode($str);
        $tweetModel = new Tweets;
        foreach($tweetArr as $tweetObj) {
            $arr = (object)$tweetModel->parseTweetObj($tweetObj);
            $tweetDBObj = $tweetModel->debug($arr);
            echo printR($tweetDBObj); exit;
        }

        //$tweetObj->saveFeed([$tweetsObj]);
        //$tweetObj->convertFeedToSocialMedia();


    }
    /*
     *
     * Obtain a collection of the lists the specified user is subscribed to, 20 lists per page by default.
     * Does not include the user’s own lists.
     *
     */
    public function getlistsubscriptions()
    {

        $twitterMain = Site::inst('TWITTER_MAIN');
        $params = [
//            'user_id' => Site::inst('TWITTER_USER_ID'),
            'screen_name' => $twitterMain,
            "count" => "200"
        ];
        $twitterObj = Twitter::getListSubscriptions($params);
        if (property_exists($twitterObj, 'lists') && count($twitterObj->lists)) {
            dd($twitterObj->lists);
        } else {
            exit("No list subscribed to found with params: " . json_encode($params));
        }

    }



    // Update feeds by grabbing from a twitter 'list' of people following on twitter instead of the twitter homepage
    public function getlist()
    {
        set_time_limit(0);
        $time = -microtime(true);
        // main timeline does not use 'lists' and instead uses main account members, so it shouldn't get here
        $slug = Site::inst('TWITTER_LIST_NAME_ARR');
        $site = Site::inst('SITEKEY');
        $twitterScreenName = Site::inst('TWITTER_SCREENNAME');
        $twitterMain = Site::inst('TWITTER_MAIN');
        if (!$slug) {
            echo "This endpoint is for twitter lists and twitter screenname '$twitterMain' for site $site uses the main twitter timeline and not a list.";
            Log::warning(__METHOD__ . " This endpoint is for twitter lists and twitter screenname '$twitterMain' uses the main twitter timeline and not a list.");
            return;
        }

        // 'listId' is for lists that are 'subscribed' to, not lists that the twitter account owner generated
        // To enable multiple list subscriptions set comma delimited list of ids in .env file named TWITTER_LIST_ID_ARR
        $listIdArr = Site::inst('TWITTER_LIST_ID_ARR');
        $listIdArr = explode(",", $listIdArr);
        $arrayOfParamArr = [];
        if (count($listIdArr) && !empty($listIdArr[0])) {
            foreach ($listIdArr as $listId) {
                $arrayOfParamArr[] = [
                    'list_id' => $listId,
                    "count" => "200",
                    'tweet_mode' => 'extended',
                    'include_entities' => 1,
                    'exclude_replies' => 1,
                    'include_rts' =>1
                ];
            }
        } else {
            $arrayOfParamArr[] = [
                'include_entities' => 1,
                'tweet_mode' => 'extended',
                'exclude_replies' => 1,
                'include_rts' =>1,
                'owner_screen_name' => $twitterMain,
                'slug'=>$slug,
                "count" => "200"
            ];
        }

        try {
            $numTweetsProcessed = 0;
            $debug = 0;
            foreach($arrayOfParamArr as $paramArr) {
                //Debug by referring to json file, not twitter
                if ($debug) {
                    $str = file_get_contents("/var/app/current/public/tweets.json");
                    //$tweetsArr = [];
                    $tweetsArr = json_decode($str);
                    //echo printR($tweetsArr);exit;
                } else {

                    $tweetsArr = \Twitter::getListStatuses($paramArr);
                    $json = json_encode($tweetsArr, JSON_PRETTY_PRINT);
                    $filename = "/tmp/" . Site::inst('SITEKEY') . "_" . $paramArr['list_id'] . "_tweets." . date("Y-m-d_H_i_s") . ".json";
                    file_put_contents($filename, $json);
                    if (empty($tweetsArr)) {
                        Log::warning(__METHOD__ . " tweetsArr empty for twitter list name '$slug' and site '$site' for twitter screenname '$twitterScreenName'");
                        return;
                    }
                }
                $tweetsModel = new Tweets;
                echo "<pre>";
                echo "\nlist_id: " . $paramArr['list_id'];
                echo "\nNum tweets retrieved: " . count($tweetsArr);
                $tweetsArr = $tweetsModel->removeAlreadyAdded($tweetsArr);
                echo "\nNum tweets after removing those that were already added: " . count($tweetsArr);
                $tweetsArr = $tweetsModel->removeIfSocialMediaAccountDoesNotExist($tweetsArr);
                echo "\nNum tweets after removing because no social media account exists: " . count($tweetsArr);

                if (count($tweetsArr)) {
                    $numTweetsProcessed+=count($tweetsArr);
                    $tweetsModel->saveFeed($tweetsArr);
                    $tweetsModel->updateTwitterAvatarsWithTweetsArr($tweetsArr);
                }
                set_time_limit(0);

            }
        } catch(\Exception $e) {
            Log::error(__METHOD__ . " $site " . $e->getMessage());
            Log::error(__METHOD__ . " $site paramArr: " . json_encode($paramArr));
        }
        $time += microtime(true);
        echo "\nTwitterController:getlist execution time: " . sprintf('%f', $time);
        echo "\nNum tweets processed: $numTweetsProcessed\n";
        echo "</pre><hr>";
    }

    public function getlistmembers(Request $request)
    {

        $slug = Site::inst('TWITTER_LIST_NAME_ARR');
        $twitterMain = Site::inst('TWITTER_MAIN');
        $site = Site::inst('SITEKEY');
        if ($slug == false) {
            exit("'$site' does not use lists and instead uses home timeline from $twitterMain.");
        }
        // 'listId' is for lists that are 'subscribed' to, not lists that the twitter account owner generated
        // To enable multiple list subscriptions...
        $listIdArr = Site::inst('TWITTER_LIST_ID_ARR');
        $listIdArr = explode(",", $listIdArr);
        $arrayOfParamArr = [];
        if (count($listIdArr) && !empty($listIdArr[0])) {
            foreach($listIdArr as $listId) {
                $arrayOfParamArr[] = ['list_id' => $listId, "count" => "3000"];
            }
        } else {
            $arrayOfParamArr[] = $paramArr = ['owner_screen_name' => $twitterMain, 'slug'=>$slug, "count" => "1000"];
        }

        try {
            foreach($arrayOfParamArr as $paramArr) {
                $twitterUsersObj = \Twitter::getListMembers($paramArr);
                foreach($twitterUsersObj->users as $key => $obj) {
                    unset($obj->status);
                    unset($obj->entities);
                    $twitterUsersObj->users[$key] = $obj;
                }
                $resultArr = TwitterBlvd::saveListMembersToSocialMediaAccounts($twitterUsersObj, 'twitter.com');
                if (empty($request->redirect)) {
                    echo "<pre>";
                    echo "num users twitter users examined: " . count($twitterUsersObj->users) . "<br>";
                    print_r($resultArr);
                }
            }
        } catch(\Exception $e) {
            Log::error(__METHOD__ . " " . $e->getMessage());
            Log::error(__METHOD__ . " paramArr: " . json_encode($paramArr));
        }

        if (!empty($request->redirect)) {
            return redirect()->route('socialmediaaccounts.admin', ['view' => 'unassociated', 'page' => $request->page, 'sort' => 'new', 'search' => $request->search]);
        }

    }

    /**
     * Return the home timeline for the main account (not list)
     *
     * @return \Illuminate\Http\Response
     */
    public function getfeed()
    {
        set_time_limit(0);
        $slug = Site::inst('TWITTER_LIST_NAME_ARR');
        $twitterMain = Site::inst('TWITTER_MAIN');

        if ($slug) {
            exit("'$slug' is a list for the main account $twitterMain. This endpoint gets the main timeline for $twitterMain and not a list. Call /twitter/getlist for $slug.");
        }
        $tweetsModel = new Tweets();
        $tweetsArr = $tweetsModel->getFeed();

        $tweetsArr = $tweetsModel->removeAlreadyAdded($tweetsArr);
        echo "\n<br>\n# tweets after already added removed:" . count($tweetsArr) . "\n<br>\n";

        // file_put_contents("/tmp/tweets.txt", json_encode($tweetsArr));
        //$str = file_get_contents("/tmp/tweets.txt");
        //$tweetsArr = json_decode($str);
        //$tweetsArr = array($tweetsArr[2]);
        //echo printR($tweetsArr);exit;

        $tweetsModel->saveFeed($tweetsArr);
        $tweetsModel->convertFeedToSocialMedia();
        $tweetsModel->updateTwitterAvatarsWithTweetsArr($tweetsArr);

        json_encode($tweetsArr);
        return;
    }

    // Convert all rows in tweets table that do not exist in social_media table
    // For developing this tweetsObj method directly. Delete from social_media table and leave tweets intact to test converting to social_media
    // To have it write to json, call 'read' endpoint for subdomain with path /writejson
    public function convertfeedtosocialmedia(Request $request) {

        $hashtag = NULL;
        if (!empty($request->hashtag)) {
            $hashtag = $request->hashtag;
        }
        $tweetsObj = new Tweets();
        $tweetsObj->convertFeedToSocialMedia($hashtag);
    }

    /**
     * Get friends of main account (not list)
     *
     * @param  \App\Models\Twitter  $twitter
     * @return \Illuminate\Http\Response
     */
    public function getfriends(Request $request)
    {

        $site = Site::inst('SITEKEY');
        $isList = Site::inst('TWITTER_LIST_NAME_ARR');
        $twitterMain = Site::inst('TWITTER_MAIN');
        if ($isList) {
            echo "<p>error '$site' is a list site, not a main timeline site. Call /twitter/getlistmembers instead</p>";
            return;
        }
        $twitterObj = new TwitterBlvd();
        $twitterObj->saveFriends();
        if (!empty($request->redirect)) {
            Session::flash('success', "Added any twitter users $twitterMain was not already following.");
            return redirect()->route('socialmediaaccounts.admin', [
                'page' => 0,
                'sort' => 'new'
            ]);
        }

    }

}
