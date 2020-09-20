<?php

namespace App\Http\Controllers;

use App\Models\Items as Items;
use App\Models\WikipediaBlvd;
use Illuminate\Http\Request;
use Input;
use Redirect;

class WikipediaController extends Controller
{

    public function __construct() {
        //$this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $itemsId = $request->items_id;
        $view = empty($request->view) ? "unassociated" : $request->view;
        $title = "";
        $url = "";
        $description = "";
        $page = $request->page;

        if ($itemsId) {
            $title = Items::select('title')->where("id", "=", $itemsId)->get()->pluck('title')->first();
            // See if there's already a row in the wikipedia table
            $r = WikipediaBlvd::select("*")->where("items_id", "=", $itemsId)->get();
            if ($r && $r->count()) {
                return redirect()->route('wikipedia.edit', $itemsId);
            }
        }

        $numUnassociated = WikipediaBlvd::getNumUnassociated();
        $numDeactivated = WikipediaBlvd::getNumDeactivated();
        $wikipediaColl = WikipediaBlvd::getWikipediaColl($view);

        $wikipediaArr = [
            'description' => $description,
            'url' => $url,
            'items_id' => $request->items_id,
            'title' => $title
        ];

        return view('wikipedia.index', compact(
            'wikipediaColl',
            'itemsId',
            'title',
            'view',
            'numUnassociated',
            'numDeactivated',
            'wikipediaArr',
            'page'
        ));

    }

