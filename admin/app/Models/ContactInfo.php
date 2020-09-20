<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactInfo extends Model
{
    protected $fillable = [
        'id',
        'items_id',
        'biz_id',
        'first_name',
        'last_name',
        'business',
        'address',
        'address2',
        'postal_code',
        'city',
        'state',
        'phone_number',
        'email',
        'website',
        'lon',
        'lat',
        'hours',
        'no_yelp_update'
    ];

    protected $table = 'contact_info';

    public function items()
    {
        return $this->hasOne('App\Models\Items');
    }

}