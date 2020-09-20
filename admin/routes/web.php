<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
Route::get('/', 'WelcomeController@welcome')->name('welcome');

// category editor
Route::get('/cats','CatsController@index')->name('cats.index');
Route::post('/cats/hashtagsignore','CatsController@hashtagsignore')->name('cats.hashtagsignore');
Route::get('/cats/hashtagsignoredelete','CatsController@hashtagsignoredelete')->name('cats.hashtagsignoredelete');
Route::get('/cats/hashtags','CatsController@hashtags')->name('cats.hashtags');
Route::get('/cats/depth','CatsController@depth')->name('cats.depth');
Route::get('/cats/images','CatsController@images')->name('cats.images');
Route::post('/cats/{cats}/images', 'CatsController@imagesstore')->name('cats.imagesstore');
Route::get('/cats/{cats}/images', 'CatsController@imagesdestroy')->name('cats.imagesdelete');
Route::post('/cats', 'CatsController@store')->name('cats.store');
Route::post('/cats/{cats}/update', 'CatsController@update')->name('cats.update');
Route::post('/cats/updatedepth', 'CatsController@updatedepth')->name('cats.updatedepth');
Route::post('/cats/{cats}/updaterelationship', 'CatsController@updaterelationship')->name('cats.updaterelationship');
Route::get('/cats/{cats}', 'CatsController@destroy')->name('cats.delete');
Route::post('/cats/{cats}/updatedeactivated', 'CatsController@updatedeactivated')->name('cats.updatedeactivated');


// items
Route::get('/items','ItemsController@index')->name('items.index');
Route::post('/items', 'ItemsController@store')->name('items.store');
Route::post('/items/{items}/update', 'ItemsController@update')->name('items.update');
Route::get('/items/{items}', 'ItemsController@destroy')->name('items.delete');
Route::post('/items/{items}/updatecolumn', 'ItemsController@updatecolumn')->name('items.updatecolumn');
Route::post('/items/{items}/updatedeactivated', 'ItemsController@updatedeactivated')->name('items.updatedeactivated');


// links
Route::get('/links','LinksController@index')->name('links.index');
Route::post('/links', 'LinksController@store')->name('links.store');
Route::get('/links/{links}/edit', 'LinksController@edit')->name('links.edit');
Route::post('/links/{links}/update', 'LinksController@update')->name('links.update');
Route::get('/links/{links}', 'LinksController@destroy')->name('links.delete');
Route::get('/links/{links}/deactivate', 'LinksController@deactivate')->name('links.deactivate');

// wikipedia
Route::get('/wikipedia/{items}/searchwikipedia', 'WikipediaController@searchwikipedia')->name('wikipedia.searchwikipedia');
Route::get('/wikipedia','WikipediaController@index')->name('wikipedia.index');
Route::get('/wikipedia/create', 'WikipediaController@create')->name('wikipedia.create');
Route::post('/wikipedia', 'WikipediaController@store')->name('wikipedia.store');
Route::get('/wikipedia/{wikipedia}/edit', 'WikipediaController@edit')->name('wikipedia.edit');
Route::post('/wikipedia/{wikipedia}/update', 'WikipediaController@update')->name('wikipedia.update');
Route::get('/wikipedia/{wikipedia}', 'WikipediaController@destroy')->name('wikipedia.delete');
Route::get('/wikipedia/{wikipedia}/deactivate', 'WikipediaController@deactivate')->name('wikipedia.deactivate');

// socialmediaaccounts
Route::get('/socialmediaaccounts/admin','SocialMediaAccountsController@admin')->name('socialmediaaccounts.admin');
Route::get('/socialmediaaccounts/assocall','SocialMediaAccountsController@assocall')->name('socialmediaaccounts.assocall');
Route::get('/socialmediaaccounts/create', 'SocialMediaAccountsController@create')->name('socialmediaaccounts.create');
Route::get('/socialmediaaccounts/edit', 'SocialMediaAccountsController@edit')->name('socialmediaaccounts.edit');
Route::get('/socialmediaaccounts/{SocialMediaAccounts}', 'SocialMediaAccountsController@destroy')->name('socialmediaaccounts.delete');
Route::post('/socialmediaaccounts/{SocialMediaAccounts}/assoc', 'SocialMediaAccountsController@assoc')->name('socialmediaaccounts.assoc');
Route::post('/socialmediaaccounts/{SocialMediaAccounts}/update', 'SocialMediaAccountsController@update')->name('socialmediaaccounts.update');
Route::post('/socialmediaaccounts/{SocialMediaAccounts}/updatecolumn', 'SocialMediaAccountsController@updatecolumn')->name('socialmediaaccounts.updatecolumn');
Route::post('/socialmediaaccounts', 'SocialMediaAccountsController@store')->name('socialmediaaccounts.store');

