<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \Log;
use Twitter;
use Validator, Input, Redirect, Session;
use App\Site;

class InstagramController extends Controller
{

    public function __construct() {
        $env = \Config::get('app.env');
        if ($env == 'production') {
            exit("This can only be called in cron or local environment, not production");
        }
    }

    public function getuseraccesstoken() {

    }

}