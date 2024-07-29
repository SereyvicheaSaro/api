<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class EmployeeController extends Controller
{

    // ================ get all users in laravel api
    public function getAll() {
        $employees = Employee::all();
        return response()->json(['Employee list' => $employees], 200);
    }

    // ================ get own user informaion in laravel api
    public function getMe(Request $req){
        try {
            // Extract the Bearer token from the Authorization header
            $access_token = $req->bearerToken();

            if (!$access_token) {
                return response()->json(['message' => 'No access token provided'], 401);
            }

            // Verify and get user info from Keycloak
            $params = $this->getKeycloakParams([
                'token' => $access_token
            ]);

            $introspectionResponse = Http::asForm()->post($this->getKeycloakUrl('token/introspect'), $params);

            if ($introspectionResponse->failed()) {
                return response()->json(['message' => 'Failed to retrieve user info from Keycloak'], 500);
            }

            $userInfo = $introspectionResponse->json();

            // Check if the token is active
            if (!isset($userInfo['sub'])) {
                return response()->json(['message' => 'Token is inactive or invalid'], 401);
            }

            $email = $userInfo['email'];

            // Check if the user exists in the employees table
            $employee = Employee::where('email', $email)->first();

            if (!$employee) {
                return response()->json(['message' => 'User not found'], 404);
            }

            // Return user information
            return response()->json(['user' => $employee]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Internal server error: ' . $e->getMessage()], 500);
        }
    }

    // ================ update user in laravel api by selected user
    public function update(Request $req, $id)
    {
        try {
            $validator = [
                'name'          => 'sometimes|string',
                'phoneNumber'   => 'sometimes|string',
                'avatar'        => 'sometimes|string',
                'bio'           => 'sometimes|string',
            ];

            $req->validate($validator);

            $employee = Employee::findOrFail($id);
            $employee->update($req->only(array_keys($validator)));

            return response()->json([
                'message' => 'Update successful...',
                'employee' => $employee
            ], 200);

        } catch (ValidationException $e) {
            return $this->handleValidationException($e);
        } catch (\Exception $e) {
            return $this->handleUnexpectedException($e);
        }
    }

    // ================ Handle validation errors
    protected function handleValidationException(ValidationException $e)
    {
        return response()->json(['message' => 'Validation Error', 'errors' => $e->errors()], 422);
    }

    // ================ Handle unexpected errors
    protected function handleUnexpectedException(\Exception $e)
    {
        return response()->json(['error' => 'An unexpected error occurred.'], 500);
    }
}
