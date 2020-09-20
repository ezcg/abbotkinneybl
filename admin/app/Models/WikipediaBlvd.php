<?php

namespace App\Models;

use App\Models\Items as Items;
use Illuminate\Database\Eloquent\Model;

class WikipediaBlvd extends Model
{
    protected $fillable = ['items_id', 'url','description', 'deactivated'];

    protected $table = 'wikipedia';

    public function items()
    {
        return $this->belongsTo('App\Models\Items');
    }

    public static function getNumUnassociated() {
        $itemsModel = new Items();
        $numUnassociated = $itemsModel->select('items.id')
            ->leftJoin('wikipedia', 'items.id', '=', 'wikipedia.items_id')
            ->where("items.deactivated", "=", 0)
            ->whereNull('wikipedia.items_id')
            ->count();
        return $numUnassociated;
    }

    public static function getNumDeactivated() {
        $itemsModel = new Items();
        $numDeactivated = $itemsModel->select('items.id')
            ->join('wikipedia', 'items.id', '=', 'wikipedia.items_id')
            ->where('wikipedia.deactivated', '=', 1)
            ->count();
        return $numDeactivated;
    }

    /*
     * Get paginated list based on view passed in
     */
    public static function getWikipediaColl($view) {

        if ($view == "unassociated") {
            $wikipediaColl = \DB::table('items')
                ->leftJoin('wikipedia', 'items.id', '=', 'wikipedia.items_id')
                ->select('items.title as title', 'items.id as items_id')
                ->where("items.deactivated", "=", 0)
                ->whereNull('wikipedia.items_id')->paginate(8);
        } else if ($view == "deactivated") {
            $wikipediaColl = \DB::table('items')
                ->join('wikipedia', 'items.id', '=', 'wikipedia.items_id')
                ->select('items.title as title', 'items.id as items_id', 'wikipedia.*')
                ->where('wikipedia.deactivated', '=', 1)->paginate(8);
        }

        return $wikipediaColl;

    }

}
