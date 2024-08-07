<?php

$router->options('{any:.*}', function () {
    return response()->json([], 200);
});

$router->get('/', function () use ($router) {
    return $router->app->version();
});

// ============= Auth with Keycloak
$router->group(['prefix' => 'api/auth'], function () use ($router) {
    $router->post('/login',     'KeycloakController@login');
    $router->get('/users',      'KeycloakController@getAllUsers');
    $router->post('/refresh',   'KeycloakController@refresh');
    $router->post('/introspect','KeycloakController@introspect');
    $router->post('/logout',    'KeycloakController@logout');
});

// ============= Employee 
$router->group(['prefix' => 'api/employee'], function () use ($router) {
    $router->get('/', [
        'uses'      => 'EmployeeController@getAll',
        'middleware'=> ['auth', 'role:admin']
    ]);
    $router->get('/me', [
        'uses'      => 'EmployeeController@getMe',
        'middleware'=> 'auth'
    ]);
    $router->post('/{id}', [
        'uses'      => 'EmployeeController@update',
        'middleware'=> 'auth'
    ]);
});

// ============= Visitor
$router->group(['prefix' => 'api/visitor'], function () use ($router) {
    $router->get('/', [
        'uses'      => 'VisitorController@getAllVisitor',
        'middleware'=> 'auth'
    ]);
    $router->post('/', 'VisitorController@register');
    $router->get('/{id}', 'VisitorController@show');
    $router->post('/{id}', [
        'uses'      => 'VisitorController@update',
        'middleware'=> 'auth'
    ]);
});

