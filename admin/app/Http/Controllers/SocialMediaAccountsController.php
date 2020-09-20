<?php

namespace App\Http\Controllers;

use App\Models\Items;
use App\Models\SocialMediaAccounts;
use App\Models\SocialMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator, Input, Redirect, Session;


class SocialMediaAccountsController extends Controller
{

    public function __construct() {
        //$this->middleware('auth');
    }

    /**
     * Add a social_media_account form
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $itemsId = $request->items_id;
        $title = $request->title;
        $site = $request->site;
        $username = $request->username;
        $usesTwitter = \App\Site::inst('USES_TWITTER');
        if ($usesTwitter) {
            $twitterAvatar = SocialMediaAccounts::select("avatar")
                ->where("items_id", $itemsId)
                ->where("site", "twitter.com")
                ->pluck("avatar");
        }
        $usesYelp = \App\Site::inst('USES_YELP');
        $yelpFindLoc = \App\Site::inst('YELP_FIND_LOC');
        return view("socialmediaaccounts.create", compact(
            'itemsId',
            'title',
            'username',
            'usesTwitter',
            'usesYelp',
            'yelpFindLoc',
            'site'
        ));
    }

    /**
     * Save a social media account and associate it with an item
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $request->validate([
            'username' => "required|min:3|max:60"
        ]);

        if (FALSE && $request->site != 'twitter.com' && $request->site != 'yelp.com' && empty($request->avatar)) {
            $msg = "Non-Twitter and non-Yelp accounts require a url to the avatar for the account.";
            Session::flash('success', $msg);
            return redirect()->route('socialmediaaccounts.create', array(
                'items_id' => $request->items_id,
                'title' => $request->title,
                'username' => $request->username,
                'site' => $request->site
            ));
        }

        // Make sure username is unique for the site. May have same username on multiple platforms
        $r = \App\Models\SocialMediaAccounts::getSocialMediaAccountWithUsernameAndSite($request->username, $request->site);
        if ($r->count()) {
            $msg = "'" . $request->username . "' on " . $request->site . " platform has already been added.";
            Session::flash('error', $msg);
            return redirect()->route('socialmediaaccounts.create', array(
                'itemsId' => $request->items_id,
                'title' => $request->title,
                'site' => $request->site
            ));
        }

        // if twitter get source_user_id from twitter
        if ($request->site == 'twitter.com') {
            try {
                $r = \Twitter::getUsersLookup(['screen_name' => $request->username]);
                $source_user_id = $r[0]->id_str;
                $avatar = $r[0]->profile_image_url;
            } catch(\Exception $e) {
                $msg = "'" . $request->username . "' on twitter.com was not found. Double check your spelling and verify that the account is on twitter.";
                Session::flash('error', $msg);
                return redirect()->route('socialmediaaccounts.create', array(
                    'items_id' => $request->items_id,
                    'title' => $request->title,
                    'username' => $request->username,
                    'site' => $request->site
                ));
            }
        } else {
            $source_user_id = $request->username;
            $avatar = $request->avatar;
        }

        $isActive = !empty($request->is_active) ? 1 : 0;
        $arr = [
            'source_user_id' => $source_user_id,
            'username' => $request->username,
            'site' => $request->site,
            'is_active' => $isActive,
            'is_primary' => 0,
            'use_avatar' => 0,
            'avatar' => $avatar,
            'items_id' => $request->items_id,
            'name' => $request->username
        ];

        SocialMediaAccounts::create($arr);
        Session::flash('success', 'Successfully added!');

        return redirect()->route('socialmediaaccounts.admin', array(
            'items_id' => $request->items_id,
            'title' => $request->title
        ));

    }


    /**
     * Update social_media_account for item
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SocialMediaAccounts $SocialMediaAccounts)
    {

        $SocialMediaAccounts->avatar = $request->avatar;
        $SocialMediaAccounts->save();
        return ['success' => true, 'sma' => $SocialMediaAccounts];

    }

    public function updatecolumn(Request $request, SocialMediaAccounts $SocialMediaAccounts) {

        // results in an undefined variable 'is_active' error
        // $SocialMediaAccounts->${$request->name} = $request->value;
        // so doing this...
        if ($request->name == "is_active") {
            $SocialMediaAccounts->is_active = $request->value;
        } else if ($request->name == "is_primary") {
            $SocialMediaAccounts->is_primary = $request->value;
        } else if ($request->name == "use_avatar") {
            $SocialMediaAccounts->use_avatar = $request->value;
        }
        $SocialMediaAccounts->save();
        return ['success' => true, 'sma' => $SocialMediaAccounts];
    }

    /**
     * Delete social media account and all related media in social media tables
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\SocialMediaAccounts  $socialMediaAccounts
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, SocialMediaAccounts $SocialMediaAccounts)
    {

        $SocialMediaAccounts->where('id', $SocialMediaAccounts->id)->delete();
        SocialMedia::where('source_user_id', $SocialMediaAccounts->source_user_id)
            ->where("site", $SocialMediaAccounts->site)
            ->delete();
        if ($SocialMediaAccounts->site == 'twitter.com') {
            \App\Models\Tweets::where("user_id", $SocialMediaAccounts->source_user_id)->delete();
        } else if ($SocialMediaAccounts->site == 'yelp.com') {
            \App\Models\Yelp::where("biz_id", $SocialMediaAccounts->source_user_id)->delete();
        } else if ($SocialMediaAccounts->site == 'reddit.com') {
            \App\Models\Reddit::where("subreddit", $SocialMediaAccounts->source_user_id)->delete();
        }
        if (!empty($request->redirect_to) && $request->redirect_to == 'admin') {
            return redirect()->route('socialmediaaccounts.admin', [
                'search' => $request->search,
                'page' => $request->page,
                'sort' => $request->sort,
                'view' => $request->view,
                'items_id' => $request->items_id,
                'title' => $request->title
            ]);
        } else {
            return redirect()->route('socialmediaaccounts.edit', [
                'items_id' => $request->items_id,
                'title' => $request->title
            ]);
        }

    }

   /**
    * Update submitted social_media_account with submitted items_id
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  \App\SocialMediaAccounts  $socialMediaAccounts
    * @return \Illuminate\Http\Response
    */
    public function assoc(Request $request, SocialMediaAccounts $SocialMediaAccounts)
    {

        $SocialMediaAccounts->items_id = $request->items_id;
        SocialMediaAccounts::updateSocialMediaAccountStatus($SocialMediaAccounts);
        if (empty($request->redirect_disabled)) {
            return redirect()->route('socialmediaaccounts.edit', [
                'items_id' => $request->items_id,
                'title' => $request->title
            ]);
        } else {
            return ['success' => true, 'sma' => $SocialMediaAccounts];
        }

    }

