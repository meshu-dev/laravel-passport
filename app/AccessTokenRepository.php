<?php

namespace App;

use Laravel\Passport\TokenRepository;
use Laravel\Passport\Passport;

class AccessTokenRepository extends TokenRepository
{
    public function getFirstToken($userId)
    {
        return Passport::token()
                       ->where('user_id', $userId)
                       ->orderBy('created_at', 'ASC')
                       ->first();
    }

    public function clearTokens($userId)
    {
        return Passport::token()
                       ->where('user_id', $userId)
                       ->delete();
    }

    public function setTokenExpiryTimes($expiryInMinutes)
    {
        $authTokenExpiry = now()->addMinutes($expiryInMinutes);
        $refreshTokenExpiry = now()->addMinutes($expiryInMinutes);

        Passport::tokensExpireIn($authTokenExpiry);
        Passport::refreshTokensExpireIn($refreshTokenExpiry);
        Passport::personalAccessTokensExpireIn($authTokenExpiry);
    }
}
