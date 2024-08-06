<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Employee;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class KeycloakController extends Controller
{

    // ================ login with keycloak
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $params = array_merge(
                $this->getKeycloakParams([
                    'grant_type' => 'password',
                    'username' => $request->input('email'),
                    'password' => $request->input('password'),
                ])
            );

            $response = Http::asForm()->post($this->getKeycloakUrl('token'), $params);

            if ($response->failed()) {
                $status = $response->status();
                $message = $status === 401 ? 'Invalid email or password' : 'Bad request';
                return response()->json(['message' => $message], $status);
            }

            $tokenData = $response->json();
            $accessToken = $tokenData['access_token'];

            $params = $this->getKeycloakParams(['token' => $accessToken]);

            $introspectionResponse = Http::asForm()->post($this->getKeycloakUrl('token/introspect'), $params);

            if ($introspectionResponse->failed()) {
                return response()->json(['message' => 'Failed to retrieve user info from Keycloak'], 500);
            }

            $userInfo = $introspectionResponse->json();

            if (!isset($userInfo['sub'])) {
                return response()->json(['message' => 'Token is inactive or invalid'], 401);
            }
            
            $email = $userInfo['email'];
            $name = $userInfo['username'];
            $roles = $userInfo['realm_access']['roles'] ?? [];
            $firstRole = !empty($roles) ? $roles[0] : null;

            $employee = Employee::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'role' => $firstRole,
                    'password' => Hash::make($request->input('password')),
                ]
            );

            return response()->json(['tokenData' => $tokenData, 'user' => $employee]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Internal server error: ' . $e->getMessage()], 500);
        }
    }


    // ================ get all user in Kecloak
    public function getAllUsers(Request $request)
    {
        // Extract the Bearer token from the Authorization header
        $token = $request->bearerToken();
        
        if (!$token) {
            return response()->json(['message' => 'Authorization token not provided'], 401);
        }

        try {
            // Query Keycloak for users
            $response = Http::withToken($token)->get(env('KEYCLOAK_SERVER') .'/admin/realms/'. env('KEYCLOAK_REALM') . '/users');
            
            // Check if the response was successful
            if (!$response->successful()){
                return response()->json(['message' => 'Failed to fetch users'], $response->status());
            }
            return response()->json(['allUsers' => $response->json()]);
            
        } catch (\Exception $e) {
            // Handle exceptions
            return response()->json(['message' => 'Internal server error'], 500);
        }
    }

    // ================ take refresh_token to get access_token
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

    // ================ Get user data by keycloak
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

    // ================ Logout 
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