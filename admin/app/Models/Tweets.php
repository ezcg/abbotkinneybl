<?php

namespace App\Models;

use \Log;
use \DB;
use \App\Models\SocialMediaAccounts as SocialMediaAccounts;

class Tweets extends Feed
{
    // TODO remove 'site'
    protected $fillable = ['id', 'user_id', 'site', 'screen_name', 'text', 'urls', 'media', 'in_reply_to_status_id', 'in_reply_to_user_id', 'created_at', 'hashtag'];

    public $failedDomainsFile = "/tmp/faileddomains";

    /*
     * Some lists have members that are unwanted
     */
    public function removeIfSocialMediaAccountDoesNotExist($tweetsArr) {

        if (count($tweetsArr) == 0) {
            return $tweetsArr;
        }
        foreach($tweetsArr as $key => $obj) {
            $usernameArr[] = $obj->user->screen_name;
        }
        $dbUsernameArr = \DB::table("social_media_accounts")
            ->select("username")
            ->whereIn("username", $usernameArr)
            ->get()
            ->pluck('username');
        $newTweetsArr = [];
        foreach($dbUsernameArr as $username) {
            foreach($tweetsArr as $key => $obj) {
                if ($obj->user->screen_name == $username) {
                    $newTweetsArr[] = $obj;
                }
            }
        }

        return $newTweetsArr;

    }


    public function removeAlreadyAdded($tweetsArr) {

        if (count($tweetsArr) == 0) {
            return $tweetsArr;
        }
        foreach($tweetsArr as $key => $obj) {
            $idArr[] = $obj->id_str;
        }
        $dbIdArr = \DB::table("tweets")->select("id")->whereIn("id", $idArr)->get()->pluck('id');
        foreach($dbIdArr as $id) {
            foreach($tweetsArr as $key => $obj) {
                if ($obj->id_str == $id) {
                    unset($tweetsArr[$key]);
                }
            }
        }

        return $tweetsArr;

    }

    /*
     * Hashtags should have no social_media account associated with them
     */
    public function removeIfSocialMediaAccountExists($tweetsArr) {

        if (count($tweetsArr) == 0) {
            return $tweetsArr;
        }
        foreach($tweetsArr as $key => $obj) {
            $usernameArr[] = $obj->user->screen_name;
        }
        $dbUsernameArr = \DB::table("social_media_accounts")->select("id")->whereIn("username", $usernameArr)->get()->pluck('username');
        foreach($dbUsernameArr as $username) {
            foreach($tweetsArr as $key => $obj) {
                if ($obj->user->screen_name == $username) {
                    unset($tweetsArr[$key]);
                }
            }
        }

        return $tweetsArr;

    }

    public function updateTwitterAvatarsWithTweetsArr($tweetsArr) {

        if (empty($tweetsArr)) {
            return;
        }

        $setArr = [];
        foreach($tweetsArr as $tweetObj) {
            $sourceUserId = $tweetObj->user->id_str;
            if (in_array($sourceUserId, $setArr)) {
                continue;
            }
            $setArr[] = $sourceUserId;
            $avatar = \App\Models\Utility::removeHTTP($tweetObj->user->profile_image_url);
            SocialMediaAccounts::updateAvatarWithSourceUserIdAndSite($avatar, $sourceUserId, 'twitter.com');
        }

    }

    public function getFeed($id = '')
    {
        $paramArr = [
            'include_entities' => 1,
            'tweet_mode' => 'extended',
            'exclude_replies' => 1,
            'include_rts' =>1
        ];
        if ($id) {
            $paramArr['user_id'] = $id;
            $paramArr['count'] = 1;
        } else {

            // get max id from 'tweets' table (not 'social_media' table)
            // 'id' is actually a string type
//            $q = "SELECT IFNULL(MAX(CONVERT( id , SIGNED INTEGER)), 0) AS id FROM tweets WHERE hashtag IS NULL ORDER BY id DESC";
//            $r = DB::select($q, []);
//            if ($r[0]->id > 0) {
//                $paramArr['since_id'] = $r[0]->id;
//            }
            $paramArr['count'] = 200;
        }

        echo printR($paramArr) . "<br>";
        $r = \Twitter::getHomeTimeline($paramArr);
        echo "Tweets found: " . count($r) . "<br>";
        return $r;

    }

    public function getTweetById($id)
    {

        $q = "SELECT * FROM tweets WHERE id = ?";
        $r = \DB::select($q, [$id]);
        return $r;

    }

