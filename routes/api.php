<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DailySaturationController;

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

Route::get('/', function () {
    return ['msg' => 'Welcome to Monstre API'];
});

//API route for register new user
Route::post('/register', [App\Http\Controllers\Api\AuthController::class, 'register']);
//API route for login user
Route::post('/login', [App\Http\Controllers\Api\AuthController::class, 'login']);

//Protecting Routes
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('/profile', function () {
        return auth()->user();
    });

    // API route for logout user
    Route::post('/logout', [App\Http\Controllers\Api\AuthController::class, 'logout']);

    // API route for update user's personality
    Route::put('/update-personality', [App\Http\Controllers\Api\UserController::class, 'updatePersonality']);

    // API route for update user's avatar
    Route::post('/update-avatar', [App\Http\Controllers\Api\UserController::class, 'updateAvatar']);

    Route::group(['prefix' => 'saturation'], function () {
        // API route for set today's saturation
        Route::post('/', [DailySaturationController::class, 'setTodaySaturation']);
        // API route for get today's saturation
        Route::get('/', [DailySaturationController::class, 'getTodaySaturation']);
        // API route for get week's saturation
        Route::get('/get-week', [DailySaturationController::class, 'getWeekSaturation']);
        // API route for get month's saturation
        Route::get('/get-month', [DailySaturationController::class, 'getMonthSaturation']);
        // API route for get year's saturation
        Route::get('/get-year', [DailySaturationController::class, 'getYearSaturation']);
        // API route to get full week saturation
        Route::get('/get-full-week', [DailySaturationController::class, 'getFullWeekSaturation']);
        // API route to get full month saturation
        Route::get('/get-full-month', [DailySaturationController::class, 'getFullMonthSaturation']);
        // API route to get full year saturation
        Route::get('/get-full-year', [DailySaturationController::class, 'getFullYearSaturation']);
    });
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
