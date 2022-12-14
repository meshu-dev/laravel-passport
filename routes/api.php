<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
}); */

Route::post('/register', 'Api\AuthController@register');
Route::post('/login', 'Api\AuthController@login');

Route::get('/test', 'Api\AuthController@test');

Route::post('/refresh', 'Api\AuthController@refreshToken');

Route::group(['middleware' => ['auth:api']], function () {
    Route::apiResource('/ceo', 'Api\CeoController');
    Route::apiResource('/staff', 'Api\StaffController');
    
    Route::post('/logout', 'Api\AuthController@logout');
});
