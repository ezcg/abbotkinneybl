<?php

namespace App\Http\Controllers;
use App\Models\AWSS3;
use App\Models\Items;
use Auth;
Use App\Site;
use Input;
use App\Models\Cats;
use App\Models\ItemsCats;
use App\Models\CatsPandC;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator, Redirect, Session;
use Intervention\Image\ImageManager as Image;

class CatsController extends Controller
{

    private $depthArr = [1,2,3,4,5];// Possible category depths

    public function __construct() {
        //$this->middleware('auth');
    }

    // If switching between category depths, additional updates need to happen to other data as well, so manage it
    // via an interface
    public function depth()
    {

        $r = \DB::table('cats')->select('*')->where('level', '>', 0)->get();
        $hasMultiLevelInCatsTable = $r->count();
        $depthArr = $this->depthArr;
        $depthObj = \DB::table('configs')->select('*')->where('name', '=', 'MAX_CATEGORY_LEVEL')->first();
        return view('cats.depth', compact(
            'depthObj',
            'depthArr',
            'hasMultiLevelInCatsTable'
        ));

    }

    public function updatedepth(Request $request)
    {
        $max = $this->depthArr[count($this->depthArr) - 1];
        $request->validate([
            'depth' => 'nullable|min:1|max:' . $max
        ]);
        $name = 'MAX_CATEGORY_LEVEL';
        $value = $request->depth;
        $r = \DB::table('configs')->select("*")->where('name', '=', $name)->get();
        if ($r->count()) {
            Session::flash('success', 'Updated!');
            \DB::table('configs')->where('name', '=', $name)->update(['value' => $value]);
        } else {
            $r = \DB::table('configs')->insert(['value' => $value, 'name' => $name]);
            Session::flash('success', 'Added!');
        }

        // set to 0 any levels in cats table that are greater than the current level of depth submitted
        \DB::table('cats')->where('level', '>', $value)->update(['level' => 0]);

        return redirect(route('cats.depth'));

    }

    // Allow for Main Accounts to be a hashtag and have no dedicated Social Media Accounts associated with the Main Account.
    public function hashtags()
    {
        $usesHashtagCategories = \App\Site::inst('USES_HASHTAG_CATEGORIES');
        $hashtagCategoryArr = \App\Models\Cats::getHashtagCategory();
        $hashtagItemsArr = \App\Models\Items::getHashtagItems();
        $hashtagIgnoreArr = \DB::table('ignore_text')->select('*')->get()->toArray();
        return view('cats.hashtags', compact(
            'usesHashtagCategories',
            'hashtagCategoryArr',
            'hashtagItemsArr',
            'hashtagIgnoreArr'
        ));
    }

    /*
    * @param  \Illuminate\Http\Request  $request
    */
    public function hashtagsignore(Request $request)
    {

        $value = $request->value;
        $r = \DB::table('ignore_text')->select("*")->where('value', '=', $value)->get();
        if ($r->count()) {
            Session::flash('success', 'That text is already added:' . $value);
            return redirect(route('cats.hashtags'));
        }
        $r = \DB::table('ignore_text')->insert(['value' => $value]);
        Session::flash('success', 'Added:' . $value);
        return redirect(route('cats.hashtags'));
    }


    /*
    * @param  \Illuminate\Http\Request  $request
    */
    public function hashtagsignoredelete(Request $request)
    {

        $value = $request->value;
        $r = \DB::table('ignore_text')->where('value', '=', $value)->delete();
        if ($r) {
            Session::flash('success', 'Deleted:' . $value);
            return redirect(route('cats.hashtags'));
        }

    }