//contact info
Route::get('/contactinfo','ContactInfoController@index')->name('contactinfo.index');
Route::get('/contactinfo/create', 'ContactInfoController@create')->name('contactinfo.create');
Route::get('/contactinfo/all',    'ContactInfoController@all')->name('contactinfo.all');
Route::post('/contactinfo', 'ContactInfoController@store')->name('contactinfo.store');
Route::get('/contactinfo/{contactInfo}/edit', 'ContactInfoController@edit')->name('contactinfo.edit');
Route::post('/contactinfo/{contactInfo}/update', 'ContactInfoController@update')->name('contactinfo.update');
Route::get('/contactinfo/{contactInfo}', 'ContactInfoController@destroy')->name('contactinfo.delete');

// hours
Route::post('/hours/{hours}/update', 'HoursController@update')->name('hours.update');
Route::post('/hours/{hours}/store', 'HoursController@store')->name('hours.store');
Route::get('/hours/index', 'HoursController@index')->name('hours.index');
Route::get('/hours/all',    'HoursController@all')->name('hours.all');

// social media
Route::get('/socialmedia/deleteunpublishedhashtags', 'SocialMediaController@deleteunpublishedhashtags')->name('socialmedia.deleteunpublishedhashtags');
Route::get('/socialmedia','SocialMediaController@index')->name('socialmedia.index');
Route::get('/socialmedia/visitors','SocialMediaController@visitors')->name('socialmedia.visitors');
Route::get('/socialmedia/{SocialMedia}/edit', 'SocialMediaController@edit')->name('socialmedia.edit');
Route::get('/socialmedia/{SocialMedia}', 'SocialMediaController@destroy')->name('socialmedia.delete');

// reddit
Route::get('/reddit','RedditController@index')->name('reddit.index');
Route::get('/reddit/read','RedditController@read')->name('reddit.read');
Route::get('/reddit/search','RedditController@search')->name('reddit.search');
Route::get('/reddit/create', 'RedditController@create')->name('reddit.create');
Route::post('/reddit', 'RedditController@store')->name('reddit.store');
Route::get('/reddit/{Reddit}/edit', 'RedditController@edit')->name('reddit.edit');
Route::post('/reddit/{Reddit}/update', 'RedditController@update')->name('reddit.update');
Route::get('/reddit/{Reddit}', 'RedditController@destroy')->name('reddit.delete');

// twitter
Route::get('/twitter/getgeosearch', 'TwitterController@getgeosearch')->name('twitter.getgeosearch');
Route::get('/twitter/getgeo', 'TwitterController@getgeo')->name('twitter.getgeo');
Route::get('/twitter/gethashtags', 'TwitterController@gethashtags')->name('twitter.gethashtags');
Route::get('/twitter/getgeoreverse', 'TwitterController@getgeoreverse')->name('twitter.getgeoreverse');
Route::get('/twitter/getfeed', 'TwitterController@getfeed')->name('twitter.getfeed');
Route::get('/twitter/convertfeedtosocialmedia', 'TwitterController@convertfeedtosocialmedia')->name('twitter.convertfeedtosocialmedia');
Route::get('/twitter/getfriends', 'TwitterController@getfriends')->name('twitter.getfriends');
Route::get('/twitter/index', 'TwitterController@index')->name('twitter.index');
Route::get('/twitter/getlistsubscriptions', 'TwitterController@getlistsubscriptions')->name('twitter.getlistsubscriptions');
Route::get('/twitter/getlistmembers', 'TwitterController@getlistmembers')->name('twitter.getlistmembers');
Route::get('/twitter/getlist', 'TwitterController@getlist')->name('twitter.getlist');

// aws
Route::get('/aws/update', 'AWSController@update')->name('aws.update');

// cronjobs write json to s3
Route::get('/writehashtag/items', 'WriteHashtagController@items')->name('writehashtag.items');
Route::get('/writehashtag/category', 'WriteHashtagController@category')->name('writehashtag.category');
Route::resource("writecategoryjson", "WriteCategoryJsonController");
Route::resource("writeitemsjson", "WriteItemsJsonController");


// cronjob for pruning db, maintenance, whatever els
Route::resource("prune", "MaintenanceController");

// yelp
Route::resource('yelp', 'YelpController');

Route::get('login/instagram', 'Auth\LoginController@redirectToInstagramProvider')->name('instagram.login');

Route::get('login/instagram/callback', 'Auth\LoginController@instagramProviderCallback')->name('instagram.login.callback');

