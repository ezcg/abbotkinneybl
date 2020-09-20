<?php

namespace App\Models;
use Illuminate\Support\Facades\Log;
use \DB;
use \App\Models\SocialMediaAccounts as SocialMediaAccounts;
use Illuminate\Database\Eloquent\Model;

/*
 * Handle twitter account details in this class, handle tweet details in Tweets class
 */
class TwitterBlvd extends Model
{


    public static function saveListMembersToSocialMediaAccounts($twitterUsersObj, $site = 'twitter.com')
    {
        if (is_object($twitterUsersObj) && (!isset($twitterUsersObj->users) || !count($twitterUsersObj->users))) {
            return;
        }
        $resultArr = [];
        $resultArr['avatars_updated'] = [];
        $resultArr['accounts_added'] = [];
        foreach($twitterUsersObj->users as $key => $obj) {
            $r = SocialMediaAccounts::getRowWithSourceUserIdAndSite($obj->id_str, $site);
            if (count($r)) {
                // update avatar
                $r = SocialMediaAccounts::updateAvatarWithSourceUserIdAndSite($obj->profile_image_url, $obj->id, $site);
                if ($r) {
                    $resultArr['avatars_updated'][] = $obj->screen_name;
                }
                continue;
            }

            // add new row into social media accounts
            $smaObj = new SocialMediaAccounts;
            $smaObj->source_user_id = $obj->id_str;
            $smaObj->username = $obj->screen_name;
            $smaObj->description = $obj->description;
            $smaObj->site = $site;
            $smaObj->name = $obj->name;
            // make avatar be //www.domain/image.jpg instead of https://www.domain/image.jpg
            $smaObj->avatar = \App\Models\Utility::removeHTTP($obj->profile_image_url);
            $smaObj->save();

            $resultArr['accounts_added'][] = $smaObj->screen_name;

        }

        return $resultArr;

    }

    // Get friends of main account (not list) and save to social_media_accounts table
    public static function saveFriends()
    {
        $cursor = -1;
        $screenName = env('TWITTER_SCREENNAME');
        $paramArr = [
            'screen_name' => $screenName,
            'skip_status' => true,
            'include_user_entities' => false,
            'cursor' => $cursor,
            'count' => 200
        ];
        do {

            $r = \Twitter::getFriends($paramArr);
            if (isset($r->users)) {
                foreach($r->users as $obj) {
                    $row = SocialMediaAccounts::getSocialMediaAccountWithIdAndSite($obj->id_str, 'twitter.com');
                    if (count($row)) {
                        //Log::warning(__METHOD__ . "already added: " . $obj->screen_name);
                        continue;
                    }
                    Log::warning(__METHOD__ . 'adding: ' . $obj->screen_name);
                    $smaObj = new SocialMediaAccounts;
                    $smaObj->source_user_id = $obj->id_str;
                    $smaObj->username = $obj->screen_name;
                    $smaObj->site = 'twitter.com';
                    $smaObj->name = $obj->name;
                    $smaObj->avatar = \App\Models\Utility::removeHTTP($obj->profile_image_url);
                    $smaObj->save();
                }
            }
        } while($r->next_cursor_str > 0);

    }

}