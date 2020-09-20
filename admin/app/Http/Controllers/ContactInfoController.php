<?php

namespace App\Http\Controllers;

use App\Models\Cats;
use App\Models\SocialMediaAccounts;
use App\Models\ContactInfo;
use Illuminate\Http\Request;
use Validator, Input, Redirect, Session;


class ContactInfoController extends Controller
{

    public function __construct() {
        //$this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // check for existing row in contact_info. if exists redirect to update, if not redirect to create
        if (empty($request->items_id)) {
            return response()->view('contactinfo.index', ['items_id not passed in'], 500);
        }
        $contactInfoColl = ContactInfo::select(['id', 'items_id'])->where('items_id', $request->items_id)->get();
        if (count($contactInfoColl)) {
            return Redirect::to('contactinfo/' .  $contactInfoColl[0]->id . '/edit?title=' . urlencode($request->title));
            //return redirect(route('contactinfo.edit', ['id' => $contactInfoColl[0]->id, 'title' => $request->title]));
        } else {
            return Redirect::to('contactinfo/create?items_id=' .   $request->items_id . '&title=' . urlencode($request->title));
            //return redirect(route('contactinfo.create', ['items_id' => $request->items_id, 'title' => $request->title]));
        }

    }

    private function getLatLon($addressObj)
    {

        if (empty($addressObj->address) || empty($addressObj->city) || empty($addressObj->state)) {
            return false;
        }
        $address = urlencode($addressObj->address . "," . $addressObj->city . "," . $addressObj->state);
        $url = "https://maps.googleapis.com/maps/api/geocode/json?address=" . $address . "&key=";
        $url.=\Config::get('app.googlemapskey');
        $json = file_get_contents($url);
        $jsonObj = json_decode($json);
        $r = ($jsonObj->results[0]->geometry->location);
        return $r;
    }

