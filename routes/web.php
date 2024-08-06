<?php

$router->options('{any:.*}', function () {
    return response()->json([], 200);
});

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'api/auth'], function () use ($router) {
    $router->post('/login', 'KeycloakController@login');
    $router->get('/users', 'KeycloakController@getAllUsers');
    $router->post('/refresh', 'KeycloakController@refresh');
    $router->post('/introspect', 'KeycloakController@introspect');
    $router->post('/logout', 'KeycloakController@logout');
});

$router->group(['prefix' => 'api/employee'], function () use ($router) {
    $router->get('/', [
        'uses' => 'EmployeeController@getAll',
        'middleware' => ['auth', 'role:admin']
    ]);
    $router->get('/me', [
        'uses' => 'EmployeeController@getMe',
        'middleware' => 'auth'
    ]);
    $router->post('/{id}', [
        'uses' => 'EmployeeController@update',
        'middleware' => 'auth'
    ]);
});

$router->group(['prefix' => 'api/visitor'], function () use ($router) {
    $router->get('/', 'VisitorController@getAllVisitor');
    $router->post('/', 'VisitorController@register');
    $router->get('/{id}', 'VisitorController@show');
    $router->post('/{id}', 'VisitorController@update');
});