    /**
     * Display list of navigable and editable categories
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $request->validate([
            'search' => 'nullable|min:3|max:255'
        ]);
        $search = $request->search;
        $sort = $request->sort;
        $catsModel = new Cats();

        // Get all cats by id => name and store in lookup array
        $catsCollArr = $catsModel->select('cats.id', 'cats.title', 'cats.level')->orderBy('title', 'asc')->pluck('title', 'id');

        // Get cats level and key by cats.id
        $catsLevelArr = $catsModel->select('cats.id', 'cats.level')->orderBy('title', 'asc')->pluck('level', 'id');

        // Get all cats that have a relationship with a parent. For the checkboxes below the category detail
        $catsRelatedArr = $catsModel->select('cats.id', 'cats_p_and_c.parent_id')
            ->join('cats_p_and_c', 'cats.id', '=', 'cats_p_and_c.child_id')
            ->pluck('parent_id', 'id')
            ->toArray();

        // Get cats on page
        $catsModel = $catsModel->select('cats.rank', 'cats.deactivated', 'cats.id', 'cats.title', 'cats.description', 'cats.level', 'cats_p_and_c.parent_id')
            ->leftJoin('cats_p_and_c', 'cats.id', '=', 'cats_p_and_c.child_id');
        if (!empty($search)) {
            $catsModel = $catsModel->where("cats.title", "like", "%" . $search . "%");
        }
        if (Site::inst('MAX_CATEGORY_LEVEL') == 1) {
            $catsModel = $catsModel->orderBy('cats.rank', 'asc');
        } else {
            $catsModel = $catsModel->orderBy('cats.level', 'asc');
        }
        $catsModel = $catsModel->groupBy("cats.id");
        $perPage = 200;
        $catsPaginator = $catsModel->paginate($perPage);

        $catsPandCObj = new CatsPandC();

        // Get array one dimension deep of all cats that are a parent and their children
        $parentChildArr = $catsPandCObj->getParentChildArr();

        // $parentChildFlattenedArr is for toggle category hierarchy div
        $parentChildFlattenedArr = $catsPandCObj->getFlattenedHier();
        $parentChildHierArr = $catsPandCObj->getHierarchy();

        $maxLevel = \App\Site::inst('MAX_CATEGORY_LEVEL');
        $usesHashtagCategories = \App\Site::inst('USES_HASHTAG_CATEGORIES');
        $hashtagCategoryArr = \App\Models\Cats::getHashtagCategory();
        $env = \Config::get('app.env');

        return view('cats.index', compact(
            'catsPaginator',
            'catsCollArr',
            'parentChildArr',
            'parentChildHierArr',
            'sort',
            'search',
            'parentChildFlattenedArr',
            'catsRelatedArr',
            'catsLevelArr',
            'maxLevel',
            'env',
            'usesHashtagCategories',
            'hashtagCategoryArr'
        ));
    }

    /*
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  \App\Items  $items
    * @return \Illuminate\Http\Response
    */
    public function updatedeactivated(Request $request, Cats $cats) {
        $cats->deactivated = $request->deactivate == 1 || $request->deactive == -1 ? 1 : 0;
        $cats->update();
        $msg = '';
        if ($cats->deactivated == 0) {
            $isHashtagCategory = \App\Models\Cats::isHashtagCategory($cats->id);
            if (!$isHashtagCategory) {
                $r = \App\Models\SocialMedia::getSocialMediaWithCatsId($cats->id);
            } else {
                $r = \App\Models\SocialMedia::getHashtagSocialMediaWithCatsId($cats->id);
            }
            $num = $r->count();
            if ($num === 0) {
                $msg = "Once the category has unhidden social media associated with it (it currently does not), it will appear to the public.";
            }
        }

        \App\Models\Read::writeCategoryJsonToS3();

        return ["success" => true, 'message' => $msg];
    }


