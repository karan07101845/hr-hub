<?php

namespace App\Http\Controllers\Api\Auth\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;


class EmployeeController extends Controller
{
    //SHOW ALL DATA
    public function employeesData(Request $request)
    {
        // $employees = User::whereNot('role', 'admin')->get();
        $employees = User::whereNot('role', 'admin')
            ->orderBy('joining_date', 'DESC')
            ->get();


        // Format for DataTables
        $data = $employees->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'designation' => $user->designation,
                'emp_code' => $user->emp_code,
                'joining_date' => $user->joining_date?->format('d-m-Y'),
                'image' => '<img src="' . asset('storage/' . $user->image) . '" width="50" class="rounded-circle" />',
                'role' => $user->role,
                'created_at' => $user->created_at->format('d-m-Y'),
              
            ];
        });

        return response()->json(['data' => $data]);
    }

    // GET DATA BY ID
    public function getEmployee($id)
    {
        $user = User::findOrFail($id);
        return response()->json($user);
    }


    //UPDATE DATA
  public function updateEmployee(Request $request, $id)
    {
        $user = User::findOrFail($id);
// dd("ftyfj");
        // Validation
        $validated = $request->validate([
            // Basic Information
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:users,email,' . $id,
            'emp_code' => 'nullable|string|max:50|unique:users,emp_code,' . $id,
            'joining_date' => 'required|date|before_or_equal:today',
            'designation' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:6|max:255',
            'role' => 'required|in:admin,employee,manager,team_leader,sales',
            'manager_id' => 'nullable|exists:users,id',
            'team_id' => 'nullable|exists:teams,id',

            // Image
            'image' => 'nullable|image|max:2048',

            // Document Information
            'aadhaar_number' => 'nullable|regex:/^\d{12}$/',
            'pan_number' => 'nullable|regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/',

            // Guardian Details
            'father_name' => 'nullable|string|max:255',
            'mother_name' => 'nullable|string|max:255',

            // Experience Details
            'years_of_experience' => 'nullable|numeric|min:0|max:70',
            'training_experience' => 'nullable|string|max:1000',

            // Previous Company Details
            'previous_company_name' => 'nullable|string|max:255',
            'previous_designation' => 'nullable|string|max:255',
            'previous_company_duration' => 'nullable|numeric|min:0|max:70',
        ]);
    
        // Handle password
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']); // keep existing password
        }
        

        // Handle image upload
        if ($request->hasFile('image')) {
            if ($user->image && Storage::exists('public/' . $user->image)) {
                Storage::delete('public/' . $user->image);
            }
            $validated['image'] = $request->file('image')->store('profile_images', 'public');
        }

        // Mass update
        $user->fill($validated);
        $user->save();

        // Sync manager relation if employee
      // Set manager only if role is employee
if ($validated['role'] === 'employee') {
    $user->manager_id = $validated['manager_id'] ?? null;
} else {
    // If not employee, remove manager
    $user->manager_id = null;
}

$user->save();


        return response()->json([
            'success' => true,
            'message' => 'Employee updated successfully',
            'user' => $user
        ]);
    }


    //DELETE EMPLOYEEE
        public function deleteEmployee($id)
    {
        $user = User::find($id);
        if (!$user)
            return response()->json(['error' => 'User not found']);

        $user->delete();
        return response()->json(['success' => 'Employee deleted successfully']);
    }

    //softdelete

      public function softDeleteEmployee($id)
{
    $user = \App\Models\User::find($id);

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'Employee not found.'
        ], 404);
    }

    if ($user->trashed()) {
        return response()->json([
            'success' => false,
            'message' => 'Employee already soft deleted.'
        ], 400);
    }

    $user->delete(); // Soft delete

    return response()->json([
        'success' => true,
        'message' => 'Employee soft deleted successfully.'
    ]);
}


//GETSOFTDELETE

public function getSoftDeletedEmployees()
{
    // Get only soft deleted employees
    $deletedEmployees = User::onlyTrashed()->get();

    // Map the data to a clean JSON structure
    $data = $deletedEmployees->map(function ($user) {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'emp_code' => $user->emp_code,
            'image_url' => $user->image ? url('storage/img/' . $user->image) : null, // image URL
            'role' => $user->role,
            'deleted_at' => $user->deleted_at ? $user->deleted_at->format('Y-m-d H:i:s') : null,
        ];
    });

    return response()->json([
        'success' => true,
        'message' => 'Soft deleted employees fetched successfully',
        'data' => $data,
    ], 200);
}



    //RESTORE DATA

public function restoreEmployee($id)
{
    $user = \App\Models\User::withTrashed()->find($id);

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'Employee not found.'
        ], 404);
    }

    if (!$user->trashed()) {
        return response()->json([
            'success' => false,
            'message' => 'Employee is not deleted.'
        ], 400);
    }

    $user->restore(); // Restore soft deleted user

    return response()->json([
        'success' => true,
        'message' => 'Employee restored successfully.'
    ]);
}

//PERMANENT DELETE

  public function permanentDeleteEmployee($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->forceDelete();

        return response()->json(['message' => 'Employee permanently deleted successfully.']);
    }
//GetAllemployees

    public function getAllEmployees()
    {
        $employees = User::select('id', 'name', 'emp_code', 'role', 'image')->get();

        return response()->json($employees, 200, [], JSON_PRETTY_PRINT);
    }


    //BulkUpdateEmployee

       public function bulkUpdateRoles(Request $request)
    {


        // dd($request);
        $validated = $request->validate([
            'employee_ids' => 'required|array',
            'role' => 'nullable|string',
            'designation' => 'nullable|string',
        ]);

        // Check if at least one field exists
        if (empty($validated['role']) && empty($validated['designation'])) {
            return response()->json([
                'message' => 'Please provide either role or designation for bulk update.'
            ], 422);
        }

        // Apply updates
        $query = User::whereIn('id', $validated['employee_ids']);

        if (!empty($validated['role'])) {
            $query->update(['role' => $validated['role']]);
        }

        if (!empty($validated['designation'])) {
            $query->update(['designation' => $validated['designation']]);
        }

        return response()->json([
            'message' => 'Employees updated successfully!'
        ]);
    }

}
