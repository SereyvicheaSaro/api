<?php

namespace App\Http\Controllers;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    // Define URL for the redirection.
    protected function getKeycloakUrl($endpoint) {   
        // Url of api that will redirect.
        return env('KEYCLOAK_SERVER') . '/realms/' . env('KEYCLOAK_REALM') . '/protocol/openid-connect/' . $endpoint;
    }

    // Define Params for the client.
    protected function getKeycloakParams($params = []) {
        return array_merge([
            'client_id' => env('KEYCLOAK_CLIENT_ID'),
            'client_secret' => env('KEYCLOAK_CLIENT_SECRET'),
        ], $params);
    }
}