    /**
     * Store category name and description
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|min:3|unique:cats|max:30',
            'description' => 'nullable'
        ]);
        $level = empty($request->level) ? 1 : $request->level;
        $rank = empty($request->rank) ? 0 : $request->rank;
        $maxLevel = Site::inst('MAX_CATEGORY_LEVEL');
        if ($maxLevel > 1 && $level == 0) {
            Session::flash('error', 'You must select a category level.');
            return redirect()->route('cats.index')->withInput();
        }
        Cats::create([
            'title' => $request->title,
            'description' => $request->description,
            'level' => $level,
            'deactivated' => -1,
            'rank'=> $rank
        ]);
        $id = DB::getPdo()->lastInsertId();
        $singleCats = Site::inst('MAX_CATEGORY_LEVEL');
        if ($singleCats == 1 || $level == 1) {
            $catsPandCObj = new CatsPandC();
            $catsPandCObj->create(['parent_id' => 0, 'child_id' => $id]);
        }
        Session::flash('success', 'For the category to appear to public you must click its ACTIVATE button.');
        return redirect(route('cats.index'));
    }

    /**
     *
     * Update categories name, desc, level
     * The update of the categories relationship happens in 'updaterelationships'
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Cats  $cats
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Cats $cats)
    {

        $levelValidator = 'nullable';
        if (false != ($maxCategoryLevel = Site::inst('MAX_CATEGORY_LEVEL'))) {
            $levelValidator = 'integer|between:1,' . $maxCategoryLevel;
        }
        $request->validate([
            //'title' => 'required|min:3|max:30' . $uniqueTitleValidation,
            'title' => 'required|min:3|max:30',
            'description' => 'nullable|min:3|max:60',
            'level' => $levelValidator
        ]);
        $cats->title = $request->title;
        $cats->description = $request->description;
        //$cats->deactivated = !empty($request->deactivate) ? 1 : 0;
        $level = $cats->level = !empty($request->level) ? $request->level : 1;
        $cats->rank = !empty($request->rank) ? $request->rank : 0;
        $cats->update();

        // If site has multiple depth categories, changing a 'level' of a category from 1 to anything, that
        // will make that category no longer be parent only.
        // TODO handle moving a category parent to a level beneath the children it is a parent of
        if (Site::inst('MAX_CATEGORY_LEVEL') > 1) {
            $catsPandCModel = new CatsPandC();
            $r = $catsPandCModel->where("child_id", "=", $cats->id)
                ->where("parent_id", "=", 0)
                ->get()->toArray();
            // updating a category to level 1 when the category was not set to parent_id 0 in cats_p_and_c, set the parent_id
            // of 0 in cats_p_and_c for the category
            //if (count($r) == 0 && $request->level == 1) {
            if (count($r) == 0 && $level == 1) {
                $catsPandCModel->create(['parent_id' => 0, 'child_id' => $cats->id]);
            }
            // updating a category to something other than level 1 when category had a parent_id of 0 in cats_p_and_c,
            // delete that parent_id of 0 row in cats_p_and_c
            //if (count($r) > 0 && $request->level != 1) {
            if (count($r) > 0 && $level != 1) {
                $catsPandCModel->delete(['parent_id' => 0, 'child_id' => $cats->id]);
            }
        }

        \App\Models\Read::writeCategoryJsonToS3();

        $reload = 0;
        if ($request->old_level != $request->level) {
            $reload = 1;
        }
        Session::flash('success', 'Updated!');
        return ["success" => true, "reload" => $reload];

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Cats  $cats
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Cats $cats )
    {

        // see if there any items beneath cats
        $itemsCatsObj = new ItemsCats();
        $itemsRows = $itemsCatsObj->where("cats_id", "=", $cats->id)->get();
        if ($itemsRows->count()) {
            $err = "The category has Main Accounts beneath it. Search Main Accounts by category and delete or reassign those Main Accounts first.";
            Session::flash('error', $err);
            return redirect()->route('cats.index');
        }
        $catsPandCObj = new CatsPandC();
        $catsRows = $catsPandCObj->where("parent_id", "=", $cats->id)->get();
        if ($catsRows->count()) {
            $err = "That category has children categories beneath it. Delete or reassign them first.";
//            return response()->json([
//                'error' => "That category has children categories beneath it. Delete or reassign them first."
//            ], 500);
            Session::flash('error', $err);
            return redirect()->route('cats.index');
        }

        $cats->delete();
        CatsPandC::where('parent_id', '=', $cats->id)->delete();
        CatsPandC::where('child_id', '=', $cats->id)->delete();

        \App\Models\Read::writeCategoryJsonToS3();

        Session::flash('success', 'Successfully deleted!');
        return redirect()->route('cats.index');

        //return ["success" => true];

    }

    /**
     * Update an item's single category
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Cats  $cats
     * @return \Illuminate\Http\Response
     */
    public function updaterelationship(Request $request, Cats $cats) {

        $action = $request->action;
        $parentId = $cats->id;
        $childId = $request->child_id;
        if (empty($childId)) {
            return ['success' => false];
        }
        $success = false;
        // don't allow deletion if children exist below the cat id to be deleted
        if ($action == 'delete') {
            // Setting and unsetting the parent category with children doesn't seem to be a problem
//            $r = \DB::table("cats_p_and_c")->where('parent_id', '=', $childId)->get();
//            $num = $r->count();
//            if ($num) {
//                return ['success' => false];
//            }
            \DB::table('cats_p_and_c')->where('parent_id', '=', $parentId)->where('child_id', '=', $childId)->delete();
            $success = true;
        } else if ($action == 'add') {
            $catsPandCObj = new CatsPandC;
            $catsPandCObj->parent_id = $parentId;
            $catsPandCObj->child_id = $childId;
            $catsPandCObj->save();
            $success = true;
        }
        return ['success' => $success];
    }

