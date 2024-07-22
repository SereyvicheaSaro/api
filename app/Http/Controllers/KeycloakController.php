<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class KeycloakController extends Controller
{
    // Define URL for the redirection
    private function getKeycloakUrl($endpoint)
    {
        return env('KEYCLOAK_SERVER') . '/realms/' . env('KEYCLOAK_REALM') . '/protocol/openid-connect/' . $endpoint;
    }

    // Define Params for the client
    private function getKeycloakParams($additionalParams = [])
    {
        return array_merge([
            'client_id' => env('KEYCLOAK_CLIENT_ID'),
            'client_secret' => env('KEYCLOAK_CLIENT_SECRET'),
        ], $additionalParams);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $params = array_merge(
            $this->getKeycloakParams([
                'grant_type' => 'password',
                'username' => $request->input('email'),
                'password' => $request->input('password'),
            ])
        );

        try {
            $response = Http::asForm()->post($this->getKeycloakUrl('token'), $params);

            if ($response->failed()) {
                $status = $response->status();
                $message = $status === 401 ? 'Invalid email or password' : 'Bad request';
                return response()->json(['message' => $message], $status === 401 ? 400 : 400);
            }

            return response()->json(['tokenData' => $response->json()]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Internal server error'], 500);
        }
    }

    public function refresh(Request $request)
    {
        $request->validate([
            'refresh_token' => 'required|string',
        ]);

        $params = array_merge(
            $this->getKeycloakParams([
                'grant_type' => 'refresh_token',
                'refresh_token' => $request->input('refresh_token'),
            ])
        );

        try {
            $response = Http::asForm()->post($this->getKeycloakUrl('token'), $params);

            if ($response->failed()) {
                $status = $response->status();
                $message = $status === 401 ? 'Invalid refresh token' : ($status === 400 ? 'Bad request' : 'An error occurred');
                return response()->json(['message' => $message], $status === 401 || $status === 400 ? 400 : 500);
            }

            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['message' => 'Internal server error: ' . $e->getMessage()], 500);
        }
    }

    public function introspect(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $params = $this->getKeycloakParams(['token' => $request->input('token')]);

        try {
            $response = Http::asForm()->post($this->getKeycloakUrl('token/introspect'), $params);

            if ($response->failed()) {
                throw new \Exception('Introspection failed');
            }

            return response()->json(['userData' => $response->json()]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Introspection error: ' . $e->getMessage()], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $params = $this->getKeycloakParams(['refresh_token' => $request->input('token')]);

            $response = Http::asForm()->post($this->getKeycloakUrl('logout'), $params);

            if ($response->failed()) {
                throw new \Exception('Logout failed');
            }

            return response()->json(['message' => 'Logout successful']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Logout error: ' . $e->getMessage()], 500);
        }
    }
}