    public function saveFeed(array $tweetsArr, $hashtag = NULL)
    {

        // TODO create a dedicated prune endpoint
        // delete tweets over 30 days old
        \DB::table('tweets')->whereRaw("created_at < NOW() - interval 30 day")->delete();

        foreach($tweetsArr as $tweetObj) {
            $arr = $this->parseTweetObj($tweetObj);
            $arr = $this->removeIgnored($arr, $hashtag);
            if ($arr) {
                $arr['hashtag'] = $hashtag;
                $this->insertIntoTweets($arr);
            }
        }

        return $tweetsArr;

    }

    public function removeIgnored($arr, $hashtag) {

        // only set to ignore hashtags, so if it isn't a hashtag, don't look to ignore
        if (!$hashtag) {
            return $arr;
        }

        if (false == \App\Site::inst('USES_HASHTAG_CATEGORIES')) {
            return $arr;
        }
        $hashtagIgnoreArr = \DB::table('ignore_text')->select('*')->get()->toArray();
        if (!count($hashtagIgnoreArr)) {
            return $arr;
        }
        foreach($hashtagIgnoreArr as $i => $obj) {
            if (preg_match("~" . $obj->value . "~is", $arr['text'])) {
                \Log::warning(__METHOD__ . " line " . __LINE__ . " found '" . $obj->value . "' in " . $arr['text'] . " so removing.");
                return false;
            }
        }
        return $arr;
    }

    public function parseTweetObj($tweetObj) {

        $media = $this->getMediaJson($tweetObj);
        $media = !empty($media) && $media != '' ? $media : null;
        $urls = $this->getUrlsJson($tweetObj);
        $urls = !empty($urls) ? $urls : "";

        $retweetedText = '';
        if (!empty($tweetObj->retweeted_status->full_text)) {
//            $inResponseTo = '';
//            if (!empty($tweetObj->retweeted_status->quoted_status->full_text)) {
//                $inResponseTo = '"' . $tweetObj->retweeted_status->quoted_status->full_text . '"<br>';
//            }
//            $retweetedText = $inResponseTo . $tweetObj->retweeted_status->full_text;
            $retweetedText = $tweetObj->retweeted_status->full_text;
        } else if (!empty($tweetObj->retweeted_status->text)) {
            $retweetedText = $tweetObj->retweeted_status->text;
        }

        if (!empty($retweetedText)) {
            $text = $retweetedText;
            // Get the "RT @CNN:" part from original retweet so as to add it to the rewtweeted text
            if (!property_exists($tweetObj, 'full_text')) {
                \Log::error(__METHOD__ . " no 'full_text' property in tweetObj: " . json_encode($tweetObj));
            } else {
                // is this done to work around retweeted_status->text getting truncated? why not just use full_text and skip the above?
                preg_match("~RT @[^:]+:~", $tweetObj->full_text, $rtMatchArr);
            }
            if (isset($rtMatchArr[0])) {
                $text = $rtMatchArr[0] . " " . $text;
            }
            $urls = $this->getUrlsJson($tweetObj->retweeted_status);
            $urls = !empty($urls) ? $urls : "";
            $media = $this->getMediaJson($tweetObj->retweeted_status);

            // Links like this https://twitter.com/Mathieu_Era/status/1256313201379217408 bring user to retweeted text
            // without any reference to retweeter Mathieu_Era.
            // Link to the retweet like this https://twitter.com/Juan_Thornhill/status/1256310374023847936 brings user
            // to retweeted text with header 'Matheiu Retweeted'
            // Make a link to the later as the former is what the twitter icon links to which is confusing
// twitter is doing something internal for these links and I can't get the header
//            $link = "//twitter.com/" . $tweetObj->retweeted_status->user->screen_name . "/status/";
//            $link.=  $tweetObj->retweeted_status->id;
//            $href = "<a class='siteLink' href='" . $link . "' target='_blank'>RT</a>";
//            $text.= " " . $href;

        } else if (!empty($tweetObj->full_text)) {
            $text = $tweetObj->full_text;
        } else if (!empty($tweetObj->text)) {
            $text = $tweetObj->text;
        } else {
            \Log::warning(__METHOD__ . " tweet with no 'text' field found. " . json_encode($tweetObj));
            return false;
        }

        $quotedStatusObj = false;
        if (!empty($tweetObj->retweeted_status->quoted_status)) {
            $quotedStatusObj = $tweetObj->retweeted_status->quoted_status;
        } else if (!empty($tweetObj->quoted_status)) {
            $quotedStatusObj = $tweetObj->quoted_status;
        }

        if ($quotedStatusObj) {
            if (!empty($quotedStatusObj->full_text)) {
                $text.= " <a class='atLink' target='_blank' href='https://twitter.com/";
                $text.= $quotedStatusObj->user->screen_name;
                $text.="'>" . $quotedStatusObj->user->screen_name . "</a>: ";
                $text.= ' "' . $quotedStatusObj->full_text . '"';
            }
            $media2 = $this->getMediaJson($quotedStatusObj);
            $media2 = !empty($media2) ? $media2 : null;
            if (empty($media) && !empty($media2)) {
                $media = $media2;
            } else if (!empty($media) && !empty($media2)) {
                $mediaArr = (array)json_decode($media);
                $media2Arr = (array)json_decode($media2);
                $media = $mediaArr + $media2Arr;
                $media = json_encode($media);
            }
            $urls1 = $this->getUrlsJson($quotedStatusObj);
            $urls1 = !empty($urls1) ? $urls1 : "";
            if (empty($urls) && !empty($urls1)) {
                $urls = $urls1;
            } else if (!empty($urls) && !empty($urls1)) {
                $urlsArr = (array)json_decode($urls);
                $urls1Arr = (array)json_decode($urls1);
                $urls = $urlsArr + $urls1Arr;
                $urls = json_encode($urls);
            }
        }

        $arr = [
            'id' => $tweetObj->id,
            'user_id' => $tweetObj->user->id,
            'screen_name' => $tweetObj->user->screen_name,
            'text' => $text,
            'urls' => $urls,
            'media' => $media,
            'in_reply_to_status_id' => intval($tweetObj->in_reply_to_status_id),
            'in_reply_to_user_id' => intval($tweetObj->in_reply_to_user_id)
        ];

        return $arr;
    }

