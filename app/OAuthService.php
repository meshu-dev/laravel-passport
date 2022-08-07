<?php

namespace App;

use GuzzleHttp\Client;
use Laravel\Passport\Client as OClient;

class OAuthService
{
    protected $authUrl;

    public function __construct()
    {
        $this->authUrl = 'http://passport.docker';
    }

    public function getTokenAndRefreshToken(
        $email,
        $password
    ) {
        $params = [
            'grant_type' => 'password',
            'username' => $email,
            'password' => $password
        ];
        return $this->callTokenEndpoint($params);
    }

    public function refreshToken($refreshToken)
    {
        $params = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken
        ];
        return $this->callTokenEndpoint($params);
    }

    protected function callTokenEndpoint($params)
    {
        $oClient = OClient::where('password_client', 1)->first();
        $http = new Client;

        $params['client_id'] = $oClient->id;
        $params['client_secret'] = $oClient->secret;
        $params['scope'] = '*';

        $response = $http->request(
            'POST',
            $this->authUrl . '/oauth/token',
            ['form_params' => $params]
        );

        return json_decode(
            (string) $response->getBody(),
            true
        );
    }
}
