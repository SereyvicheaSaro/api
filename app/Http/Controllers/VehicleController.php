<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class VehicleController extends Controller
{
    // ================ Create vehicle
    public function create(Request $req)
    {
        try {
            $token = $this->extractTokenFromRequest($req);

            // Validation rules
            $validationRules = [
                'model'        => 'required|string',
                'plate'        => 'required|string|unique:vehicles,plate',
                'expire_date'  => 'required|date',
            ];

            if (!$token) {
                $validationRules['owner'] = 'required|string';
            }

            $req->validate($validationRules);

            // Determine the owner
            $owner = $token ? $this->getOwnerFromToken($token) : $req->input('owner');

            $vehicle = Vehicle::create([
                'model'        => $req->model,
                'plate'        => $req->plate,
                'owner'        => $owner,
                'approver'     => null,
                'status'       => false,
                'issue_date'   => now(),
                'expire_date'  => $req->expire_date,
            ]);

            return response()->json($vehicle, 201);
        } catch (ValidationException $e) {
            return $this->handleValidationException($e);
        } catch (\Exception $e) {
            return $this->handleUnexpectedException($e);
        }
    }

    // ================ View all vehicles or search by plate
    public function read(Request $req)
    {
        try {
            $plate = $req->input('plate');

            $vehicles = $plate
                ? Vehicle::where('plate', 'like', "%$plate%")->get()
                : Vehicle::all();

            return response()->json(['list vehicle' => $vehicles], 200);
        } catch (ValidationException $e) {
            return $this->handleValidationException($e);
        } catch (\Exception $e) {
            return $this->handleUnexpectedException($e);
        }
    }

    // ================ View vehicle by ID
    public function readById($id)
    {
        try {
            $vehicle = Vehicle::findOrFail($id);
            return response()->json($vehicle, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Vehicle not found.'], 404);
        } catch (ValidationException $e) {
            return $this->handleValidationException($e);
        } catch (\Exception $e) {
            return $this->handleUnexpectedException($e);
        }
    }

    // ================ Update vehicle by ID
    public function update(Request $req, $id)
    {
        try {
            $rules = [
                'model'        => 'sometimes|required|string',
                'plate'        => 'sometimes|required|string|unique:vehicles,plate,' . $id,
                'owner'        => 'sometimes|required|string',
                'approver'     => 'sometimes|required|string',
                'status'       => 'sometimes|required|boolean',
                'issue_date'   => 'sometimes|required|date',
                'expire_date'  => 'sometimes|required|date',
            ];

            $req->validate($rules);
            $vehicle = Vehicle::findOrFail($id);
            $vehicle->update($req->only(array_keys($rules)));

            return response()->json($vehicle, 200);
        } catch (ValidationException $e) {
            return $this->handleValidationException($e);
        } catch (\Exception $e) {
            return $this->handleUnexpectedException($e);
        }
    }

    // ================ Delete vehicle by ID
    public function delete($id)
    {
        try {
            $vehicle = Vehicle::findOrFail($id);
            $vehicle->delete();

            return response()->json(['message' => 'Vehicle deleted successfully.'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Vehicle not found.'], 404);
        } catch (ValidationException $e) {
            return $this->handleValidationException($e);
        } catch (\Exception $e) {
            return $this->handleUnexpectedException($e);
        }
    }

    // ================ Approve vehicle
    public function approve(Request $req, $id)
    {
        try {
            $vehicle = Vehicle::findOrFail($id);

            $token = $this->extractTokenFromRequest($req);
            $approverName = $this->getOwnerFromToken($token);

            $vehicle->status = true;
            $vehicle->approver = $approverName;
            $vehicle->save();

            return response()->json(['message'=>'Approved successful...','vehicle'=>$vehicle], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Vehicle not found.'], 404);
        } catch (\Exception $e) {
            return $this->handleUnexpectedException($e);
        }
    }

    // ================ Extract token from request
    private function extractTokenFromRequest(Request $req)
    {
        $token = $req->header('Authorization');
        return ($token && strpos($token, 'Bearer ') === 0) ? substr($token, 7) : null;
    }

    // ================ Get owner from token
    private function getOwnerFromToken($token)
    {
        $params = $this->getKeycloakParams(['token' => $token]);
        $response = Http::asForm()->post($this->getKeycloakUrl('token/introspect'), $params);

        if ($response->failed()) {
            throw new \Exception('Token introspection failed');
        }

        $userData = $response->json();
        return $userData['preferred_username'] ?? 'unknown';
    }

    // ================ Handle validation errors
    protected function handleValidationException(ValidationException $e)
    {
        return response()->json(['message' => 'Validation Error', 'errors' => $e->errors()], 422);
    }

    // ================ Handle unexpected errors
    protected function handleUnexpectedException(\Exception $e)
    {
        Log::error($e->getMessage());
        return response()->json(['error' => 'An unexpected error occurred.'], 500);
    }
}
