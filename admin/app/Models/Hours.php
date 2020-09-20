<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hours extends Model
{
    protected $fillable = ['id', 'items_id', 'hours', 'no_yelp_update'];

    protected $table = 'hours';

//    public function items()
//    {
//        return $this->hasOne('App\Models\Items');
//    }

}
