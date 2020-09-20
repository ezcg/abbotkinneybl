<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\YelpFusion;
use App\Models\Yelp;
use Illuminate\Support\Facades\Config;

class YelpController extends Controller
{

    public function __construct() {
        $env = \Config::get('app.env');
        if ($env == 'production') {
            exit("This can only be called in cron or local environment, not production");
        }
    }

    public function index(Request $request)
    {

        $yelpObj = new Yelp();
        if ($request->action == 'reviews') {
            // to get a single review, pass in the source_user_id
            $source_user_id = '';
            if (!empty($request->id)) {
                $source_user_id = $request->id;
            }
            $yelpObj->getFeed($source_user_id);
            //$r = $yelpObj->convertFeedToSocialMedia();
        } elseif ($request->action == 'contactinfo' && $request->update == 'all') {
            $r = $yelpObj->updateContactInfo($request->id, 'all');
        } elseif ($request->action == 'contactinfo' && $request->update == 'missing') {
            $r = $yelpObj->updateContactInfo(null, 'missing');
        }else {
            exit("not recognizing " . $request->action . "with update: " . $request->update);
        }
        echo printR($r);
        return;

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $yelpObj = new Yelp();
        $arr = $yelpObj->convertFeedToSocialMedia();
        dd($arr);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
