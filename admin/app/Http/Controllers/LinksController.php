<?php

namespace App\Http\Controllers;

use App\Models\AWSS3;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Input;
use Redirect;
use Log;

use App\Models\Links;

class LinksController extends Controller
{

    public function __construct() {
        //$this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $linksObj = Links::all();
        $bucket = \App\Site::inst('AWS_RAW_BUCKET');
        $footerLinksUrl = $bucket . "/json/footerlinks.json";
        $id = 0;
        $name = '';
        $imgsrc = '';
        $open_link_in_new_window = '';
        $link = '';
        return view('links.index', compact(
            'linksObj',
            'footerLinksUrl',
            'id',
            'name',
            'link',
            'imgsrc',
            'open_link_in_new_window'
        ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request  $request)
    {

        $request->validate([
            'name' => "required|min:3|unique:links|max:64",
            'link' => "required|min:12",
            'imgsrc' => 'nullable',
            'open_link_in_new_window' => 'min:0|max:1|integer'
        ]);

        $id = Links::create( array(
            "name" => $request->name,
            "link" => $request->link,
            "imgsrc" => $request->imgsrc,
            "open_link_in_new_window" => $request->open_link_in_new_window
        ) );
        $this->updateS3Json();
        return Redirect::route('links.index', $id)->with('success', 'Link created.');

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show()
    {

        $linkArr = Links::orderBy('rank', 'ASC')->lists('name','id');
        return view('links.show', compact('linkArr'));

    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $linksObj = Links::all();
        $linkObj = Links::whereId($id)->get()->first();
        $id = $linkObj->id;
        $name = $linkObj->name;
        $link = $linkObj->link;
        $imgsrc = $linkObj->imgsrc;
        $open_link_in_new_window = $linkObj->open_link_in_new_window;
        $bucket = \App\Site::inst('AWS_RAW_BUCKET');
        $footerLinksUrl = $bucket . "/json/footerlinks.json";

        return view('links.index', compact(
            'linksObj',
            'footerLinksUrl',
            'id',
            'name',
            'link',
            'imgsrc',
            'open_link_in_new_window'
        ));

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Response
     */
    public function update($id, Request  $request)
    {
        $nameUniqueValidate = '';
        if ($request->old_name != $request->name) {
          $nameUniqueValidate = '|unique:links';
        }
        $request->validate([
            'name' => "required|min:3|max:64" . $nameUniqueValidate,
            'link' => "required|min:12",
            'imgsrc' => 'nullable',
            'open_link_in_new_window' => 'min:0|max:1|integer'
        ]);

        $linkObj = Links::whereId($id)->get()->first();
        $linkObj->update(array(
            "name" => $request->name,
            "link" => $request->link,
            "imgsrc" => $request->imgsrc,
            'open_link_in_new_window' => $request->open_link_in_new_window
        ));
        $this->updateS3Json();
        return Redirect::route('links.edit', $id)->with('success', 'Link updated.');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {

        Links::whereId($id)->delete();
        $this->updateS3Json();
        return Redirect::route('links.index')->with('success', 'Link deleted.');

    }

    /**
     * Set deactivated = 1 in db
     *
     * @param  int  $id
     * @return Response
     */
    public function deactivate(Request $request, $id)
    {
        $deactivated = !empty($request->deactivated) ? 1 : 0;
        \DB::table('links')->where(["id" => $id])->update(["deactivated" => $deactivated]);

        $msg = 'Link reactivated';
        if ($deactivated) {
            $msg = 'Link deactivated.';
        }
        $this->updateS3Json();
        return Redirect::route('links.index')->with('success', $msg);

    }

    // After every operation; delete, update, new, create a new json file of all links and save to s3
    private function updateS3Json() {

        $r = \DB::table("links")
            ->select("id", "name", "link","imgsrc", "open_link_in_new_window")
            ->where("deactivated", "=", 0)
            ->orderBy('rank', 'ASC')
            ->get()
            ->toArray();

        $json = json_encode($r);
        $filename = "footerlinks.json";
        file_put_contents("/tmp/" . $filename , $json);
        $aws = new AWSS3();
        $bucket = \App\Site::inst('AWS_BUCKET');
        $r = $aws->updateS3('json/' . $filename, "/tmp/" . $filename, $bucket);
    }

}