    public function admin(Request $request)
    {

        $request->validate([
            'search' => 'nullable|min:3|max:255',
            'sort' => 'nullable'
        ]);
        $search = $request->search;
        $sort = $request->sort;
        $itemsId = $request->items_id;
        $title = $request->title;

        $r = DB::table("social_media_accounts")
            ->selectRaw('count(*) as num_deactivated')
            ->where('is_active', '=', 0)->get();
        $numDeactivated = $r[0]->num_deactivated;

        $r = DB::table("social_media_accounts")
            ->selectRaw('count(*) as num_associated')
            ->leftJoin('items', 'items.id', '=', 'social_media_accounts.items_id')
            ->where('is_active', '=', 1)
            ->whereNotNull('items.id')->get();
        $numAssociated = $r[0]->num_associated;

        $r = DB::table("social_media_accounts")
               ->select('social_media_accounts.id AS sma_id', 'social_media_accounts.*', 'items.*')
               ->leftJoin('items', 'items.id', '=', 'social_media_accounts.items_id')
               ->whereNull('items.id');
        // Get number of social_media_accounts that are unassociated
        // If view is 'unassociated', then operate on $r below
        $numUnassociated = $r->count();

        if ($request->view == 'deactivated') {
            $r = DB::table("social_media_accounts")
                ->select('social_media_accounts.id AS sma_id', 'social_media_accounts.*', 'items.*')
                ->leftJoin('items', 'items.id', '=', 'social_media_accounts.items_id')
                ->where('is_active', '=', 0);
            $smaColl = $r->paginate(8);
        } else if ($request->view == 'unassociated') {
            $smaColl = $r->paginate(8);
        } elseif ($request->view != 'unassociated') {

            $r = DB::table("social_media_accounts")
                ->select('social_media_accounts.id AS sma_id', 'social_media_accounts.*', 'items.*')
                ->leftJoin('items', 'items.id', '=', 'social_media_accounts.items_id');
                //->where('is_active', '=', 1);
            if (!empty($search)) {
                $r->where("social_media_accounts.username", "like", "%$search%")
                    ->orWhere("social_media_accounts.name", "like", "%$search%");
            } else if (!empty($itemsId)) {
                $r->where("social_media_accounts.items_id", "=", $itemsId);
            } else {
                $r->where('is_active', '=', 1);
            }
            if (empty($sort)) {
                $r->orderBy('social_media_accounts.items_id', 'desc');
            } else if ($sort == 'old') {
                $r->orderBy('social_media_accounts.created_at', 'asc');
            } elseif ($sort == 'asc') {
                $r->orderBy('social_media_accounts.username', 'asc');
            } elseif ($sort == 'desc') {
                $r->orderBy('username', 'desc');
            } else {
                $r->orderBy('social_media_accounts.created_at', 'desc');
            }

            $smaColl = $r->paginate(8);
        }
        $view= !empty($request->view) ? $request->view : '';

        $twitterListNamesStr = '';
        $twitterListNameArr = explode(",", \App\Site::inst('TWITTER_LIST_NAME_ARR'));
        foreach($twitterListNameArr as $listName) {
            $twitterListNamesStr.= $listName . ", ";
        }
        $twitterListNamesStr = substr($twitterListNamesStr, 0, -2);
        $twitterMain = \App\Site::inst('TWITTER_MAIN');

        // Get all items that have no account for each social media type
        //if (env('USES_TWITTER')) {
        //$itemsWithoutSMAArr = array();
            //$arr = SocialMediaAccounts::getItemsThatDoNotHaveSocialMediaAccount()->toArray();
            //if (count($arr)) {
                //$itemsWithoutSMAArr = array_column($arr, 'title', 'id');
                //$itemsWithoutSMAArr[0] = "Associate with this item:";
            //}
            // TODO foreach sma in this pagination, find best matches against items and duplicate matches to the top and keep alpha order below
        //}

        $itemsArr = array();
        // only offer drop down of Main Accounts to associate with if there are 50 or less main accounts otherwise the drop down
        // gets too cumbersome
        if ($numUnassociated <= 50) {
            $itemsArr = DB::table("items")->select("id", "title")->orderBy("title", "desc")->get()->toArray();
            if (count($itemsArr)) {
                $itemsArr    = array_column($itemsArr, 'title', 'id');
                $itemsArr[0] = "Associate with this Main Account:";
            }
        }

        $awsPrimaryBucket = \App\Site::inst('AWS_PRIMARY_BUCKET');

        return view("socialmediaaccounts.admin", compact(
            'itemsId',
            'itemsArr',
            'smaColl',
            'search',
            'sort',
            'twitterListNamesStr',
            'twitterMain',
            'view',
            'numUnassociated',
            'numAssociated',
            'numDeactivated',
            'title',
            'awsPrimaryBucket'
        ));

    }

