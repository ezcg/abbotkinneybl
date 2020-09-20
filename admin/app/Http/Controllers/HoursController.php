<?php

namespace App\Http\Controllers;

use App\Models\Cats;
use App\Models\Hours;
use Illuminate\Http\Request;
use Validator, Input, Redirect, Session;

class HoursController extends Controller
{

    public function __construct()
    {
        //$this->middleware('auth');
    }

    /**
     * Show form for creating/updating hours of operation for the business.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {

        $r = \DB::table("hours")->where("items_id", '=', $request->items_id)->get()->toArray();
        $hoursArr = !empty($r) ? $this->buildHoursArr($r[0]->hours) : array();
        $id = !empty($r) ? $r[0]->id : 0;
        $noYelpUpdate = !empty($r) ? $r[0]->no_yelp_update : 0;
        return view('hours.index', [
            'hoursArr' => $hoursArr,
            'itemsId' => $request->items_id,
            'title' => $request->title,
            'id' => $id,
            'noYelpUpdate' => $noYelpUpdate
        ]);

    }


    /**
     * List for sorting Main Accounts with/without hours info and filtering by categories
     * @param  IlluminateHttpRequest  $request
     * @return IlluminateHttpResponse
     */
    public function all(Request $request)
    {
        $excludeCatsArr = !empty($request->excludeCatsArr) ? $request->excludeCatsArr : [];
        $missing = $request->missing;
        if (empty($missing) && !empty($_COOKIE['hours_missing'])) {
            $missing = 1;
        }
        if (!empty($_COOKIE['hours_exclude_cats_json_str'])) {
            $arr = (array)json_decode($_COOKIE['hours_exclude_cats_json_str']);
            $excludeCatsArr =$excludeCatsArr + $arr;
        }
        // get all main accounts without hours info
        if ($missing) {
            $itemsArr = \DB::table("items")
                ->select('items.*', 'hours.hours', 'hours.no_yelp_update')
                ->join('items_cats', 'items.id', '=', 'items_cats.items_id')
                ->leftJoin('hours', 'items.id', '=', 'hours.items_id')
                ->where('deactivated', '=', 0)
                ->whereNull('hours.hours');
            if (count($excludeCatsArr)) {
                $itemsArr = $itemsArr->whereNotIn('items_cats.cats_id', $excludeCatsArr);
            }

        } else {
            // get all main accounts and hours rows
            $itemsArr = \DB::table("items")
                ->select('items.*', 'hours.*')
                ->join('items_cats', 'items.id', '=', 'items_cats.items_id')
                ->leftJoin('hours', 'items.id', '=', 'hours.items_id')
                ->where('deactivated', '=', 0);
            if (count($excludeCatsArr)) {
                $itemsArr = $itemsArr->whereNotIn('items_cats.cats_id', $excludeCatsArr);
            }
        }
        $itemsArr = $itemsArr->get()->toArray();

        $catsObj = new Cats();
        $catsArr = $catsObj->select()->pluck('title', 'id')->all();

        return view('hours.all', [
            'itemsArr' => $itemsArr,
            'missing' => $missing,
            'excludeCatsArr' => $excludeCatsArr,
            'catsArr' => $catsArr
        ]);

    }


