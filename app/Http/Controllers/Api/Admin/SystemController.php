<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Systems;

class SystemController extends Controller
{
    public function store(Request $req)
    {
        $req->validate([
            'team_id' => 'required',
            'upwork_id'    => 'required',
            'system_users' => 'required',
            'employee_id'  => 'required',
        ]);

        $register = new Systems();
        $register->team_id = $req->team_id;
        $register->upwork_id = $req->upwork_id;
        $register->system_users = $req->system_users;
        $register->employee_id = $req->employee_id;
        $register->save();

        return response()->json([
            'success' => true,
            'message' => 'Done'


        ]);
    }

    public function showAllData()
    {
        $allData = Systems::orderBy('id', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $allData
        ]);
    }


    public function edit($id)
    {
        $record = Systems::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $record
        ]);
    }


    public function updateData(Request $request, $id)
    {
        $record = Systems::find($id);

        if (!$record) {
            return response()->json(['success' => false, 'message' => 'Record not found']);
        }

        $record->update([
            'team_id' => $request->team_id,
            'upwork_id' => $request->upwork_id,
            'system_users' => $request->system_users,
            'employee_id' => $request->employee_id,
        ]);

        return response()->json(['success' => true, 'message' => 'Data updated successfully']);
    }


    public function deletedata(request $request)
    {
        $id = $request->id;
        $system = Systems::find($id);

        if (!$system) {
            return response()->json([
                'success' => false,
                'message' => 'User not Found'

            ]);
        }

        $system->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully'

        ]);
    }
}
