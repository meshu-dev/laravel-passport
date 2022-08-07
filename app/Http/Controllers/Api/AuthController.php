<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 

use App\User;

use App\OAuthService;
use App\JwtService;

class AuthController extends Controller
{
    protected $oAuthService;
    protected $jwtService;

    public function __construct(
        OAuthService $oAuthService,
        JwtService $jwtService
    ) {
        $this->oAuthService = $oAuthService;
        $this->jwtService = $jwtService;
    }

    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|max:55',
            'email' => 'email|required|unique:users',
            'password' => 'required|confirmed'
        ]);

        $validatedData['password'] = bcrypt($request->password);

        $user = User::create($validatedData);

        $authToken = $user->createToken('authToken');
        $accessToken = $authToken->accessToken;

        return response(['user' => $user, 'access_token' => $accessToken]);
    }

    public function login(Request $request)
    {
        $authParams = [
            'email' => $request->email,
            'password' => $request->password
        ];

        if (Auth::attempt($authParams)) {
            $user = User::where('email', $request->email)->first();
            $this->jwtService->clearTokens($user->id);

            return $this->oAuthService->getTokenAndRefreshToken(
                $request->email,
                $request->password
            );
        }
        else {
            return response()->json(['error' => 'Unauthorised'], 401);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    public function refreshToken(Request $request)
    {
        $refreshToken = $request->header('token');

        $isSessionValid = $this->jwtService->isLoginSessionExpired($refreshToken);
        $isRefreshValid = $this->jwtService->isRefreshValid($refreshToken);

        $responseData = ['error' => 'An error occurred trying to refresh token. Please try again later'];
        $statusCode = 401;

        if ($isSessionValid === true && $isRefreshValid === true) {
            $this->jwtService->setExpiryTime($refreshToken);

            try {
                return $this->oAuthService->refreshToken($refreshToken);
            } catch (\Exception $e) {
                $responseData['error'] = 'There was an error when trying to refresh token';
            }
        } elseif ($isSessionValid === false) {
            $responseData['error'] = 'Access token couldn\'t be refreshed as the login session has or will expire soon';
        } elseif ($isRefreshValid === false) {
            $responseData['error'] = 'Access token couldn\'t be refreshed as it\'s not close to expiry time';
        }
        return response()->json($responseData, $statusCode);
    }

    public function test()
    {
        return response()->json(['data'=> (new \DateTime())->format('Y-m-d H:i:s')]);
    }
}