    // Get all social media accounts unassociated with an item, create an item based on sma and associate it
    // with that sma
    public function assocAll(Request $request)
    {

        $numAdded = 0;
        $numAlreadyInItemsTable = 0;
        $namesAlreadyInItemsTable = '';
        $smaColl = SocialMediaAccounts::getSocialMediaAccountsThatDoNotHaveItems();
        foreach ($smaColl as $smaObj) {
            $itemsObj = new Items;
            $name = !empty($smaObj->name) ? $smaObj->name : $smaObj->username;
            $itemsId = $itemsObj->where("title", $name)->orWhere("title", $smaObj->username)->value('id');
            if ($itemsId) {
                // a row is in items table with that name already
                $numAlreadyInItemsTable++;
                $namesAlreadyInItemsTable .= $name . " or " . $smaObj->username .", ";
                continue;
            }
            $itemsObj->title = $name;
            $itemsObj->description = $smaObj->description;

            $itemsObj->save();
            $smaModel = new SocialMediaAccounts();
            $smaModel->items_id = $itemsObj->id;
            $smaModel->source_user_id = $smaObj->source_user_id;
            SocialMediaAccounts::updateSocialMediaAccountStatus($smaModel);
            $numAdded++;
        }

        $msg = '';
        if ($numAdded) {
            $msg = "Successfully created $numAdded Main Accounts from social media accounts! View them in the Main Accounts section via the 'Uncategorized' link found there.";
        }
        $qArr = array();
        if ($numAlreadyInItemsTable) {
            $namesAlreadyInItemsTable = substr($namesAlreadyInItemsTable,0, -2);
            $msg.= " $numAlreadyInItemsTable Main Accounts already had the same name or username as the social media account, so no new ";
            $msg.= "Main Account was created for those items and they must be manually associated: $namesAlreadyInItemsTable ";
            $msg.="View unassociated below.";
            $qArr = array("view" => "unassociated");
        }
        Session::flash('success', $msg);
        return redirect()->route('socialmediaaccounts.admin', $qArr);

    }

}
