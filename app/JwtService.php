<?php

namespace App;

use Carbon\Carbon;
use App\JwtRefreshService;

class JwtService
{
    protected $jwtRefreshService;
    protected $accessTokenRepository;

    public function __construct(
        JwtRefreshService $jwtRefreshService,
        AccessTokenRepository $accessTokenRepository
    ) {
        $this->jwtRefreshService = $jwtRefreshService;
        $this->accessTokenRepository = $accessTokenRepository;
    }

    /*
    public function isLoginSessionExpired($token)
    {
        $maxTime = $this->getMaxExpiryTime($token);
        $tokenExpiryTime = $this->getTokenExpiryTime();

        return $tokenExpiryTime->lessThan($maxTime);
    } */

    public function isLoginSessionExpired($token)
    {
        $maxTime = $this->getMaxExpiryTime($token);
        $currentTime = Carbon::now();

        return $currentTime->lessThan($maxTime);
    }

    public function getLoginTimeRemaining($token)
    {
        $maxTime = $this->getMaxExpiryTime($token);
        $currentTime = Carbon::now();

        return $currentTime->diffInMinutes($maxTime);
    }

    public function setExpiryTime($refreshToken)
    {
        $sessionTimeLeft = $this->getLoginTimeRemaining($refreshToken);
        $tokenExpiry = config('app.tokenExpiry');

        if ($sessionTimeLeft > 0 && $sessionTimeLeft < $tokenExpiry) {
            $this->accessTokenRepository->setTokenExpiryTimes($sessionTimeLeft);
        }
    }

    protected function getMaxExpiryTime($token)
    {
        $refreshToken = $this->jwtRefreshService->getRefreshToken($token);
        $accessToken = $this->accessTokenRepository->find($refreshToken->access_token_id);
        $firstAccessToken = $this->accessTokenRepository->getFirstToken($accessToken->user_id);

        $maxExpiry = config('app.maxExpiry');

        $maxTime = Carbon::createFromFormat('Y-m-d H:i:s', $firstAccessToken->created_at);
        $maxTime->addMinutes($maxExpiry);

        return $maxTime;
    }

    protected function getTokenExpiryTime()
    {
        $tokenExpiry = config('app.tokenExpiry');

        $tokenExpiryTime = Carbon::now();
        $tokenExpiryTime->addMinutes($tokenExpiry);

        return $tokenExpiryTime;
    }

    public function isRefreshValid($token)
    {
        $refreshToken = $this->jwtRefreshService->getRefreshToken($token);
        $expiryTime = $refreshToken->expires_at;

        $refreshStartTime = $this->jwtRefreshService->getRefreshStartTime($refreshToken);

        $currentTime = Carbon::now();

        return $currentTime->between($refreshStartTime, $expiryTime);
    }

    public function clearTokens($userId)
    {
        $this->accessTokenRepository->clearTokens($userId);
    }
}
