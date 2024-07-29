<?php

use App\Http\Controllers\EmployeeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KeycloakController;
use App\Http\Controllers\VehicleController;
use App\Http\Middleware\Authorization;
use App\Http\Middleware\Authenticate;

Route::group([
    'prefix' => 'auth'
], function(){
    Route::post('/login', [KeycloakController::class, 'login']);
    Route::post('/logout', [KeycloakController::class, 'logout']);
    Route::post('/introspect', [KeycloakController::class, 'introspect'])->name('introspect');
    Route::post('/refresh', [KeycloakController::class, 'refresh']);
    Route::get('/getAllUsers', [KeycloakController::class, 'getAllUsers']);
});

Route::get('/admin', function(){
    return response()->json(['message'=>'This is Admin page.'],200);
})->middleware(Authorization::class . ':employee, user, admin'); // add roles as you need

Route::group([
    'prefix' => 'vehicle',
],function(){
    Route::post('/', [VehicleController::class, 'create']);
    Route::get('/', [VehicleController::class, 'read'])->middleware(Authenticate::class);
    Route::get('/{id}', [VehicleController::class, 'readById'])->middleware(Authenticate::class);
    Route::patch('/{id}', [VehicleController::class, 'update'])->middleware(Authenticate::class)->middleware(Authorization::class . ':admin');
    Route::delete('/{id}', [VehicleController::class, 'delete'])->middleware(Authenticate::class)->middleware(Authorization::class . ':admin');
    Route::post('/approve/{id}', [VehicleController::class, 'approve'])->middleware(Authenticate::class)->middleware(Authorization::class . ':admin');
});

Route::group([
    'prefix' => 'employee',
],function(){
    Route::get('/', [EmployeeController::class, 'getAll'])->middleware(Authenticate::class);
    Route::get('/me', [EmployeeController::class, 'getMe']);
    Route::patch('/{id}', [EmployeeController::class, 'update']);
    
});