    public function insertIntoTweets(array $arr) {

        $date = date("Y-m-d H:i:s");
        $q = "INSERT INTO tweets (";
        $q.= implode(", ", array_keys($arr));
        $q.= ")";
        $q.= " VALUES ";
        $q.= "( :";
        $q.= implode(",:", array_keys($arr));
        $q.= ") ";
        $q.= "ON DUPLICATE KEY UPDATE updated_at = '" . $date . "'";
        $valArr = array_values($arr);
        $r = DB::insert($q, $valArr);
        return $r;
    }

    /*
     * Convert short urls to full, add hyperlinks to @ and #, convert smart quotes, etc
     */
    public function convertFeedToSocialMedia($hashtag = NULL)
    {

        $r = $this->getUnconvertedFeed($hashtag);
        foreach($r as $tweetDBObj) {
            $tweetDBObj->text = nl2br($tweetDBObj->text);
            $tweetDBObj->text = Utility::cleanText($tweetDBObj->text);
            $tweetDBObj->text = $this->cleanTweet($tweetDBObj->text);
            //$tweetDBObj->text = Utility::tighten($tweetDBObj->text);
            $tweetDBObj = $this->parseUrls($tweetDBObj);
            $tweetDBObj = $this->parseHashtags($tweetDBObj);
            $tweetDBObj = $this->parseAt($tweetDBObj);
            $tweetDBObj = $this->parseMedia($tweetDBObj);
            $tweetDBObj = $this->addMetaData($tweetDBObj);

            $this->saveConvertedFeedToSocialMedia([$tweetDBObj], $hashtag);

        }
        print "\n<br>\nSaved # unconverted tweets: " . count($r) . "\n<br>\n";

    }

    public function getUnconvertedFeed($hashtag = NULL)
    {
        $q = "SELECT tweets.* FROM tweets LEFT JOIN social_media ON social_media.source_id = tweets.id AND social_media.site = 'twitter.com' WHERE social_media.id is null AND tweets.hashtag ";
        if (is_null($hashtag)) {
            $q.= "IS NULL";
            $r = DB::select($q);
        } else {
            $q.= "= ? ";
            $r = DB::select($q, [$hashtag]);
        }

        return $r;
    }


