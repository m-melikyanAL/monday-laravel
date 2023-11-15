<?php

use App\Http\Controllers\Infos;
use App\Http\Controllers\Webhooks;
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

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});



Route::get('/items', [Infos::class, 'getItems']);
Route::patch('/items', [Infos::class, 'updateItems']);
Route::put('/items', [Infos::class, 'updateStatus']);

Route::delete('/items', [Infos::class, 'deleteItems']);


Route::post('/items', [Infos::class, 'createIssue']);

