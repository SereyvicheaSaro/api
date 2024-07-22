<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KeycloakController;
use App\Http\Middleware\Authorization;

Route::group([
    'prefix' => 'auth'
], function(){
    Route::post('/login', [KeycloakController::class, 'login']);
    Route::post('/logout', [KeycloakController::class, 'logout']);
    Route::post('/introspect', [KeycloakController::class, 'introspect']);
    Route::post('/refresh', [KeycloakController::class, 'refresh']);
});

Route::get('/admin', function(){
    return response()->json(['message'=>'This is Admin page.'],200);
})->middleware(Authorization::class . ':admin, employee '); // add roles as you need