    /**
     * Show the form for creating a new resource.
     *
     * @param  Request  $request
     * @return Response
     */
    public function create(Request $request)
    {
        $wikipediaArr = ['description' => '', 'url' => '', 'items_id' => $request->items_id];
        return view('wikipedia.create', compact('wikipediaArr'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request  $request)
    {

        $request->validate([
            'items_id' => "required|unique:wikipedia",
        ]);

        WikipediaBlvd::create( array(
            "description" => $request->description,
            "url" => $request->url,
            "items_id" => $request->items_id
        ) );
        return Redirect::route('wikipedia.index')->with('success', 'Created.');

    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Response
     */
    public function edit(Request $request, $itemsId)
    {

        $view = empty($request->view) ? "unassociated" : $request->view;
        $page = $request->page;

        $wikipediaArr = Items::select(array('items.title as title', 'items.id as items_id', 'wikipedia.*'))
            ->leftJoin('wikipedia', 'items.id', '=', 'wikipedia.items_id')
            ->where('items.id', '=', $itemsId)
            ->get()
            ->first()
            ->toArray();

        $numUnassociated = WikipediaBlvd::getNumUnassociated();
        $numDeactivated = WikipediaBlvd::getNumDeactivated();
        $wikipediaColl = WikipediaBlvd::getWikipediaColl($view);

        return view('wikipedia.edit', compact(
            'wikipediaArr',
            'itemsId',
            'view',
            'numUnassociated',
            'numDeactivated',
            'wikipediaColl',
            'page'
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Response
     */
    public function update($id, Request  $request)
    {
        $view = $request->view;
        $page = $request->page;
        $q = "UPDATE wikipedia SET description = ?, url = ? WHERE items_id = ?";
        $r = \DB::update($q, [$request->description, $request->url, $id]);

        return Redirect::route('wikipedia.edit', [$id, 'view' => $view, 'page' => $page])->with('success', 'Updated.');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {

        WikipediaBlvd::where('items_id', $id)->delete();
        return Redirect::route('wikipedia.index')->with('success', 'Deleted.');

    }

    /**
     * Set deactivated = 1 in db
     *
     * @param  int  $id
     * @return Response
     */
    public function deactivate(Request $request, $id)
    {
        $deactivated = !empty($request->deactivated) ? 1 : 0;
        \DB::table('wikipedia')->where(["items_id" => $id])->update(["deactivated" => $deactivated]);

        $msg = 'Reactivated';
        if ($deactivated) {
            $msg = 'Deactivated.';
        }

        return Redirect::route('wikipedia.index')->with('success', $msg);

    }


    /**
     * Get first five sentences from wikipedia for given search_term
     *
     * @param  Request  $request
     * @param  Items $items
     * @return Response
     */
    public function searchwikipedia(Request $request, Items $items) {

        $request->validate([
            'id' => "required"
        ]);

        $searchTerm = $request->search_term;
        // Use of 'Sr., Jr., IV' at ends of names seems to be not used on wikipedia
        if (\App\Site::inst('TEAMS')) {
            $tmp = explode(" ", $searchTerm);
            if (count($tmp) >= 2) {
                $lastLetters = array_pop($tmp);
                if (strlen($lastLetters) ==2 || strlen($lastLetters) == 3) {
                    $searchTerm = implode(" ", $tmp);
                }
            }
        }

        $wikiUrl = '';
        $deactivate = 0;
        $team = '';
        $found = false;
        $errorMsg = '';
        $wikiObj = $this->getWikiExtract($searchTerm);
        $isDisambiguation = $wikiObj->pageObj->isDisambiguation();
        if (empty($wikiObj->pageObj->isSuccess())) {
            $text = "Nothing found for " . $searchTerm;
            $errorMsg = $text;
        } else if (empty($isDisambiguation)) {
            $found = true;
            $text = $wikiObj->extract;
        } else if ($isDisambiguation) {
            $disambiguateStr = \App\Site::inst('WIKIPEDIA_DISAMBIGUATE');
            if ($disambiguateStr) {
                $searchTerm2 = $searchTerm . " " . $disambiguateStr;
                $wikiObj = $this->getWikiExtract($searchTerm2);
                if ($wikiObj->pageObj->isSuccess() && !stristr($wikiObj->extract, 'may refer to')) {
                    $found = true;
                    $text = $wikiObj->extract;
                }
            }
            if ($found == false) {
                $text = "Too many similar results for search term '$searchTerm' and you'll have to do a manual search and entry.";
                $errorMsg = $text;
            }
        }

        // Get the url to wikipedia
        if ($found) {

            // do some clean up
            // ( Persian: كرستين امانپور‎, romanized: Kristiane Amānpur; born 12 January 1958)
            $text = preg_replace("~\(.*? born ~is", "(born ", $text);
            // add space after period but not when period represents a decimal or letter abbreviation?
            //$text = preg_replace('/(\.)([[:alpha:]]{2,})/', '$1 $2', $text);

            $url = "https://en.wikipedia.org/w/api.php?action=opensearch&format=json&formatversion=2&search=";
            $url.= rawurlencode($searchTerm) . "&namespace=0&limit=10&prop=extracts";
            $jsonStr = file_get_contents($url);
            $jsonArr = json_decode($jsonStr);
            if (!empty($jsonArr[0]) && !empty($jsonArr[3]) && !empty($jsonArr[3][0])) {
                $wikiUrl = $jsonArr[3][0];
            }
        }

        // for sports teams, extract team name and other stuff to auto-update category like team, retired status, free-agent, etc
        if (\App\Site::inst('PRO_SPORTS')) {

            if (stristr($text, "may refer to")) {
                $text = "Unable to disambiguate. Try google.";
                $found = false;
            } elseif ($text) {
                $text = strip_tags($text);
                //$text = str_replace("(", "", $text);
                //$text = str_replace(")", "", $text);
                // college football at Connecticut.[1]
                $text = preg_replace("~&#91;[0-9]+&#93;~", "", $text);
                $wrongLeague = \App\Site::inst('WRONG_LEAGUE');
                // is an American football tight end for the New York Guardians of the XFL
                // is an American football linebacker and safety for the XFL's Team 9 practice squad
                $isWrongLeague = preg_match("~ is (a|an) [^\.]+ for the [^\.]*$wrongLeague~is", $text);
                if ($wrongLeague && $isWrongLeague) {
                    $text = preg_replace("~$wrongLeague~is", "<b>\\0</b>", $text);
                    $text = "<b>Appears to now be in a different league. Automatically set to Deactivated.</b><br>" . $text;
                    $found = false;
                    $deactivate = 1;
                } elseif (preg_match("~is an? free agent|is currently an? free agent~is", $text)) {
                    $text = preg_replace("~free agent~i", "<b>\\0</b>", $text);
                    $team = "Free Agent";
                    $found = true;
                    $deactivate = 1;
                } elseif (preg_match("~retired|former ~i", $text) && !preg_match("~is the [^\.]+ of former~", $text)) {
                // do NOT match 'He is the son of former NBA player Wes Matthews'
                // but what about 'is a former American football guard who played 11 seasons for the Pittsburgh Steelers of the National Football League NFL. He is the brother of former Rams'
                    $team = "Retired";
                    $text = preg_replace("~retired|former~i", "<b>\\0</b>", $text);
                    $found = true;
                    $deactivate = 1;
                } else {
                    if (\App\Site::inst('TEAMS')) {
                        $text = preg_replace("~ " . \App\Site::inst('TEAMS') . "~is", "<b>\\0</b>", $text);
                        $teams = \App\Site::inst('TEAMS');
                        preg_match("~$teams~is", $text, $arr);
                        if (isset($arr[0])) {
                            $found = true;
                            $team = $arr[0];
                        } else {
                            $found = false;
                            $text = "Unable to find result in Wikipedia. Try Google. ";
                        }
                    } else {
                        $textReplaced = preg_replace("~ is (a|an) .*? for the [^\.]+ of the [^\.]+\.~is", "<b>\\0</b>", $text);
                        if ($textReplaced == $text) {
                            $text = "Unable to find result in Wikipedia. Try Google. ";
                            $found = false;
                        } else {
                            $found = true;
                        }
                    }
                }
            }
            if (!$found) {
                $deactivate = 1;
            }

        }
        else if (\App\Site::inst('SITEKEY') == 'uscongress') {
            $team = '';
            if (preg_match("~member of the Democratic|is a Democrat|of the Democratic Party~is", $text)) {
                $team = 'Democrat';
            } else if (preg_match("~member of the Republican|is a Republican|of the Republican Party~is", $text)) {
                $team = 'Republican';
            }
            // Front end is set up to use the client side form to update one category, not two, so
            // doing this second category here.
            $congress = '';
            if (preg_match("~U.S. Representative|United States Representative|United States House of Representatives~is", $text)){
                $congress = 'House of Representatives';
            } else if (preg_match("~Senate|Senator~", $text)) {
                $congress = 'Senate';
            }
            $r = \DB::table('cats')->select('id')->where('title', '=', $congress)->pluck('id')->toArray();
            if (!empty($r) && !empty($r[0])) {
                $congressCatsId = $r[0];
                $itemsCatsModel = new \App\Models\ItemsCats();
                $r = $itemsCatsModel->where('items_id', '=', $items->id)->where('cats_id', '=', $congressCatsId);
                if ($r->count() == 0) {
                    $itemsCatsModel->items_id = $items->id;
                    $itemsCatsModel->cats_id = $congressCatsId;
                    $itemsCatsModel->save();
                }
            }
        }

        if ($found == true) {
            $wikipediaModel = new WikipediaBlvd();
            $wikipediaModel->items_id = $items->id;
            $wikipediaModel->url = $wikiUrl;
            $wikipediaModel->description = $text;
            $eloqBuildObj = $wikipediaModel->where('items_id', '=', $items->id);
            if ($eloqBuildObj->count()) {
                // This does not save description even when it has changed
                //$wikipediaModel->update();
                $wikipediaModel->where('items_id', $items->id)
                    ->update(['description' => $text, 'url' => $wikiUrl]);
            } else {
                $wikipediaModel->save();
            }
        }

        $jsonArr = [];
        $jsonArr['text'] = $text;
        $jsonArr['error_msg'] = $errorMsg;
        $jsonArr['team'] = trim($team);
        $jsonArr['cats_id'] = 0;
        $jsonArr['deactivate'] = $deactivate;
        $jsonArr['url'] = $found ? $wikiUrl : '';
        if ($team) {
            $r = \DB::table('cats')->select('id')->where('title', '=', $team)->pluck('id')->toArray();
            if (!empty($r) && !empty($r[0])) {
                $jsonArr['cats_id'] = $r[0];
            }
        }
        $jsonStr = json_encode($jsonArr);
        return $jsonStr;

    }

    private function getWikiExtract($searchTerm) {

        $qb = new \Wikipedia('en');
        $pageObj = $qb->page($searchTerm);
        $returnObj = new \stdClass();
        $returnObj->pageObj = $pageObj;
        if ($pageObj->isMissing()) {
            return $returnObj;
        }

        $pageSectionsArr = $pageObj->getSections()->toArray();
        $extract = $pageSectionsArr[0]->getBody();
        $returnObj->extract = $extract;

        return $returnObj;

    }

}
