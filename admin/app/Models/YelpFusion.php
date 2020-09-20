<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\YelpTrait;
use Illuminate\Support\Facades\Config;


class YelpFusion extends \Neighborhoods\YelpFusion\Yelp
{
    use YelpTrait;

    private $bizLookupEndpoint = 'v3/businesses/';
    private $oauthToken;
    protected $fillable = ['biz_id', 'name', 'description'];

    public function __construct()
    {

        parent::__construct();
        $providerKey = Config::get('services.yelp');
        //$oauthTokenData = $this->getBearerTokenObject($providerKey['client_id'], $providerKey['client_secret']);
        //$this->oauthToken = $oauthTokenData->access_token;
        $this->oauthToken = $providerKey['api_key'];
    }

    /**
     * Get biz using yelp biz id
     *
     * @param $yelpBizId
     * @param $bearerToken
     * @return object
     * @throws Exception
     */
    public function bizlookup($yelpBizId, $bearerToken, $reviews = false)
    {
        $endpoint = $this->bizLookupEndpoint . $yelpBizId;
        if ($reviews) {
            $endpoint.= '/reviews';
        }
        return $this->parseResponse(
            $this->guzzle->get(
                $endpoint,
                [
                    'headers' => [
                        'authorization' => 'Bearer ' . $bearerToken,
                    ]
                ]
            )
        );
    }

    public function getOauthToken()
    {
        return $this->oauthToken;
    }

}
