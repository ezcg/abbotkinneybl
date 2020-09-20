<?php

namespace App\Http\Controllers;

use App\Models\Items;
use App\Models\Cats;
use App\Models\SocialMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator, Input, Redirect, Session;


class SocialMediaController extends Controller
{

    public function __construct() {
        //$this->middleware('auth');
    }

    /**
     * Display social media by items_id or all
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //DB::enableQueryLog();

        $request->validate([
            'search' => 'nullable|min:3|max:255',
            'sort' => 'nullable',
            'items_id' => 'nullable|integer'
        ]);
        $title = $request->title;
        $search = $request->search;
        $sort = $request->sort;
        $searchCatsId = $request->cats_id;
        $itemsId = $request->items_id;
        $view = $request->view;
        $isHashtagCategory = Cats::isHashtagCategory($searchCatsId);
        $isHashtagItemsId = Items::isHashtagItemsId($itemsId);
        $isHashtag = $isHashtagCategory || $isHashtagItemsId;
        $hashtagCatsId = Cats::getHashtagCategoryId();

        if ($isHashtagItemsId) {

            $hashtag = Items::getItemsTitleWithItemsId($itemsId);

            $r = DB::table("social_media")
                ->select('social_media.*', 'items.id as items_id', 'items.title as title')
                ->join("items", "items.title", "=", "social_media.hashtag")
                ->leftJoin('social_media_accounts', 'social_media_accounts.source_user_id', '=', 'social_media.source_user_id')
                ->whereNull("social_media_accounts.source_user_id")
                ->where("social_media.hashtag", "=", $hashtag)
                ->where('social_media.deleted', "!=", 2)
                ->groupBy("social_media.source_id");


        } else if ($searchCatsId) {

            if ($isHashtagCategory) {
                $includeDeleted = $view == 'publishedhashtags' ? 0 : 1;
                $r = SocialMedia::getHashtagSocialMediaWithCatsId($searchCatsId, $includeDeleted);
            } else {
                $r = SocialMedia::getSocialMediaWithCatsId($searchCatsId);
            }
            if (!empty($search)) {
                $r->where("text", "like", "%$search%")
                    ->orWhere("social_media.username", "like", "%$search%");
            }

        } else {

            $r = DB::table("social_media")
                ->select('social_media.*', 'items.title', 'items.id as items_id')
                ->leftJoin('social_media_accounts', 'social_media_accounts.source_user_id', '=', 'social_media.source_user_id')
                ->leftJoin('items', 'items.id', '=', 'social_media_accounts.items_id')
                ->where("social_media_accounts.is_active", '=', 1)
                ->where("social_media.deleted", '!=', 2);
                //->groupBy("social_media.source_id");
            if (!empty($itemsId)) {
                $r->where("items.id", '=', $itemsId);
            } else if (!empty($search)) {
                $r->where("text", "like", "%$search%")
                  ->orWhere("social_media.username", "like", "%$search%");
            }

        }

        if ($sort == 'old') {
            $r = $r->orderBy('social_media.created_at', 'asc')->orderBy("social_media.id", "asc");
        } elseif ($sort == 'asc') {
            $r = $r->orderBy('social_media.username', 'asc');
        } elseif ($sort == 'desc') {
            $r = $r->orderBy('social_media.username', 'desc');
        } else {
            $r = $r->orderBy('social_media.created_at', 'desc')->orderBy("social_media.id", "desc");
        }

        $smColl = $r->paginate(5);
        $catsArr = Cats::getCatsIdAndTitleArr();

        if (($isHashtagItemsId || $isHashtagCategory) && $smColl->count()) {
            //$smColl = Cats::addItemsToHashtagCollection($smColl);
        }

        return view("socialmedia.index", compact(
            'smColl',
            'search',
            'sort',
            'view',
            'itemsId',
            'title',
            'catsArr',
            'searchCatsId',
            'isHashtag',
            'isHashtagItemsId',
            'isHashtagCategory',
            'hashtagCatsId'
        ));
    }

    public function deleteunpublishedhashtags(Request $request)
    {

        $r = DB::table("social_media")
            ->whereNotNull("hashtag")
            ->where("deleted", "<>", 0)
            ->update(["deleted" => 2]);
        Session::flash('success', 'Unpublished and hidden hashtag only social media deleted');
        return redirect()->route('socialmedia.index', [
                "cats_id" => $request->cats_id,
                "view" => "publishedhashtags"
            ]
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\SocialMedia  $socialMedia
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, SocialMedia $SocialMedia)
    {

        if ($request->action == 'hide' ) {
            $SocialMedia->deleted = 1;
            $msg = 'Social media set to hidden and it will only show up here. You may unhide it or permanently delete it.';
        } else if ($request->action == 'unhide') {
            $SocialMedia->deleted = 0;
            $msg = 'Social media unhidden and it will be visible everywhere. You may re-hide it or permanently delete it.';
        } else if ($request->action == 'deleteforreal') {
            //$SocialMedia->destroy($SocialMedia->id);
            $SocialMedia->deleted = 2;
            $msg = "The social media will no longer appear anywhere.";
        } else {
            $msg = "Did not recognize action: " . $request->action;
        }
        $SocialMedia->save();
        Session::flash('success', $msg);
        return redirect()->route('socialmedia.index', [
            "search" => $request->search,
            "page" => $request->page,
            "items_id" => $request->items_id,
            "title" => $request->title,
            "sort" => $request->sort,
            "cats_id" => $request->cats_id
            ]
        );
    }

}
