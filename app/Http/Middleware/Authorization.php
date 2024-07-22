<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Http;

class Authorization
{

    public function handle($request, Closure $next, $roles)
    {
        // Request Bearer Token to Authorization 
        $token = $request->bearerToken();

        if (!$token) {
            abort(403, 'Unauthorized action.');
        }

        $response = $this->introspectToken($token);

        if ($response->successful()) {
            $rolesArray = explode(',', $roles);
            $tokenData = $response->json();
            if ($this->hasAnyRole($tokenData, $rolesArray)) {
                return $next($request);
            }
        }

        return response()->json(['message'=> 'Unauthorized action.'], 401);
    }

    // Introspect to get the User in Keycloak
    private function introspectToken($token)
    {
        $params = [
            'token' => $token,
            'client_id' => env('KEYCLOAK_CLIENT_ID'),
            'client_secret' => env('KEYCLOAK_CLIENT_SECRET'),
        ];

        $url = env('KEYCLOAK_SERVER') . '/realms/' . env('KEYCLOAK_REALM') . '/protocol/openid-connect/token/introspect';
        return Http::asForm()->post($url, $params);
    }

    // Get the User roles
    private function hasAnyRole($tokenData, $roles)
    {
        $userRoles = $tokenData['realm_access']['roles'] ?? [];
        return !empty(array_intersect($roles, $userRoles));
    }
}
