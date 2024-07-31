<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Visitor;

class VisitorController extends Controller
{
    public function register(Request $req){
        $validator = [
            'name'          => 'required|string',
            'purpose'       => 'required|string',
            'contact'       => 'required|string',
            'entry_time'    => 'required|date_format:H:i:s',
            'exit_time'     => 'required|date_format:H:i:s',
        ];
        $req->validate($validator);

        $visitor = Visitor::create([
            'name'          => $req->name,
            'purpose'       => $req->purpose,
            'contact'       => $req->contact,
            'entry_time'    => $req->entry_time,
            'exit_time'     => $req->exit_time,
            'approver_id'   => 1,
            'status'        =>true,
        ]);
        return response()->json($visitor,201);
    }

    public function getAllVisitor(){
        $visitors = Visitor::all();
        return response()->json($visitors, 200);
    }

    public function update(Request $req, $id)
    {
        // Find the visitor record
        $visitor = Visitor::findOrFail($id);

        // Validate the request data
        $req->validate([
            'name'          => 'sometimes|string',
            'purpose'       => 'sometimes|string',
            'contact'       => 'sometimes|string',
            'entry_time'    => 'sometimes|date_format:H:i:s',
            'exit_time'     => 'sometimes|date_format:H:i:s',
        ]);

        // Update the visitor record with the validated data
        $visitor->update($req->only([
            'name',
            'purpose',
            'contact',
            'entry_time',
            'exit_time',
        ]));

        // Return a successful response
        return response()->json(['message' => 'Visitor updated successfully', 'visitor' => $visitor], 200);
    }

    public function show($id)
    {
        $visitor = Visitor::findOrFail($id);
        return response()->json(['visitor' => $visitor], 200);
    }

}
