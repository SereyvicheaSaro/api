<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use App\Models\Visitor;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class VisitorController extends Controller
{
    public function register(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'name'          => 'required|string|max:255',
            'purpose'       => 'required|string|max:255',
            'contact'       => 'required|string|max:255',
            'entry_time'    => 'required|date_format:H:i:s',
            'exit_time'     => 'required|date_format:H:i:s',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $visitor = Visitor::create([
            'name'          => $req->name,
            'purpose'       => $req->purpose,
            'contact'       => $req->contact,
            'entry_time'    => $req->entry_time,
            'exit_time'     => $req->exit_time,
            'approver'      => null,
            'status'        => 'pending',
        ]);

        return response()->json($visitor, 201);
    }

    public function getAllVisitor(Request $req)
    {
        $name = $req->input('name');
        if ($name) {
            $visitors = Visitor::where('name', 'like', '%' . $name . '%')->get();
            return response()->json($visitors, 200);
        } 

        $visitors = Visitor::all();
        return response()->json($visitors, 200); 
    }

    public function update(Request $req, $id)
    {
        // Find the visitor by ID or fail
        $visitor = Visitor::findOrFail($id);

        // Validate the incoming request
        $validator = Validator::make($req->all(), [
            'name'          => 'sometimes|string|max:255',
            'purpose'       => 'sometimes|string|max:255',
            'contact'       => 'sometimes|string|max:255',
            'entry_time'    => 'sometimes|date_format:H:i:s',
            'exit_time'     => 'sometimes|date_format:H:i:s|after:entry_time',
            'status'        => 'sometimes|in:pending,approved,rejected',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

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

            // Prepare data for update
            $validatedData = $validator->validated();
            $validatedData['approver'] = $employee->name; // Set approver name

            // Update visitor record
            $visitor->update($validatedData);

            return response()->json([
                'message' => 'Visitor updated successfully',
                'visitor' => $visitor,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Internal server error: ' . $e->getMessage()], 500);
        }
    }


    public function show($id)
    {
        $visitor = Visitor::findOrFail($id);
        return response()->json(['visitor' => $visitor], 200);
    }

    public function store(Request $request)
    {
        $id = $request->input('id');

        // Process the ID or log it
        // For example, you might want to log it or update the visitor status in your database

        return response()->json(['message' => 'QR code data received', 'id' => $id]);
    }
}
