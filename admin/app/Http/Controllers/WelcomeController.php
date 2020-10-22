<?php

namespace App\Http\Controllers;


class WelcomeController extends Controller
{



    public function binarySearch ($listArr, $searchValue) {
        $lowIndex = 0;
        $highIndex = count($listArr) - 1;
        $num = 0;
        while ($lowIndex <= $highIndex) {
            $listIndex = floor(($lowIndex + $highIndex) / 2);
            $listValue = $listArr[$listIndex];
            print "Iteration #" . ($num++) . " lowIndex:" . $lowIndex . " highIndex:" . $highIndex . " middle listIndex:" . $listIndex . "<br />";
            if ($listValue === $searchValue) {
                return ("listIndex:" . $listIndex . " listValue:" . $listValue . " searchValue:" . $searchValue);
            }
            if ($listValue < $searchValue) {
                print "listValue" . $listValue . " is < searchValue " . $searchValue . "<br />";
                $lowIndex = $listIndex + 1;
            } else {
                print "listValue" . $listValue . " is > searchValue " . $searchValue . "<br />";
                $highIndex = $listIndex - 1;
            }
        }
        return null;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function welcome()
    {
        $arr = explode(".", $_SERVER['HTTP_HOST']);
        $tld = isset($arr[2]) ? $arr[2] : (isset($arr[1]) ? $arr[1] : $arr[0]);
        $site = Config("app.sitekey");
        $env = \Config::get('app.env');
        $view = 'welcome';
        if ($env == 'production' && $site == 'abbotkinneybl') {
            $view = 'welcome' . $site;
        }
        return view($view, compact('tld'));
    }


}
