<?php

namespace App\Http\Controllers;
use Auth;

use App\Models\Items;
use App\Models\ItemsCats;
use App\Models\SocialMediaAccounts;
use App\Models\Cats;
use App\Models\WikipediaBlvd as WikipediaBlvd;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator, Input, Redirect, Session;

class ItemsController extends Controller
{

    public function __construct() {
        //$this->middleware('auth');
    }

    /**
     * Main page for displaying item name, desc, categories and links to editing item related info
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $searchCatsId = 0;
        if (!empty($request->cats_id)) {
            $searchCatsId = $request->cats_id;
        }

        $request->validate([
            'search' => 'nullable|min:3|max:255',
            'cats_id' => 'nullable|integer',
            'sort' => 'nullable'
        ]);
        $search = $request->search;
        $sort = empty($request->sort) ? "asc" : $request->sort;
        //DB::enableQueryLog();

        $q = "SELECT count(*) as num_deactivated FROM items WHERE items.deactivated = 1";
        $r = \DB::select($q, []);
        $numDeactivated = $r[0]->num_deactivated;

        $q = "SELECT count(*) as num_activated FROM items WHERE items.deactivated = 0";
        $r = \DB::select($q, []);
        $numActivated = $r[0]->num_activated;

        $q = "SELECT items.id as items_id 
                FROM items 
                LEFT JOIN items_cats ON items.id = items_cats.items_id 
                WHERE items_id IS NULL AND items.deactivated = 0 
                ORDER BY ";
        if ($sort == 'old') {
            $q.= "items.created_at asc";
        } elseif ($sort == 'asc') {
            $q.= "items.title asc";
        } elseif ($sort == 'desc') {
            $q.= "items.title desc";
        } else {
            $q.= "items.created_at desc";
        }
        $r = \DB::select($q, []);
        $itemsIdArr = array_column($r, 'items_id');
        $numUncategorized = count($itemsIdArr);

        $itemsModel = new Items();
        if ($request->view == 'uncategorized') {

            $itemsModel = $itemsModel->whereIn('id', $itemsIdArr);

        } elseif ($request->view == 'deactivated') {

            $itemsModel = $itemsModel->where("deactivated", "=", 1);

        } elseif (!empty($request->cats_id)) {

            // Get all the child cats_ids of the requested cats_id
            $paramArr = [$request->cats_id, $request->cats_id];
            $q = "SELECT child_id as cats_id FROM cats_p_and_c WHERE child_id = ? OR parent_id = ?";
            $r = \DB::select($q, $paramArr);
            $catsIdArr = array_column($r, 'cats_id');
//            $paramArr = $catsIdArr;
//            if (count($paramArr)) {
//                do {
//                    $q = "SELECT child_id as cats_id FROM cats_p_and_c WHERE parent_id IN (";
//                    $q.= implode( ", ", array_fill(0, count($paramArr), "?" ));
//                    $q.= ")";
//                    $r = \DB::select($q, $paramArr);
//                    $tmpCatsIdArr = array_column($r, 'cats_id');
//                    $catsIdArr = array_merge($catsIdArr, $tmpCatsIdArr);
//                    $paramArr = $tmpCatsIdArr;
//                } while (count($paramArr));
//            }

            $q = "SELECT items_id FROM items_cats WHERE cats_id IN (";
            $q.= implode( ", ", array_fill(0, count($catsIdArr), "?" ));
            $q.=")";
            $r = \DB::select($q, $catsIdArr);
            $itemsIdArr = array_column($r, 'items_id');
            $itemsModel = $itemsModel->whereIn('id', $itemsIdArr);
            //$itemsModel->whereIn('id', $itemsIdArr);
        }
        if (!empty($search)) {
            $itemsModel = $itemsModel->where("items.title", "like", "%" . $search . "%");
            //$itemsModel->where("items.title", "like", "%" . $search . "%");
        } else if (!empty($request->items_id)) {
            // if a single items_id was passed in via the request object, only get that item
            $itemsModel = $itemsModel->whereIn('id', [$request->items_id]);
            //$itemsModel->whereIn('id', [$request->items_id]);
        } else if ($request->view != 'deactivated') {
            $itemsModel = $itemsModel->where("deactivated", "=", 0);
        }
        if ($sort == 'old') {
            $itemsModel = $itemsModel->orderBy('items.created_at', 'asc');
        } elseif ($sort == 'asc') {
            $itemsModel = $itemsModel->orderBy('items.title', 'asc');
        } elseif ($sort == 'desc') {
            $itemsModel = $itemsModel->orderBy('items.title', 'desc');
        } else {
            $itemsModel = $itemsModel->orderBy('items.created_at', 'desc');
        }
        if ($request->view == 'uncategorized') {
            $itemsColl = $itemsModel->paginate(50);
        } else {
            $perPage = \App\Site::inst('NUM_MAIN_ACCOUNT_ROWS_PER_PAGE') ? \App\Site::inst('NUM_MAIN_ACCOUNT_ROWS_PER_PAGE') : 4;
            $itemsColl = $itemsModel->paginate($perPage);
        }
        //dd(DB::getQueryLog());
        $itemsCatsObj = new ItemsCats();
        $itemsCatsColl = $itemsCatsObj->getItemsCats($itemsColl);

        // Get sma associated with item
        foreach($itemsColl as $itemsObj) {
            $sma = \App\Models\SocialMediaAccounts::getSocialMediaAccountsWithItemsId($itemsObj->id);
            $itemsObj->smaArr = $sma;
        }

        // Get wikipedia associated with item
        // Currently only used to determine type of link to display
        foreach($itemsColl as $itemsObj) {
            $w = \App\Models\WikipediaBlvd::where("items_id", "=", $itemsObj->id)->get()->first();
            if ($w && $w->count()) {
                // get 'name' from url eg. https://en.wikipedia.org/wiki/Ben_Smith
                $arr = explode("/", $w['url']);
                $urlName = array_pop($arr);
                $itemsObj->wikipediaArr = array('urlName' => $urlName, 'url' => $w['url']);
            } else {
                $itemsObj->wikipediaArr = [];
            }
        }

        // This is for the dropdown to filter by category
        $catsArr = \App\Models\Cats::getCatsIdAndTitleArr();

        $catsObj = new Cats();
        $catsCollArr = $catsObj->pluck('title', 'id')->all();

        // If it is a multidimensional category site, only the lowest level category may be associated with a Main Account aka item
        // eg. NFL East -> NY Giants -> Peyton Manning ... "Peyton Manning" would be at the max category level of 3
        if (\App\Site::inst('MAX_CATEGORY_LEVEL') > 1) {
           $itemsLevel = \App\Site::inst('MAX_CATEGORY_LEVEL');
           $itemsLevelCatsArr = DB::table('cats')
               ->where("level", "=", $itemsLevel)
               ->orderBy("title", "asc")
               ->pluck('title', 'id')
               ->toArray();
        } else {
            $itemsLevelCatsArr = $catsArr;
        }

        $search = $request->search;
        $sort = !empty($request->sort) ? $request->sort : 'asc';
        $view = $request->view;

        $usesContactInfo = \App\Site::inst('USES_CONTACT_INFO');
        $googleSearchTerm = \App\Site::inst('GOOGLE_SEARCH_TERM');
        $usesWikipediaSearch = \App\Site::inst('USES_WIKIPEDIA_SEARCH');
        $usesHours = \App\Site::inst('USES_HOURS');

        return view(
            'items.index',
            compact(
                'itemsColl',
                'sort',
                'search',
                'catsArr',
                'itemsCatsColl',
                'itemsLevelCatsArr',
                'catsCollArr',
                'searchCatsId',
                'view',
                'numUncategorized',
                'numDeactivated',
                'numActivated',
                'usesContactInfo',
                'googleSearchTerm',
                'usesWikipediaSearch',
                'usesHours'
            )
        );
    }

    /**
     * Save a new item
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $request->validate([
            'title' => "required|min:3|unique:items|max:30",
            'description' => 'nullable'
        ]);
        $itemsObj = new Items;
        $itemsObj->title = str_replace("~", "", $request->title);
        $itemsObj->description = !empty($request->description) ? $request->description : '';
        $itemsObj->save();
        if (!empty($request->sma_id)) {
            SocialMediaAccounts::updateSocialMediaAccountsItemsIdWithId($itemsObj->id, $request->sma_id);
            Session::flash('success', 'Successfully added ' . $request->title . " as a Main Account.");
            return redirect()->route('socialmediaaccounts.admin', ['page' => $request->page, 'search' => $request->search, 'view' => $request->view]);
        }

        return redirect(route('items.index'));
    }

    /**
     * Update an item's single category
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Items  $items
     * @return \Illuminate\Http\Response
     */
    public function updatecolumn(Request $request, Items $items) {

        $action = $request->action;
        $catsId = $request->cats_id;
        $itemsId = $items->id;
        $success = false;
        if ($action == 'delete') {
            \DB::table('items_cats')->where('cats_id', '=', $catsId)->where('items_id', '=', $itemsId)->delete();
            $success = true;
        } else if ($action == 'add') {
            $itemsCatsObj = new ItemsCats;
            $itemsCatsObj->items_id = $itemsId;
            $itemsCatsObj->cats_id = $catsId;
            $itemsCatsObj->save();
            $success = true;
        }
        return ['success' => $success, 'items' => $items];
    }

