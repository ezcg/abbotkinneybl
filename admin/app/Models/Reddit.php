<?php

namespace App\Models;
use Illuminate\Support\Facades\Log;

use Illuminate\Database\Eloquent\Model;

class Reddit extends Model
{

    protected $table = 'reddit';

    protected $fillable = ['id', 'post_id', 'subreddit', 'permalink', 'url', 'text', 'title', 'thumbnail', 'num_comments', 'created_at'];

    public function saveFeed($feedArr) {

        $postIdArr = [];
        foreach($feedArr as $key => $obj) {
            $postIdArr[] = $obj->id;
        }
        if (count($postIdArr) == 0) {
            return;
        }
        $r = Reddit::select('post_id')->whereIn("post_id", $postIdArr)->pluck("post_id");
        if ($r && count($r)) {
            foreach($feedArr as $key => $obj) {
                foreach($r as $dbPostId) {
                    if ($dbPostId == $obj->id || $obj->ups < \App\Site::inst('MIN_REDDIT_UPVOTES')) {
                        unset($feedArr[$key]);
                    }
                }
            }
        }

        foreach($feedArr as $key =>  $obj) {
            if ($obj->is_self) {
                // allow self posts?
            }

            $r = new Reddit();
            $r->title = \App\Models\Utility::cleanText($obj->title);
            if (empty($obj->title) || empty($r->title)) {
                Log::error(__METHOD__ . " " . __LINE__ . " missing title for " . json_encode($obj));
                continue;
            }
            $r->title = iconv("UTF-8", "UTF-8//IGNORE", $r->title);
            $r->thumbnail = '';
            if (substr(trim($obj->thumbnail), 0, 4) == 'http') {
                $r->thumbnail = preg_replace("~https?:~", "", trim($obj->thumbnail));
            }
            $r->url = preg_replace("~https?:~", "", $obj->url);
            $r->permalink = "//www.reddit.com" . $obj->permalink;
            $r->num_comments = $obj->num_comments;
            $r->post_id = $obj->id;
            $r->subreddit = $obj->subreddit;
            $r->text = '';
            $r->created_at = $obj->created;
            $r->save();

        }

    }
    /*
     * Convert short urls to full, add hyperlinks to @ and #, convert smart quotes, etc
     */
    public function convertFeedToSocialMedia()
    {

        $r = $this->getUnconvertedFeed();
        foreach($r as $redditDBObj) {
            $sm = SocialMedia::where('source_id', $redditDBObj->post_id)->first();
            if (is_null($sm)) {
                $sm = new \App\Models\SocialMedia;
            }
            // the title is the text
            $sm->text = $this->buildText($redditDBObj);
            $sm->source_user_id = $redditDBObj->subreddit;
            $sm->source_id = $redditDBObj->post_id;
            $sm->username = $redditDBObj->subreddit;
            $sm->site = 'reddit.com';
            //$sm->link = $redditDBObj->url;
            $sm->link = $redditDBObj->permalink;
            $sm->created_at = $redditDBObj->created_at;
            $sm->save();

        }

    }

    private function buildText($obj) {
        $text = '';
        $linkToArticle = "<a target='_blank' href='" . $obj->url . "'>";
        $host = parse_url($obj->url, PHP_URL_HOST);
        $host = str_replace("www.", "", $host);
        if (!empty($obj->thumbnail) && substr($obj->thumbnail, 0, 2) == '//') {
            $thumb = "<img src='" . $obj->thumbnail . "' class='socialMediaThumb'>";
            $text.= $linkToArticle . $thumb . "</a>";
        }
        $text.= trim($obj->title);// 'title' is topic of reddit thread

        // TODO Hold off on num comments link for now
        // "<a class='numComments' target='_blank' href='" . $obj->permalink . "'>comments: " . $obj->num_comments . "</a>";

        // Don't offer offsite link if link is to reddit. The link already lives in reddit icon
        if (!preg_match("~reddit.com~i", $host) && !preg_match("~redd.it~i", $host) ) {
            $text.= $linkToArticle = " <a class='offsiteLink' target='_blank' href='" . $obj->url . "'>$host</a>";
        }
        return $text;
    }