    /**
     * Display images related to category for editing
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Cats  $cats
     * @return \Illuminate\Http\Response
     */
    public function images(Request $request) {

        $sort = $request->sort;
        if ($sort == 'alpha') {
            $catsRows = Cats::select("id", "title", "image")->orderBy("title", "asc")->get()->toArray();
        } else {
            $catsRows = Cats::select("id", "title", "image")->orderBy("level", "asc")->orderBy("rank", "asc")->get()->toArray();
        }
        return view('cats.images', compact("catsRows", "sort"));

    }

    /**
     * Store category name and description
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function imagesstore(Request $request, Cats $cats)
    {

        $sort = $request->sort;
        $this->validate($request, [
            'imageupload'  => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        // File should be saved to /tmp before being moved to aws
        $uploadedToTmp = false;
        $destinationPath = '/tmp/';

        if (!empty($request->imageurl)) {
            $request->validate([
                'imageurl' => 'min:10|max:255',
            ]);
            $imageUrl = filter_var($request->imageurl, FILTER_SANITIZE_URL);
            if (filter_var($imageUrl, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED|FILTER_FLAG_HOST_REQUIRED|FILTER_FLAG_PATH_REQUIRED) === FALSE) {
                Session::flash('error', 'That is not a valid url.');
                return redirect()->route('cats.images', ['sort' => $sort])->withInput();
            }
            if (false === ($imageContents = file_get_contents($imageUrl))) {
                Session::flash('error', 'Failed to retrieve image from ' . $imageUrl);
                return redirect()->route('cats.images', ['sort' => $sort])->withInput();
            }
            $pattern = "/^content-type\s*:\s*(.*)$/i";
            if (($header = array_values(preg_grep($pattern, $http_response_header))) &&
                (preg_match($pattern, $header[0], $match) !== false))
            {
                $contentType = $match[1];
                $tmp = explode("/", $contentType);
                $imageExt = strtolower(array_pop($tmp));
                if (preg_match("~jpeg|png|jpg|gif|svg~", $imageExt) === false) {
                    Session::flash('error', "Invalid image type detected: $imageExt");
                    return redirect()->route('cats.images')->withInput();
                }
            } else {
                Session::flash('error', 'Was not able to retrieve the image type. Are you sure the url points to an image? - ' . $imageUrl);
                return redirect()->route('cats.images', ['sort' => $sort])->withInput();
            }
            $imageName = $this->getThumbnailName($cats->id, $imageExt);
            file_put_contents("/tmp/" . $imageName, $imageContents);
            $uploadedToTmp = true;
        }

        if (!empty($request->imageupload)) {
            $image = $request->file('imageupload');
            $imageName = $this->getThumbnailName($cats->id, $image->getClientOriginalExtension());
            $manager = new Image();
            $resizeImage = $manager->make($image->getRealPath());
            $resizeImage->resize(100, 100, function($constraint){
                $constraint->aspectRatio();
            })->save($destinationPath . '/' . $imageName);
            $uploadedToTmp = true;
        }

        if ($uploadedToTmp) {
            $bucket = \App\Site::inst('AWS_BUCKET');
            $aws = new AWSS3();
            $r = $aws->updateS3('categorythumbnails/' . $imageName, $destinationPath.$imageName, $bucket)->toArray();
            $awsUrl = $r['ObjectURL'];
            $cats->image = $awsUrl;
            $cats->update();
            unlink($destinationPath.$imageName);
        }

        if (!$uploadedToTmp) {
            Session::flash('error', 'Failed to save to tmp directory. Contact dev with this message.');
            return redirect()->route('cats.images')->withInput();
        } else {
            Session::flash('success', 'Uploaded!');
            return redirect()->route('cats.images', ['sort' => $sort]);
        }

    }

    private function getThumbnailName($id, $ext) {
        return $id . '_100x100.' . $ext;
    }

    /**
     * Remove the image from storage.
     *
     * @param  \App\Cats  $cats
     * @return \Illuminate\Http\Response
     */
    public function imagesdestroy(Request $request, Cats $cats )
    {
        $tmp = explode("/", $cats->image);
        $image = array_pop($tmp);
        $cats->image = '';
        $cats->update();
        $bucket = \App\Site::inst('AWS_BUCKET');
        $aws = \AWS::createClient('s3');
        $aws->deleteObject([
            'Bucket' => $bucket,
            'Key'    => 'categorythumbnails/' . $image
        ]);
        Session::flash('success', 'Deleted!');
        return redirect()->route('cats.images', ['sort'=> $request->sort]);
    }




}
