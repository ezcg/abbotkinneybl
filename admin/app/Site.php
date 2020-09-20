<?php

namespace App;

class Site
{

    # Must be filesystem frienldly, no spaces, all alphanumerics _ or -
    private static $SITEKEY;

    # Default to one level of categories; a category parent and multiple child main accounts beneath it
    private static $MAX_CATEGORY_LEVEL = 1;

    # number of items to list on admin main accounts page at a time
    private static $NUM_MAIN_ACCOUNT_ROWS_PER_PAGE;
    # number of social media cards for single item view (not category). eg. 5 tweets by thebrigvenice
    private static $NUM_SINGLE_ITEM_CARDS = 9;
    # create more context for google. replace ~searchterm~ with name to search
    private static $GOOGLE_SEARCH_TERM;
    private static $USES_WIKIPEDIA_SEARCH;
    private static $WIKIPEDIA_DISAMBIGUATE;
    private static $USES_HISTORY;
    private static $PRO_SPORTS;
    private static $TEAMS;
    private static $WRONG_LEAGUE;
    // In content card row for a category (eg. "shopping" or "Buccaneers") an item ("Happy Socks" or "Tom Brady")
    // can have this many cards in the row
    private static $NUM_ENTITIES_PER_ITEM;
    private static $USES_REDDIT;
    private static $USES_INSTAGRAM;
    private static $USES_YELP;
    private static $YELP_MIN_RATING = 3;
    private static $USES_TWITTER;
    private static $TWITTER_SCREENNAME;
    private static $TWITTER_MAIN;
    private static $TWITTER_LIST_ID_ARR;
    private static $TWITTER_LIST_NAME_ARR;
    private static $USES_CONTACT_INFO;
    private static $LOCATION;
    private static $USES_HOURS;
    private static $YELP_FIND_LOC;
    private static $MUST_HAVE_IMAGE;
    # just name as it appears as a folder in s3
    private static $AWS_BUCKET;
    # region included
    private static $AWS_RAW_BUCKET;
    # Where general assets resuable across all sites are stored
    private static $AWS_PRIMARY_BUCKET = '//s3.us-east-2.amazonaws.com/ezcg.com/';
    private static $MIN_REDDIT_UPVOTES = 5;
    private static $USES_HASHTAG_CATEGORIES = 0;

    private static $instance = null;

    private function __construct()
    {
    }

    public static function inst($val = '')
    {
        if (self::$instance == null)
        {
            self::$instance = new Site();
            self::$instance::init();
            // Add configs stored in db. The static properties must be set in this class first
            $r = \DB::table('configs')->select("*")->get();
            if ($r->count()) {
                $arr = $r->toArray();
                foreach($arr as $key => $obj) {
                    if ($obj->name == 'MAX_CATEGORY_LEVEL') {
                        static::$MAX_CATEGORY_LEVEL = $obj->value;
                    }
                }
            }
        }
        if (!empty($val)) {
            return self::$instance::${$val};
        } else {
            return self::$instance;
        }
    }

    private static function init() {

        $sitekey = config('app.sitekey');

        if ($sitekey == 'abbotkinneybl') {

            static::$SITEKEY='abbotkinneybl';
            # number of items to list on main accounts page at a time
            static::$NUM_MAIN_ACCOUNT_ROWS_PER_PAGE=4;
            # create more context for google. replace ~searchterm~ with name to search
            static::$GOOGLE_SEARCH_TERM="~searchterm~ abbot kinney 90291";
            static::$NUM_SINGLE_ITEM_CARDS=6;
            static::$USES_WIKIPEDIA_SEARCH=false;
            static::$WIKIPEDIA_DISAMBIGUATE="";
            static::$PRO_SPORTS=false;
            static::$NUM_ENTITIES_PER_ITEM=1;
            static::$USES_REDDIT=1;
            static::$USES_INSTAGRAM=1;
            static::$USES_CONTACT_INFO=1;
            static::$USES_YELP=1;
            static::$USES_TWITTER=1;
            static::$TWITTER_SCREENNAME="abbotkinneybl";
            static::$TWITTER_MAIN="abbotkinneybl";
            static::$TWITTER_LIST_ID_ARR="";
            static::$AWS_BUCKET='abbotkinneybl.ezcg.com';
            static::$AWS_RAW_BUCKET='//s3.us-east-2.amazonaws.com/abbotkinneybl.ezcg.com';
            static::$MIN_REDDIT_UPVOTES = 0;
            static::$USES_HOURS=1;
            static::$YELP_FIND_LOC=90291;
            static::$LOCATION="abbot kinney boulevard 90291";
            static::$USES_HASHTAG_CATEGORIES = 1;

        }

    }



}