    private function getUnconvertedFeed()
    {
        $q = "SELECT reddit.* FROM reddit 
                LEFT JOIN social_media ON social_media.source_id = reddit.post_id  AND social_media.site = 'reddit.com' 
                WHERE social_media.id is null";
        $r = \DB::select($q);
        return $r;
    }


}

/*
 *
 *

$feedArr[0]->:

https://reddit.com/r/politics/top/.jsonhttps://reddit.com/r/Political_Revolution/top/.jsonhttps://reddit.com/r/PoliticalHumor/top/.jsonhttps://reddit.com/r/SandersForPresident/top/.jsonhttps://reddit.com/r/AskThe_Donald/top/.json<pre>stdClass Object
(
    [approved_at_utc] =>
    [subreddit] => politics
    [selftext] =>
    [author_fullname] => t2_2gqlbsh9
    [saved] =>
    [mod_reason_title] =>
    [gilded] => 2
    [clicked] =>
    [title] => Senate votes 100-0 to release Trump whistleblower complaint
    [link_flair_richtext] => Array
        (
        )

    [subreddit_name_prefixed] => r/politics
    [hidden] =>
    [pwls] => 6
    [link_flair_css_class] =>
    [downs] => 0
    [thumbnail_height] => 93
    [hide_score] =>
    [name] => t3_d8tvir
    [quarantine] =>
    [link_flair_text_color] => dark
    [author_flair_background_color] =>
    [subreddit_type] => public
    [ups] => 46165
    [total_awards_received] => 2
    [media_embed] => stdClass Object
        (
        )

    [thumbnail_width] => 140
    [author_flair_template_id] =>
    [is_original_content] =>
    [user_reports] => Array
        (
        )

    [secure_media] =>
    [is_reddit_media_domain] =>
    [is_meta] =>
    [category] =>
    [secure_media_embed] => stdClass Object
        (
        )

    [link_flair_text] =>
    [can_mod_post] =>
    [score] => 46165
    [approved_by] =>
    [thumbnail] => https://b.thumbs.redditmedia.com/JS40VOncH5v8pcm-oKv5mk_NoCcDCC8i7ehCOE7DK-g.jpg
    [edited] =>
    [author_flair_css_class] =>
    [steward_reports] => Array
        (
        )

    [author_flair_richtext] => Array
        (
            [0] => stdClass Object
                (
                    [e] => text
                    [t] => ✔ Verified
                )

        )

    [gildings] => stdClass Object
        (
            [gid_2] => 2
        )

    [post_hint] => link
    [content_categories] =>
    [is_self] =>
    [mod_note] =>
    [created] => 1569389865
    [link_flair_type] => text
    [wls] => 6
    [banned_by] =>
    [author_flair_type] => richtext
    [domain] => theweek.com
    [allow_live_comments] => 1
    [selftext_html] =>
    [likes] =>
    [suggested_sort] =>
    [banned_at_utc] =>
    [view_count] =>
    [archived] =>
    [no_follow] =>
    [is_crosspostable] =>
    [pinned] =>
    [over_18] =>
    [preview] => stdClass Object
        (
            [images] => Array
                (
                    [0] => stdClass Object
                        (
                            [source] => stdClass Object
                                (
                                    [url] => https://external-preview.redd.it/qo_QuKdW6cJyICNMu1HhCFVLAYj7Jqqcgt3FCMJh4wU.jpg?auto=webp&amp;s=7ff9b4fc3644afa35bd9f08c75c06b08af541521
                                    [width] => 840
                                    [height] => 560
                                )

                            [resolutions] => Array
                                (
                                    [0] => stdClass Object
                                        (
                                            [url] => https://external-preview.redd.it/qo_QuKdW6cJyICNMu1HhCFVLAYj7Jqqcgt3FCMJh4wU.jpg?width=108&amp;crop=smart&amp;auto=webp&amp;s=2c4e030643d166993f42565c81b9fe398a7c7a96
                                            [width] => 108
                                            [height] => 72
                                        )

                                    [1] => stdClass Object
                                        (
                                            [url] => https://external-preview.redd.it/qo_QuKdW6cJyICNMu1HhCFVLAYj7Jqqcgt3FCMJh4wU.jpg?width=216&amp;crop=smart&amp;auto=webp&amp;s=209cd881f9ae15d3f0cd44b98fb288c527cf5cc8
                                            [width] => 216
                                            [height] => 144
                                        )

                                    [2] => stdClass Object
                                        (
                                            [url] => https://external-preview.redd.it/qo_QuKdW6cJyICNMu1HhCFVLAYj7Jqqcgt3FCMJh4wU.jpg?width=320&amp;crop=smart&amp;auto=webp&amp;s=f146e5beb69f4e3eab24b1502e4db45dbe64a64e
                                            [width] => 320
                                            [height] => 213
                                        )

                                    [3] => stdClass Object
                                        (
                                            [url] => https://external-preview.redd.it/qo_QuKdW6cJyICNMu1HhCFVLAYj7Jqqcgt3FCMJh4wU.jpg?width=640&amp;crop=smart&amp;auto=webp&amp;s=e4bd2f361bbb1237a210eaffe64804bef0d9e83e
                                            [width] => 640
                                            [height] => 426
                                        )

                                )

                            [variants] => stdClass Object
                                (
                                )

                            [id] => JkXFY-iJJRbwdNSpYnJgKgYy4CejAu-K9wtWz5WtfPk
                        )

                )

            [enabled] =>
        )

    [all_awardings] => Array
        (
            [0] => stdClass Object
                (
                    [count] => 2
                    [is_enabled] => 1
                    [subreddit_id] =>
                    [description] => Gives the author a week of Reddit Premium, %{coin_symbol}100 Coins to do with as they please, and shows a Gold Award.
                    [end_date] =>
                    [coin_reward] => 100
                    [icon_url] => https://www.redditstatic.com/gold/awards/icon/gold_512.png
                    [days_of_premium] => 7
                    [id] => gid_2
                    [icon_height] => 512
                    [resized_icons] => Array
                        (
                            [0] => stdClass Object
                                (
                                    [url] => https://www.redditstatic.com/gold/awards/icon/gold_16.png
                                    [width] => 16
                                    [height] => 16
                                )

                            [1] => stdClass Object
                                (
                                    [url] => https://www.redditstatic.com/gold/awards/icon/gold_32.png
                                    [width] => 32
                                    [height] => 32
                                )

                            [2] => stdClass Object
                                (
                                    [url] => https://www.redditstatic.com/gold/awards/icon/gold_48.png
                                    [width] => 48
                                    [height] => 48
                                )

                            [3] => stdClass Object
                                (
                                    [url] => https://www.redditstatic.com/gold/awards/icon/gold_64.png
                                    [width] => 64
                                    [height] => 64
                                )

                            [4] => stdClass Object
                                (
                                    [url] => https://www.redditstatic.com/gold/awards/icon/gold_128.png
                                    [width] => 128
                                    [height] => 128
                                )

                        )

                    [days_of_drip_extension] => 0
                    [award_type] => global
                    [start_date] =>
                    [coin_price] => 500
                    [icon_width] => 512
                    [subreddit_coin_reward] => 0
                    [name] => Gold
                )

        )

    [awarders] => Array
        (
        )

    [media_only] =>
    [can_gild] =>
    [spoiler] =>
    [locked] =>
    [author_flair_text] => ✔ Verified
    [visited] =>
    [num_reports] =>
    [distinguished] =>
    [subreddit_id] => t5_2cneq
    [mod_reason_by] =>
    [removal_reason] =>
    [link_flair_background_color] =>
    [id] => d8tvir
    [is_robot_indexable] => 1
    [report_reasons] =>
    [author] => TheWeekMag
    [discussion_type] =>
    [num_comments] => 2624
    [send_replies] => 1
    [whitelist_status] => all_ads
    [contest_mode] =>
    [mod_reports] => Array
        (
        )

    [author_patreon_flair] =>
    [author_flair_text_color] => dark
    [permalink] => /r/politics/comments/d8tvir/senate_votes_1000_to_release_trump_whistleblower/
    [parent_whitelist_status] => all_ads
    [stickied] =>
    [url] => https://theweek.com/speedreads/867423/senate-votes-1000-release-trump-whistleblower-complaint
    [subreddit_subscribers] => 5422802
    [created_utc] => 1569361065
    [num_crossposts] => 10
    [media] =>
    [is_video] =>
)

 */