    /**
     * Update the hours of operation for the business.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Hours $hours
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Hours $hours) {

        $hoursStr = $this->buildHoursStr($request);

        $hours->hours = $hoursStr;
        $hours->no_yelp_update = $request->no_yelp_update;
        $hours->items_id = $request->items_id;
        $hours->save();

        // redirect
        Session::flash('success', "Updated!");
        return Redirect::to('hours/index?items_id=' . $request->items_id . "&title=" . rawurlencode($request->title));

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if ($request->dayOfWeekArr['add'] == 'add'
            || $request->startTimeHourArr['add'] == 'add'
            || $request->startTimeMinuteArr['add'] == 'add'
            || $request->endTimeHourArr['add'] == 'add'
            || $request->endTimeMinuteArr['add'] == 'add') {

            Session::flash('error', "Start and End must have a day of week, hour, minute and am or pm selected. ");
            return redirect()->route('hours.index', [
                'items_id'=>$request->items_id,
                'title' => $request->title
            ]);

        }
        $hoursStr = $this->buildHoursStr($request);
        if ($request->id == 0) {
            $hoursModel = new Hours();
        } else {
            // Add the existing hours to the newly submitted hours
            $hoursModel = Hours::where("id", $request->id)->first();
            if ($hoursModel->count() && !empty($hoursModel->hours)) {
                $dbHoursStr = $hoursModel->hours;
                if ($dbHoursStr && $hoursStr) {
                    $dbHoursStr = substr($dbHoursStr, 0, -1);
                    $hoursStr = substr($hoursStr, 1);
                    $hoursStr = $dbHoursStr . "," . $hoursStr;
                }
            }
        }

        $hoursModel->hours = $hoursStr;
        $hoursModel->items_id = $request->items_id;
        $hoursModel->no_yelp_update = $request->no_yelp_update;
        $hoursModel->save();

        // redirect
        Session::flash('success', "Created!");
        return Redirect::to('hours/index?items_id=' . $request->items_id . '&title=' . $request->title);

    }

    /*
     * $str must be in the format:
     * ["Mon 11:30 am - 02:30 pm","Mon 05:00 pm - 10:00 pm","Tue 11:30 am - 02:30 pm","Tue 05:00 pm - 10:00 pm","Wed 11:30 am - 02:30 pm","Wed 05:00 pm - 10:00 pm","Thu 11:30 am - 02:30 pm","Thu 05:00 pm - 10:00 pm","Fri 11:30 am - 02:30 pm","Fri 05:00 pm - 11:00 pm","Sat 11:00 am - 03:00 pm","Sat 05:00 pm - 11:00 pm","Sun 11:00 am - 03:00 pm","Sun 05:00 pm - 10:00 pm"]
    */
    private function buildHoursArr($str) {

        if (empty($str)) {
            return [];
        }
        $str = preg_replace('~\[|\]|"|- ~', "", $str);
        $arr = explode(",", $str);
        $hoursArr = [];
        foreach($arr as $key => $val) {
            $lineArr = explode(" ", $val);
            $dayOfWeek = $lineArr[0];
            $startTime = $lineArr[1];
            $startAMPM = $lineArr[2];
            $endTime = $lineArr[3];
            $endAMPM = $lineArr[4];
            $timeArr = explode(":", $startTime);
            // make primary key unixtime so as to sort easily
            $hour = $timeArr[0];
            if($startAMPM == 'pm') {
                $hour = 12 + $timeArr[0];
            }
            $ut = mktime($hour, $timeArr[1], 0, date("m"), date("N", strtotime($dayOfWeek)) , date("Y"));
            $hoursArr[$ut][$dayOfWeek][$key]['start_time_hour'] = $timeArr[0];
            $hoursArr[$ut][$dayOfWeek][$key]['start_time_minute'] = $timeArr[1];
            $hoursArr[$ut][$dayOfWeek][$key]['start_time_ampm'] = $startAMPM;
            $timeArr = explode(":", $endTime);
            $hoursArr[$ut][$dayOfWeek][$key]['end_time_hour'] = $timeArr[0];
            $hoursArr[$ut][$dayOfWeek][$key]['end_time_minute'] = $timeArr[1];
            $hoursArr[$ut][$dayOfWeek][$key]['end_time_ampm'] = $endAMPM;
        }

        ksort($hoursArr);
        // remove unixtime key
        $newHoursArr = array();
        foreach($hoursArr as $ut => $arr) {
            foreach($arr as $dayOfWeek => $rowArr) {
                foreach($rowArr as $i => $row) {
                    $newHoursArr[$dayOfWeek][$i] = $row;
                }
            }
        }

        return $newHoursArr;

    }

    /*
     * $arr must be in the format
     * array(
     *      ["three letter day of week"][0]['start'] = 'start hour'
     *      ["three letter day of week"][0]['end'] = 'end hour'
     *      ["three letter day of week"][0]['am or pm'] = 'am or pm'
     * )
     * @return string example ["Mon 11:30 am - 02:30 pm","Mon 05:00 pm - 10:00 pm","Tue 11:30 am - 02:30 pm","Tue 05:00 pm - 10:00 pm",
     */
    private function buildHoursStr(Request $request) {

        $dayOfWeekArr = $request->dayOfWeekArr;
        $startTimeHourArr = $request->startTimeHourArr;
        $startTimeMinuteArr = $request->startTimeMinuteArr;
        $startTimeAmpmArr = $request->startTimeAmpmArr;
        $endTimeHourArr = $request->endTimeHourArr;
        $endTimeMinuteArr = $request->endTimeMinuteArr;
        $endTimeAmpmArr = $request->endTimeAmpmArr;
        $deleteArr = $request->deleteArr;

        $str = '';
        foreach($dayOfWeekArr as $i => $day) {
            if (!empty($deleteArr[$i])) {
                continue;
            }
            $str.= '"' . $day . ' ';
            $str.= $this->padTwo($startTimeHourArr[$i]) . ':' . $this->padTwo($startTimeMinuteArr[$i]) . ' ' . $startTimeAmpmArr[$i];
            $str.= ' - ';
            $str.= $this->padTwo($endTimeHourArr[$i]) . ':' . $this->padTwo($endTimeMinuteArr[$i]) . ' ' . $endTimeAmpmArr[$i];
            $str.= '",';
        }

        $str = '[' . substr($str, 0, -1) . ']';
        return $str;

    }

    private function padTwo($val) {
        return str_pad($val, 2, "0", STR_PAD_LEFT);
    }

}
