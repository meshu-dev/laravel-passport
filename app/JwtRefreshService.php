<?php

namespace App;

use Carbon\Carbon;
use Defuse\Crypto\Crypto;
use Laravel\Passport\RefreshTokenRepository;

class JwtRefreshService
{
    protected $refreshTokenRepository;
    // protected $authUrl;

    public function __construct(RefreshTokenRepository $refreshTokenRepository)
    {
        $this->refreshTokenRepository = $refreshTokenRepository;
        // $this->authUrl = 'http://passport.docker';
    }

    public function getRefreshTokenId($token)
    {
        $appKey = env('APP_KEY');
        $encKey = base64_decode(substr($appKey, 7));
        try {
            $crypto = Crypto::decryptWithPassword($token, $encKey);
        } catch (\Exception $exception) {
            return $exception;
        }
        return  json_decode($crypto, true);
    }

    public function getRefreshToken($token)
    {
        $tokenData = $this->getRefreshTokenId($token);

        if (empty($tokenData['refresh_token_id']) === false) {
            $refreshTokenId = $tokenData['refresh_token_id'];
            $refreshToken = $this->refreshTokenRepository->find($refreshTokenId);
            
            return $refreshToken;
        }
        return null;
    }

    public function getRefreshStartTime($refreshToken)
    {
        $refreshTimestamp = $refreshToken->expires_at->format('Y-m-d H:i:s');
        $refreshPeriod = config('app.refreshPeriod');

        $dateTime = Carbon::createFromFormat('Y-m-d H:i:s', $refreshTimestamp);
        $dateTime->subMinutes($refreshPeriod);

        return $dateTime;
    }
}