    /**
     * Update an item
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Items  $items
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Items $items)
    {

        $uniqueTitleValidation = '';
        if (trim(strtolower($request->title_old)) != trim(strtolower($request->title))) {
            $uniqueTitleValidation = '|unique:items';
        }
        $request->validate([
            'title' => "required|min:3|max:30" . $uniqueTitleValidation,
            'description' => 'nullable'
        ]);

        $items->title = $request->title;
        $items->description = $request->description;
        $items->update();

        return ["success" => true];

////////
//////// LEGACY
////////

        $page = $request->on_page;
        $searchCatsId = 0;
        if (!empty($request->search_cats_id)) {
            $searchCatsId = $request->search_cats_id;
        }
        if (empty($page)) {
            $arr = array();
        } else {
            $arr = ['page' => $page];
        }
        $arr['cats_id'] = $searchCatsId;
        $arr['view'] = $request->view;
        if (!empty($request->search)) {
            $arr['search'] = ($request->search);
        }
        // If viewing single item, redirect back to single item view
        if (!empty($request->items_id)) {
            $arr['items_id'] = ($request->items_id);
        }
/*
        // update categories
        $request->validate([
            'catsIdArr.*' => 'nullable|integer'
        ]);
        $catsIdArr = $request->catsIdArr;

        // Associate an items_id with a cats_id in the items_cats join table
        // Delete existing cats for item
        ItemsCats::where('items_id', $itemsId)->delete();
        if (is_array($catsIdArr) && count($catsIdArr)) {
            // add submitted cats
            foreach($catsIdArr as $catsId) {
                $itemsCatsObj = new ItemsCats;
                $itemsCatsObj->items_id = $itemsId;
                $itemsCatsObj->cats_id = $catsId;
                $itemsCatsObj->save();
            }
        }
*/
        Session::flash('success', 'Successfully updated!');
        return redirect()->route('items.index', $arr);
    }

    /*
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  \App\Items  $items
    * @return \Illuminate\Http\Response
    */
    public function updatedeactivated(Request $request, Items $items) {
        $items->deactivated = !empty($request->deactivate) ? 1 : 0;
        $items->update();
        return ["success" => true];
    }

    /**
     * Delete an item and all the rows in related tables for that items_id
     *
     * @param  \App\Items  $items
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Items $items)
    {

        $smObj = new \App\Models\SocialMedia;
        // Get social media ids related to items_id
        $smaObj = new SocialMediaAccounts;
        $smaColl = $smaObj->where('items_id', '=', $items->id)->get();
        // delete social media
        foreach($smaColl as $key => $sma) {
            $smObj->where("username", '=', $sma->username)
                    ->where('site', '=', $sma->site)
                    ->delete();
        }
        // delete social media accounts
        $smaObj->where('items_id', '=', $items->id)->delete();
        // delete contact info
        \App\Models\ContactInfo::where('items_id', $items->id)->delete();
        // delete cats
        ItemsCats::where('items_id', $items->id)->delete();

        $items->delete();
        $searchCatsId = 0;
        $page = $request->page;
        if (!empty($request->cats_id)) {
            $searchCatsId = $request->cats_id;
        }
        Session::flash('success', 'Successfully deleted!');
        return redirect()->route('items.index', [
            'page' => $page,
            'cats_id' => $searchCatsId,
            'view' => $request->view,
            'search' => $request->search,
            'sort' => $request->sort
        ]);
    }

}