    private function saveConvertedFeedToSocialMedia($objArr, $hashtag = NULL)
    {
        if (!count($objArr)) {
            Log::warning(__METHOD__ . " line " . __LINE__ . " objArr was empty and it should not have been");
            return;
        }
        foreach($objArr as $obj) {
            $smModel = new SocialMedia();
            $r = $smModel->where('source_id', $obj->id)->where('site', 'twitter.com')->get();
            $num = count($r);
            if ($num == 0) {
                $smModel->source_user_id = $obj->user_id;
                $smModel->source_id = $obj->id;
                $smModel->username = $obj->screen_name;
                $smModel->site = 'twitter.com';
                $smModel->link = 'https://twitter.com/' . $obj->screen_name . '/status/' . $obj->id;
                $smModel->text = $obj->text;
                $smModel->created_at = $obj->created_at;
                $smModel->hashtag = $hashtag;
                if ($hashtag) {
                    // This sets the social_media to be 'hidden' and require approval and unhiding it before it is public
                    // This is because social_media generated from hashtags will not have a pre-set social_media_account
                    // and could come from anybody
                    $smModel->deleted = 1;
                }
                $smModel->save();
            } else {
                Log::warning(__METHOD__ . " line " . __LINE__ . " twitter id " . $obj->id . " was found to be in social_media (source_id) when it should not have been");
            }
        }

    }

    // Call last non-twitter link posted and get title of page that the url points to and add it to card content text
    // if it is not already added
    private function addMetaData($tweetDBObj) {

        if (empty($tweetDBObj->urls)) {
            return $tweetDBObj;
        }

        $arr = array_values((array)json_decode($tweetDBObj->urls));
        $url = array_pop($arr);
        $disallowedDomainPattern = $this->getDisallowedDomainPattern();
        if ($url && !preg_match("~$disallowedDomainPattern~i", $url)) {
            try {
                //$str = file_get_contents($url, FALSE, NULL, 0, 20000);
                $str = \App\Models\Utility::curlGet($url);
            } catch(\Throwable $e) {
                $failedDomain = parse_url($url, PHP_URL_HOST);
                file_put_contents($this->failedDomainsFile, "|$failedDomain", FILE_APPEND);
                //Log::error(__METHOD__ . " " . $e->getMessage());
                return $tweetDBObj;
            }
            preg_match('~property[s]*=[s]*"og[s]*:[s]*title" content[s]*=[s]*"([^"]+)"~is', $str, $matches);
            if (isset($matches[1])) {
                $textFoundInPage = $matches[1];
                // Get rid of any encoded chars
                $textFoundInPage = preg_replace("~&#[^;]+;~", "", $textFoundInPage);
                //$textFound = preg_replace("~U+[0-9]+;~", "", $textFound);
                // Get rid of any non-ascii chars for simple comparison purposes
                $textFoundInPageReduced = preg_replace("~[^a-z0-9]+~is", "", $textFoundInPage);
                //$dbText = preg_replace("~<a[^>]+>[^<]+</a>~is", "", $tweetDBObj->text);
                $dbText = strip_tags($tweetDBObj->text);
                $dbTextReduced = preg_replace("~[^a-z0-9]+~is", "", $dbText);
                // if the text is not already in the card content text, add it.
                $dbTextReducedAndTruncated = strlen($dbTextReduced) > 10 ? substr($dbTextReduced, 10, 50) : $dbTextReduced;
                $textFoundInPageReducedAndTruncated = strlen($textFoundInPageReduced) > 10 ? substr($textFoundInPageReduced, 10, 50) : $textFoundInPageReduced;
                if ($dbTextReduced
                    && $textFoundInPageReduced
                    && $dbTextReducedAndTruncated
                    && $textFoundInPageReducedAndTruncated
                    && !stristr($dbTextReducedAndTruncated, $textFoundInPageReducedAndTruncated))
                {
                    $tweetDBObj->text.= " " . $textFoundInPage;
                }
            }

        }

        return $tweetDBObj;

    }

    // don't use file_get_contents on these domains
    private function getDisallowedDomainPattern() {
        $str = '';
        if (file_exists($this->failedDomainsFile)) {
            $str = file_get_contents($this->failedDomainsFile);
        }
        return 'twitter.com|.tv|youtube.com|instagram|patreon.com|nasa.gov|youtu.be|spotify.com|pottermore.com|insertsitename.com|bit.ly|vimeo.com' . $str;
    }

    public function debug($tweetDBObj) {
        $tweetDBObj->text = Utility::cleanText($tweetDBObj->text);
        $tweetDBObj->text = $this->cleanTweet($tweetDBObj->text);
        //$tweetDBObj->text = Utility::tighten($tweetDBObj->text);
        $tweetDBObj = $this->parseUrls($tweetDBObj);
        $tweetDBObj = $this->parseHashtags($tweetDBObj);
        $tweetDBObj = $this->parseAt($tweetDBObj);
        $tweetDBObj = $this->parseMedia($tweetDBObj);
        return $tweetDBObj;
    }

