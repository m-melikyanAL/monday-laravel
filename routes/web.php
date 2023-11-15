<?php


use App\Http\Controllers\Webhooks;
use Illuminate\Support\Facades\Route;



//Route::get('/', function () {
//    return "asd";
//});
//
//Route::get('/items', [Persons::class, 'show']);
//Route::post('/items', [Persons::class, 'insert']);
//
//Route::get('/token', function () {
//    return csrf_token();
//});

//file_put_contents('test.txt','init' );
//Route::post('/',[Webhooks::class, 'webhookListener']);

Route::post('/monday/get_remote_list_options',[Webhooks::class, 'node']);
Route::post('/monday/execute_action',[Webhooks::class, 'node']);
//Route::get('/',[Webhooks::class, 'node']);


