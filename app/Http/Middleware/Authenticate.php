<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Http;

class Authenticate
{

    public function handle(Request $request, Closure $next): Response
    {
        // Get the token from the Authorization header
        $token = $request->header('Authorization');

        if (!$token || strpos($token, 'Bearer ') !== 0) {
            return response()->json(['message' => 'Unauthenticated. Please log in.'], 401);
        }

        $token = substr($token, 7); // Remove "Bearer " prefix

        // Introspect the token using Keycloak
        $response = Http::asForm()->post($this->getKeycloakUrl('token/introspect'), [
            'client_id' => env('KEYCLOAK_CLIENT_ID'),
            'client_secret' => env('KEYCLOAK_CLIENT_SECRET'),
            'token' => $token,
        ]);

        if ($response->failed() || !$response->json()['active']) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        return $next($request);
    }

    private function getKeycloakUrl($endpoint)
    {
        return env('KEYCLOAK_SERVER') . '/realms/' . env('KEYCLOAK_REALM') . '/protocol/openid-connect/' . $endpoint;
    }
}