    private function cleanTweet($text)
    {

        $text = preg_replace("~newsmax: ~", "", $text);
        // https://t.co/y8... broken link
        $text = preg_replace("~http[^\s]+\.\.\.~", "", $text);
        $text = iconv("UTF-8", "UTF-8//IGNORE", $text);

        // custom characters show up as " or " " etc
        if (substr_count($text, '"') == 1) {
            $text = str_replace('"', '', $text );
        }
        $text = preg_replace("~'''~", " ", $text);
        $text = preg_replace("~''~", " ", $text);
        //$text = preg_replace("~^'~", "", $text);
        $text = preg_replace("~ ' ~", " ", $text);

        $text = trim($text);
        return $text;

    }

    /*
     * Replace the placeholder urls in the text property with the href and media url
     */
    private function parseMedia($tweetDBObj)
    {
        if (empty($tweetDBObj->media)) {
            return $tweetDBObj;
        }
        $mediaArr = json_decode($tweetDBObj->media);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::warning(__METHOD__ . " json error " . json_last_error() . " " . json_last_error_msg() . " " . Utility::varDumpToStr($tweetDBObj->media) );
            return $tweetDBObj;
        }
        //
        // '$mediaArr' will be in the format below. Replace the key, in this case, 'https://t.co/QrQrmWEpjR' with a clickable
        // link pointing to the media
        //
        //Array (
        //    [https://t.co/QrQrmWEpjR] => Array     (
        //        [expanded_url] => https://twitter.com/SIPerfumes/status/954836363114774528/photo/1
        //        [media_url] => http://pbs.twimg.com/media/DUBCm74VAAAQ6NL.jpg
        //    )
        //)
        $count = 0;
        foreach($mediaArr as $shortUrl => $obj) {
            $expandedUrl = $obj->expanded_url;
            $mediaUrl = \App\Models\Utility::removeHTTP($obj->media_url);
            $thumb = "<img src='" . $mediaUrl . ":thumb' class='socialMediaThumb'>";
            $replace = "<a class='imageThumbLink' target='_blank' href='$expandedUrl'>$thumb</a>";
            if ($count == 0) {
                $replace = "<a class='imageThumbLink firstImage' target='_blank' href='$expandedUrl'>$thumb</a>";
            }
            $tweetDBObj->text = str_replace($shortUrl, '', $tweetDBObj->text);
            $tweetDBObj->text = $replace . $tweetDBObj->text;
            $count++;
        }