    /**
     * Show the form for creating a new contact info row.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {

        $itemsId = $request->items_id;
        $r = SocialMediaAccounts::select("*")
            ->where("items_id", "=", $itemsId)
            ->where("site", "=", "yelp.com")
            ->get();
        $bizId = '';
        if ($r && $r->count()) {
          $bizId = $r[0]->source_user_id;
        }
        $contactInfoArr = array(
            'id' => 0,
            'items_id' => $itemsId,
            'biz_id' => $bizId,
            'first_name' => '',
            'last_name' => '',
            'business' => $request->title,
            'address' => '',
            'address2' => '',
            'postal_code' => '',
            'city' => '',
            'state' => '',
            'phone_number' => '',
            'email' => '',
            'website' => '',
            'lon' => '',
            'lat' => '',
            'created_at' => '',
            'updated_at' => '',
            'no_yelp_update' => 0,
            'title' => $request->title
        );
        $location = \App\Site::inst("LOCATION");
        $yelpFindLoc = \App\Site::inst("YELP_FIND_LOC");
        return view('contactinfo.edit', [
            'itemsId' => $itemsId,
            'title' => $request->title,
            'contactInfoArr' => $contactInfoArr,
            'location' => $location,
            'yelpFindLoc' => $yelpFindLoc
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $request->validate([
           'business' => "required|min:3|unique:contact_info|max:60",
           'items_id' => 'required',
           'email'    => 'nullable|email',
           'postal_code' => 'nullable|numeric'
        ]);

        // store
        $c = new ContactInfo;
        $c->business       = $request->business;
        $c->items_id       = $request->items_id;
        $c->address       = $request->address;
        $c->address2 = $request->city . ',' . $request->state . ' ' . $request->postal_code;
        $biz_id = $request->biz_id;
        $msg = 'Created!';
        if (!empty($biz_id) && !stristr($biz_id, "-") && $request->no_yelp_update == 0) {
            $msg = 'Saved everything but the Yelp ID because it was invalid. You cannot have Yelp update this info without a valid Yelp ID.';
            $biz_id = '';
        }
        $c->biz_id = $biz_id;
        $c->postal_code       = $request->postal_code;
        $c->city       = $request->city;
        $c->state       = $request->state;
        $c->phone_number       = $request->phone_number;
        $c->email       = $request->email;
        $c->website       = $request->website;
        $c->no_yelp_update = $request->no_yelp_update;

        $c->lon = '';
        $c->lat = '';
        if (false !== ($latLonObj = $this->getLatLon($c))) {
            $c->lon       = $latLonObj->lng;
            $c->lat      = $latLonObj->lat;
        }

        $c->save();

        // redirect
        Session::flash('success', $msg);
        return Redirect::to('contactinfo/' . $c->id . '/edit');

    }

    /**
     * List for sorting Main Accounts with/without contact info and filtering by categories
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function all(Request $request)
    {
        $excludeCatsArr = !empty($request->excludeCatsArr) ? $request->excludeCatsArr : [];
        $missing = $request->missing;
        if (empty($missing) && !empty($_COOKIE['missing'])) {
            $missing = 1;
        }
        if (!empty($_COOKIE['exclude_cats_json_str'])) {
            $arr = (array)json_decode($_COOKIE['exclude_cats_json_str']);
            $excludeCatsArr =$excludeCatsArr + $arr;
        }
        // get all main accounts that are missing contact info
        if ($missing) {
            $itemsArr = \DB::table("items")
                ->select('items.*', 'contact_info.business', 'contact_info.id as ci_id')
                ->join('items_cats', 'items.id', '=', 'items_cats.items_id')
                ->leftJoin('contact_info', 'items.id', '=', 'contact_info.items_id')
                ->where('deactivated', '=', 0)
                ->whereNull('contact_info.items_id');
            if (count($excludeCatsArr)) {
                $itemsArr = $itemsArr->whereNotIn('items_cats.cats_id', $excludeCatsArr);
            }

        } else {
        // get all main accounts and contact_info
            $itemsArr = \DB::table("items")
               ->select('items.*', 'contact_info.business', 'contact_info.id as ci_id', 'contact_info.no_yelp_update')
               ->join('items_cats', 'items.id', '=', 'items_cats.items_id')
               ->leftJoin('contact_info', 'items.id', '=', 'contact_info.items_id')
               ->where('deactivated', '=', 0);
                if (count($excludeCatsArr)) {
                    $itemsArr = $itemsArr->whereNotIn('items_cats.cats_id', $excludeCatsArr);
                }
        }
        $itemsArr = $itemsArr->get()->toArray();

        $catsObj = new Cats();
        $catsArr = $catsObj->select()->pluck('title', 'id')->all();

        return view('contactinfo.all', [
            'itemsArr' => $itemsArr,
            'missing' => $missing,
            'excludeCatsArr' => $excludeCatsArr,
            'catsArr' => $catsArr
        ]);

    }

    /**
     * Show the form for editing the specified resource.
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ContactInfo  $contactInfo
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, ContactInfo $contactInfo)
    {

        $contactInfoArr = \DB::table("contact_info")
            ->select('contact_info.*', 'items.title')
            ->join('items', 'items.id', '=', 'contact_info.items_id')
            ->where('deactivated', '=', 0)
            ->where("contact_info.id", "=", $contactInfo->id)
            ->first();

        $contactInfoArr = (array)$contactInfoArr;

        $yelpFindLoc = \App\Site::inst('YELP_FIND_LOC');
        $location = \App\Site::inst('LOCATION');
        return view('contactinfo.edit', [
            'contactInfoArr' => $contactInfoArr,
            'title' => $request->title,
            'itemsId' => $contactInfo->items_id,
            'yelpFindLoc' => $yelpFindLoc,
            'location' => $location
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ContactInfo  $contactInfo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ContactInfo  $contactInfo)
    {

        $request->validate([
           'business' => "required|min:3|max:60",
           'items_id' => 'required',
           'email'    => 'nullable|email',
           'postal_code' => 'nullable|numeric'
        ]);

        $msg = 'Updated!';
        $biz_id = $request->biz_id;
        if (!empty($biz_id) && !stristr($biz_id, "-") && $request->no_yelp_update == 0) {
            $msg = 'Saved everything but the Yelp ID. You cannot have Yelp update this info without a valid Yelp ID.';
            $biz_id = '';
        }
        $contactInfo->biz_id = $biz_id;
        $contactInfo->business = $request->business;
        $contactInfo->items_id = $request->items_id;

        $cAddress = $contactInfo->address;
        $rAddress = $request->address;
        $cPostalCode = $contactInfo->postal_code;
        $rPostalCode = $request->postal_code;
        $cCity = $contactInfo->city;
        $rCity = $request->city;
        $cState = $contactInfo->state;
        $rState = $request->state;

        // If user submitted their own lat/lon and they are different from what is in db, update with user submitted data
        if ($request->lat != $contactInfo->lat || $request->lon != $contactInfo->lon) {
            $contactInfo->lon = $request->lon;
            $contactInfo->lat = $request->lat;
        } else if ($cAddress != $rAddress
            || $cPostalCode != $rPostalCode
            || $cCity != $rCity
            || $cState != $rState
            || !$contactInfo->lat
            || !$contactInfo->lon
        ) {
        // If any part of address changed, update lat/lon
            if (false !== ($latLonObj = $this->getLatLon($request))) {
                $contactInfo->lon = $latLonObj->lng;
                $contactInfo->lat = $latLonObj->lat;
            } else {
                $contactInfo->lon = '';
                $contactInfo->lat = '';
            }
        }

        $contactInfo->address = $request->address;
        $address2 = $request->city . ',' . $request->state . ' ' . $request->postal_code;
        $contactInfo->address2 = $address2;
        $contactInfo->postal_code = $request->postal_code;
        $contactInfo->city = $request->city;
        $contactInfo->state = $request->state;

        $contactInfo->phone_number = $request->phone_number;
        $contactInfo->email = $request->email;
        $contactInfo->website = $request->website;
        $contactInfo->no_yelp_update = $request->no_yelp_update;



        $contactInfo->save();

        // redirect
        Session::flash('success', $msg);
        return Redirect::to('contactinfo/' . $contactInfo->id . '/edit');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ContactInfo  $contactInfo
     * @return \Illuminate\Http\Response
     */
    public function destroy(ContactInfo $contactInfo)
    {
        $contactInfo->delete();
        Session::flash('success', 'Successfully deleted contact info for ' . $contactInfo->business);
        return Redirect::to('contactinfo/' . $contactInfo->id . '/edit');
    }
}
