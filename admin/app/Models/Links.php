<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Links extends Model
{

    protected $fillable = array('name', 'link','imgsrc', 'open_link_in_new_window');


}