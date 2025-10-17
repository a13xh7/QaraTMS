<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ThemeController;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// Theme preference routes
Route::middleware('web')->group(function () {
    Route::post('/user/theme-preference', [ThemeController::class, 'updatePreference'])->name('api.theme.update');
    Route::get('/user/theme-preference', [ThemeController::class, 'getPreference'])->name('api.theme.get');
});