        return $tweetDBObj;

    }

    private function parseUrls($obj)
    {
        if (empty($obj->urls)) {
            return $obj;
        }
        $urlsArr = json_decode($obj->urls);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::warning(__METHOD__ . " json error " . json_last_error() . " " . json_last_error_msg() . " " . print_r($obj->urls, 1));
            return $obj;
        }
        foreach($urlsArr as $short => $full) {
            $domain = parse_url($full, PHP_URL_HOST);
            // the link to the status is twitter icon in bottom right of card, no need to display the text link
            if ($domain == 'twitter.com') {
                $obj->text = str_replace($short, "", $obj->text);
                continue;
            }
            $domain = str_replace("www.", "", $domain);
            $obj->text = str_replace($short, "<a class='siteLink' target='_blank' href='$full'>$domain</a>", $obj->text);
        }
        return $obj;

    }

    private function parseHashtags($obj)
    {
        if (!strstr($obj->text, "#")) {
            return $obj;
        }

        // first, remove all hyperlinks so as not to match against a hyperlink that has an anchor tag. eg.
        // <a href='https://video.foxnews.com/v/6167156810001?playlist_id=930909787001#sp=show-clips'>video</a>
        $tmpText = strip_tags($obj->text);
        preg_match_all("~#([a-zA-Z0-9_])+~", $tmpText, $arr);
        if (!count($arr[0])) {
            return $obj;
        }

        // sort by length of values to ensure larger values get set first
        usort($arr[0], function($a, $b) {
            return strlen($b) <=> strlen($a);
        });

        // Do replacements in two steps so as to avoid smaller matches replacing larger matches.
        $placeholderArr = array();
        foreach($arr[0] as $key => $match) {
            // check if # hashtag is actually an anchor tag in a url. If is an anchor tag, skip it
            if (preg_match("!://[a-z0-9_0\.~=/\?-]+$match!i", $obj->text)) {
                //Log::info(__METHOD__ . ' #hashtag was found to be an anchor tag instead', ["matches:" => $arr[0], "text" => $obj->text]);
                continue;
            }

            $replaceWith = "<a class='hashtagLink' target='_blank' href='https://twitter.com/hashtag/" . str_replace("#", "", $match) . "'>" . $match . "</a>";
            $placeholderArr[] = $replaceWith;
            $placeholder = "!placeholder" . $key . "!";
            $obj->text = preg_replace("~" . $match . "~is", $placeholder, $obj->text);
        }
        foreach($placeholderArr as $key => $match) {
            $placeholderPattern = "!placeholder" . $key . "!";
            $obj->text = preg_replace("~" . $placeholderPattern . "~is", $match, $obj->text);
        }
        return $obj;

    }

    private function parseAt($obj)
    {

        if (!strstr($obj->text, "@")) {
            return $obj;
        }

        // medium.com uses the @ symbol in their urls, so don't match an @ preceded by a forward slash /
        // Sample text:
        // $obj->text="RT @BarackObama: Not many of us get to live to see our own legacy play out in such a meaningful, remarkable way. John Lewis did: <a class='siteLink' target='_blank' href='https://medium.com/@BarackObama/my-statement-on-the-passing'>medium.com</a>";
        // echo htmlspecialchars($obj->text) . "<br>";
        // lookbehind and look ahead works, but it will match the "RT @BarackObama:" and then subsequently replace the @barackobama in the
        // medium url, so using placeholders
        //preg_match("~(?<!/)@[a-zA-Z0-9_]+(?!/)~is", $obj->text, $arr);
        // Use placeholders to hold the position of the @ string in url and replace it later
        $hasAtSymbolInUrl = preg_match_all("~https?://[^@\s]+(@[^/]+)~is", $obj->text, $atSymbolPlaceholderArr);
        if ($hasAtSymbolInUrl) {
            foreach($atSymbolPlaceholderArr[0] as $key => $str) {
                $obj->text = str_replace($str, "~placeholder" . $key . "~", $obj->text);
            }
        }
        //echo printR($placeholderArr);
        //echo htmlspecialchars($obj->text);exit;

        preg_match_all("~@[a-zA-Z0-9_]+~", $obj->text, $arr);
        if (!isset($arr[0]) || !count($arr[0])) {
            return $obj;
        }

        // sort by length of values to ensure larger values get set first
        usort($arr[0], function($a, $b) {
            return strlen($b) <=> strlen($a);
        });

        // Do replacements in two steps so as to avoid smaller matches replacing larger matches.
        // eg. [3] => @mainblvd [4] => @mainbl
        $placeholderArr = array();
        foreach($arr[0] as $key => $match) {
            $replaceWith = "<a class='atLink' target='_blank' href='https://twitter.com/" . str_replace("@", "", $match) . "'>$match</a>";
            $placeholderArr[] = $replaceWith;
            $obj->text  = preg_replace("~" . $match . "~is", '!placeholder' . $key . '!', $obj->text );
        }
        foreach($placeholderArr as $key => $match) {
            $placeholderPattern = '!placeholder' . $key . '!';
            $obj->text  = preg_replace("~" . $placeholderPattern . "~", $match, $obj->text );
        }

        if ($hasAtSymbolInUrl) {
            foreach($atSymbolPlaceholderArr[0] as $key => $str) {
                $obj->text = str_replace("~placeholder" . $key . "~", $str, $obj->text);
            }
        }

        return $obj;
    }

    private function getMediaJson($tweetObj)
    {

        if (empty($tweetObj->entities->media)) {
            return;
        }
        $mediaArr = [];
        foreach($tweetObj->entities->media as $mediaObj) {
            if (property_exists($mediaObj, 'expanded_url')) {
                $mediaArr[$mediaObj->url] = array(
                    'expanded_url' => $mediaObj->expanded_url,
                    'media_url' => $mediaObj->media_url
                );
            }
        }
        $mediaJson = json_encode($mediaArr);

        return $mediaJson;

    }

    private function getUrlsJson($tweetObj)
    {

        if (empty($tweetObj->entities->urls)) {
            return '';
        }
        $urlsArr = [];
        foreach($tweetObj->entities->urls as $urlObj) {
            if (property_exists($urlObj, 'expanded_url')) {
                $urlsArr[$urlObj->url] = $urlObj->expanded_url;
            }
        }
        $urlsJson = json_encode($urlsArr);

        return $urlsJson;
    }


};
