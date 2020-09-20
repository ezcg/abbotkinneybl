<?php

namespace App\Http\Controllers;


class WelcomeController extends Controller
{
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
        if ($env == 'production') {
            $view = 'welcome' . $site;
        }
        return view($view, compact('tld'));
    }


}
