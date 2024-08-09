<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Services\VisitorService;
use App\Models\Employee;
use Illuminate\Http\Request;
use App\Models\Visitor;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\WriterInterface;
use Endroid\QrCode\Encoding\EncodingInterface;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelInterface;
use Illuminate\Support\Facades\Storage;

class VisitorController extends Controller
{   
    protected $visitorService;

    public function __construct(VisitorService $visitorService)
    {
        $this->visitorService = $visitorService;
    }

    // ========== Visitor register
    public function register(Request $req)
    {
        // Validation
        $validator = Validator::make($req->all(), [
            'name'          => 'required|string|max:255',
            'purpose'       => 'required|string|max:255',
            'contact'       => 'required|string|max:255',
            'entry_time'    => 'required|date_format:H:i:s',
            'exit_time'     => 'required|date_format:H:i:s',
            'date'          => 'nullable|date_format:Y-m-d',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Default to current date if not provided
        $date = $req->input('date', Carbon::now()->format('Y-m-d'));

        $visitor = Visitor::create([
            'name'          => $req->name,
            'purpose'       => $req->purpose,
            'contact'       => $req->contact,
            'entry_time'    => $req->entry_time,
            'exit_time'     => $req->exit_time,
            'scan_count'    => 0,
            'approver'      => null,
            'status'        => 'pending',
            'date'          => $date,
        ]);

        // Generate QR code with all visitor data encoded
        $visitorData = [
            'id'            => $visitor->id,
            'name'          => $visitor->name,
            'purpose'       => $visitor->purpose,
            'contact'       => $visitor->contact,
            'entry_time'    => $visitor->entry_time,
            'exit_time'     => $visitor->exit_time,
            'date'          => $visitor->date,
            'status'        => $visitor->status,
            'approver'      => $visitor->approver,
        ];
        $qrCodeData = json_encode($visitorData);

        $qrCode = new QrCode($qrCodeData);
        $qrCode->setSize(200);

        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        // Save the QR code to a file
        $qrCodePath = 'uploads/qrcodes/' . $visitor->id . '.png';
        Storage::disk('public')->put($qrCodePath, $result->getString());

        // Store QR code path
        $qrCodePath = 'storage/' . $qrCodePath;
        $visitor->qr_code = $qrCodePath;
        $visitor->save();

        return response()->json($visitor, 201);
    }

    // // ========== Visitor update
    // public function update(Request $req, $id)
    // {
    //     $visitor = Visitor::findOrFail($id);

    //     $validator = Validator::make($req->all(), [
    //         'name'          => 'sometimes|string|max:255',
    //         'purpose'       => 'sometimes|string|max:255',
    //         'contact'       => 'sometimes|string|max:255',
    //         'entry_time'    => 'sometimes|date_format:H:i:s',
    //         'exit_time'     => 'sometimes|date_format:H:i:s|after:entry_time',
    //         'status'        => 'sometimes|in:pending,approved,rejected',
    //         'scan_count'    => 'sometimes|integer',
    //         'date'          => 'sometimes|date_format:D:m:y',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['errors' => $validator->errors()], 422);
    //     }

    //     try {
    //         // Extract the Bearer token from the Authorization header
    //         $access_token = $req->bearerToken();

    //         if (!$access_token) {
    //             return response()->json(['message' => 'No access token provided'], 401);
    //         }

    //         // Verify and get user info from Keycloak
    //         $params = $this->getKeycloakParams([
    //             'token' => $access_token
    //         ]);

    //         $introspectionResponse = Http::asForm()->post($this->getKeycloakUrl('token/introspect'), $params);

    //         if ($introspectionResponse->failed()) {
    //             return response()->json(['message' => 'Failed to retrieve user info from Keycloak'], 500);
    //         }

    //         $userInfo = $introspectionResponse->json();

    //         // Check if the token is active
    //         if (!isset($userInfo['sub'])) {
    //             return response()->json(['message' => 'Token is inactive or invalid'], 401);
    //         }

    //         $email = $userInfo['email'];

    //         // Check if the user exists in the employees table
    //         $employee = Employee::where('email', $email)->first();

    //         if (!$employee) {
    //             return response()->json(['message' => 'User not found'], 404);
    //         }

    //         $validatedData = $validator->validated();
            
    //         if (isset($validatedData['status']) && $validatedData['status'] === 'approved') {
    //             $validatedData['approver'] = $employee->name;
    //         }

    //         if ($visitor->scan_count >= 2) {
    //             $visitor->status = 'rejected'; // Or 'pending' if that's the desired status
    //             $visitor->save();
    //         }

    //         $visitor->update($validatedData);

    //         // Update status based on scan count
    //         $this->visitorService->updateStatusBasedOnScanCount($visitor);

    //         return response()->json([
    //             'message' => 'Visitor updated successfully',
    //             'visitor' => $visitor,
    //         ], 200);
    //     } catch (\Exception $e) {
    //         return response()->json(['message' => 'Internal server error: ' . $e->getMessage()], 500);
    //     }
    // }

    // ========== Visitor update
    public function update(Request $req, $id)
    {
        $visitor = Visitor::findOrFail($id);

        // Validation
        $validator = Validator::make($req->all(), [
            'name'          => 'sometimes|string|max:255',
            'purpose'       => 'sometimes|string|max:255',
            'contact'       => 'sometimes|string|max:255',
            'entry_time'    => 'sometimes|date_format:H:i:s',
            'exit_time'     => 'sometimes|date_format:H:i:s|after:entry_time',
            'status'        => 'sometimes|in:pending,approved,rejected',
            'scan_count'    => 'sometimes|integer',
            'date'          => 'sometimes|date_format:Y-m-d',
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

            $validatedData = $validator->validated();

            // Update visitor data
            $visitor->update($validatedData);

            // Update approver if status is approved
            if (isset($validatedData['status']) && $validatedData['status'] === 'approved') {
                $visitor->approver = $employee->name;
                $visitor->save();
            }

            // Generate a new QR code if necessary
            if ($visitor->wasChanged()) {
                $visitorData = [
                    'id'            => $visitor->id,
                    'name'          => $visitor->name,
                    'purpose'       => $visitor->purpose,
                    'contact'       => $visitor->contact,
                    'entry_time'    => $visitor->entry_time,
                    'exit_time'     => $visitor->exit_time,
                    'date'          => $visitor->date,
                    'status'        => $visitor->status,
                    'approver'      => $visitor->approver,
                ];
                $qrCodeData = json_encode($visitorData);

                $qrCode = new QrCode($qrCodeData);
                $qrCode->setSize(200);

                $writer = new PngWriter();
                $result = $writer->write($qrCode);

                // Save the QR code to a file
                $qrCodePath = 'uploads/qrcodes/' . $visitor->id . '.png';
                Storage::disk('public')->put($qrCodePath, $result->getString());

                // Store QR code path
                $visitor->qr_code = 'storage/' . $qrCodePath;
                $visitor->save();
            }

            // Update status based on scan count
            $this->visitorService->updateStatusBasedOnScanCount($visitor);

            return response()->json([
                'message' => 'Visitor updated successfully',
                'visitor' => $visitor,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Internal server error: ' . $e->getMessage()], 500);
        }
    }

    // ========== Visitor Delete
    public function delete($id)
    {
        $visitor = Visitor::findOrFail($id);
        if (!$visitor){
            return response()->json(['message'=> 'Visitor not found!'], 404);
        }
        $visitor = $visitor->delete();
        return response()->json(['message'=> 'Visitor deleted successfully.'],200);
    }

    // ========== Visitor Scan count
    public function updateScanCount(Request $req, $id)
    {
        $visitor = Visitor::findOrFail($id);

        // Increment the scan_count
        $visitor->increment('scan_count');

        // Update the status based on scan_count
        if ($visitor->scan_count >= 2) {
            $visitor->status = 'rejected'; // Or 'pending' if that's the desired status
            $visitor->save();
        }

        return response()->json([
            'message' => 'Scan count updated successfully',
            'visitor' => $visitor
        ], 200);
    }

    public function store(Request $request)
    {
        $id = $request->input('id');

        $visitor = Visitor::findOrFail($id);

        // Increment scan count
        $visitor->increment('scan_count');

        // Update status based on scan count
        $this->visitorService->updateStatusBasedOnScanCount($visitor);

        return response()->json(['message' => 'QR code data received', 'id' => $id, 'visitor' => $visitor]);
    }

    public function getAllVisitor(Request $req)
    {
        $query = Visitor::query();

        if ($req->has('name')) {
            $query->where('name', 'like', '%' . $req->input('name') . '%');
        }

        if ($req->has('status') && $req->input('status') !== 'all') {
            $query->where('status', 'like', '%' . $req->input('status') . '%');
        }

        // Get the count of visitors that match the criteria
        $totalCount = $query->count();

        // Get the filtered visitors
        $visitors = $query->get();

        // Return both the count and the list of visitors
        return response()->json([
            'total_count' => $totalCount,
            'visitors' => $visitors
        ], 200);
    }

    // ========== Filter visitor by status
    public function getVisitorStatus()
    {
        $visitorStats = Visitor::select('status', DB::raw('count(*) as count'))
                                ->groupBy('status')
                                ->get();
        return response()->json($visitorStats);
    }

    public function show($id)
    {
        $visitor = Visitor::findOrFail($id);
        return response()->json(['visitor' => $visitor], 200);
    }
}
