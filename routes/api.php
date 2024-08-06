<?php

use App\Http\Controllers\EmployeeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KeycloakController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\VisitorController;
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
    Route::get('/', [EmployeeController::class, 'getAll'])->middleware(Authenticate::class)->middleware(Authorization::class . ':admin');
    Route::get('/me', [EmployeeController::class, 'getMe'])->middleware(Authenticate::class);
    Route::post('/{id}', [EmployeeController::class, 'update'])->middleware(Authenticate::class);
});

Route::group([
    'prefix' => 'visitor',
],function(){
    Route::post('/', [VisitorController::class, 'register']);
    Route::get('/{id}', [VisitorController::class, 'show']);
    Route::get('/', [VisitorController::class, 'getAllVisitor']);
    Route::post('/{id}', [VisitorController::class, 'update']);
    Route::get('/search',[VisitorController::class, 'searchVisitor']);
    Route::post('/scan-qr', [VisitorController::class, 'store']);
});