<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Visitor;
use Illuminate\Support\Facades\Validator;

class VisitorController extends Controller
{
    public function register(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'name'          => 'required|string',
            'purpose'       => 'required|string',
            'contact'       => 'required|string',
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
            'approver_id'   => 1,
            'status'        => true,
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
        $visitor = Visitor::findOrFail($id);

        $validator = Validator::make($req->all(), [
            'name'          => 'sometimes|string',
            'purpose'       => 'sometimes|string',
            'contact'       => 'sometimes|string',
            'entry_time'    => 'sometimes|date_format:H:i:s',
            'exit_time'     => 'sometimes|date_format:H:i:s',
            'approver_id'   => 'sometimes|integer',
            'status'        => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();
        $visitor->update($validatedData);

        return response()->json([
            'message' => 'Visitor updated successfully',
            'visitor' => $visitor
        ], 200);
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
