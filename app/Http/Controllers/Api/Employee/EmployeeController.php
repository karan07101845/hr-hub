<?php

namespace App\Http\Controllers\Api\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Nette\Utils\Json;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Imports\UsersImport;
use Maatwebsite\Excel\Facades\Excel;




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
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimes:pdf,doc,docx,jpg,png|max:5120',
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

        // Handle profile image
        if ($request->hasFile('image')) {
            if ($user->image && Storage::exists('public/' . $user->image)) {
                Storage::delete('public/' . $user->image);
            }
            $validated['image'] = $request->file('image')->store('profile_images', 'public');
        }

        // Handle multi-document upload
        // $filesData = $user->document ?? [];

        if ($request->hasFile('documents')) {

            foreach ($request->file('documents') as $file) {

                $path = $file->store('employee-documents', 'public');

                $filesData[] = [
                    'doc_id' => rand(10000, 99999),
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientMimeType(),
                ];
            }

            $user->document = $filesData;
        }

        // Mass update
        $user->fill($validated);
        $user->save();

        // Assign manager if role is employee
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


    //document

    public function upload(Request $request, $id)
    {

        $validated = $request->validate([
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimes:pdf,doc,docx,jpg,png|max:5120',
        ], [
            'documents.*.mimes' => 'Only PDF, DOC, DOCX, JPG, and PNG files are allowed.',
            'documents.*.max' => 'Each document must not exceed 5MB.',
        ]);
        // dd('tessst');

        // Find the employee
        $employee = User::findOrFail($id);

        $filesData = $employee->document ?? [];

        if ($request->hasFile('documents')) {

            foreach ($request->file('documents') as $file) {

                // Store file in storage/app/public/employee-documents
                $path = $file->store('employee-documents', 'public');

                $filesData[] = [
                    'doc_id' => rand(10000, 99999), // generates a 5-digit number
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientMimeType(),
                ];
            }

            // Store structured JSON in DB
            $employee->document = $filesData;
        }

        $employee->save();
        return response()->json([
            'success' => true,
            'message' => 'Documents uploaded successfully.',
            'documents' => $employee->document,
        ]);
    }


    //getDocuments

    public function getDocuments($id)
    {
        $user = User::findOrFail($id);

        return response()->json([
            'success' => true,
            'documents' => $user->document ?? []
        ]);
    }


    //DownloadDocument
    public function downloadDocument($userId, $docId)
    {
        $user = User::findOrFail($userId);

        $documents = $user->document ?? [];

        // Find document by doc_id
        $key = array_search($docId, array_column($documents, 'doc_id'));

        if ($key === false) {
            return response()->json([
                'message' => 'Document not found'
            ], 404);
        }

        $file = $documents[$key];
        $path = storage_path('app/public/' . $file['file_path']);

        if (!file_exists($path)) {
            return response()->json([
                'message' => 'File not found on server'
            ], 404);
        }

        return response()->download($path, $file['original_name']);
    }

    //DeleteDocument
    public function deleteDocument($userId, $docId)
    {
        $user = User::findOrFail($userId);

        $documents = $user->document ?? [];

        // Find the document by doc_id
        $key = array_search($docId, array_column($documents, 'doc_id'));

        if ($key === false) {
            return response()->json([
                'message' => 'Document not found'
            ], 404);
        }

        $file = $documents[$key];
        $path = storage_path('app/public/' . $file['file_path']);

        // Delete file from storage
        if (file_exists($path)) {
            unlink($path);
        }

        // Remove from array
        array_splice($documents, $key, 1);

        // Save updated documents
        $user->document = $documents;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Document deleted successfully.'
        ]);
    }


    public function getAllProfiles()
    {
        $employees = User::all();

        return response()->json([



            'status' => true,
            'message' => 'Employees fetched successfully',
            'data' => $employees
        ]);
    }

    public function updateProfile(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:users,email,' . $id,
            'emp_code' => 'nullable|string|max:50|unique:users,emp_code,' . $id,
            'joining_date' => 'required|date|before_or_equal:today',
            'designation' => 'nullable|string|max:255',
            'role' => 'required|in:admin,employee,manager,team_leader,sales',
            'manager_id' => 'nullable|exists:users,id',
            'team_id' => 'nullable|exists:teams,id',
            'image' => 'nullable|image|max:2048',
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimes:pdf,doc,docx,jpg,png|max:5120',
            'aadhaar_number' => 'nullable|regex:/^\d{12}$/',
            'pan_number' => 'nullable|regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/',
            'father_name' => 'nullable|string|max:255',
            'mother_name' => 'nullable|string|max:255',
            'years_of_experience' => 'nullable|numeric|min:0|max:70',
            'training_experience' => 'nullable|string|max:1000',
            'previous_company_name' => 'nullable|string|max:255',
            'previous_designation' => 'nullable|string|max:255',
            'previous_company_duration' => 'nullable|numeric|min:0|max:70',
        ]);

        // Handle profile image
        if ($request->hasFile('image')) {
            if ($user->image && Storage::exists('public/' . $user->image)) {
                Storage::delete('public/' . $user->image);
            }
            $validated['image'] = $request->file('image')->store('profile_images', 'public');
        }

        // Handle multi-document upload
        // $filesData = $user->document ?? [];

        if ($request->hasFile('documents')) {

            foreach ($request->file('documents') as $file) {

                $path = $file->store('employee-documents', 'public');

                $filesData[] = [
                    'doc_id' => rand(10000, 99999),
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientMimeType(),
                ];
            }

            $user->document = $filesData;
        }

        // Mass update
        $user->fill($validated);

        // Assign manager if role is employee
        if ($validated['role'] === 'employee') {
            $user->manager_id = $validated['manager_id'] ?? null;
        } else {
            $user->manager_id = null;
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Employee updated successfully',
            'user' => $user
        ]);
    }



    public function myProfile()
    {
        $user = Auth::user();

        return response()->json([
            'success' => true,
            'user' => $user
        ]);
    }

    public function updateMyProfile(Request $request)
{
    $user = Auth::user();

    $validated = $request->validate([
        'image' => 'nullable|image|max:2048',
        'password' => 'nullable|string|min:6|confirmed'
    ]);

    // Update Image
    if ($request->hasFile('image')) {

        if ($user->image && Storage::exists('public/' . $user->image)) {
            Storage::delete('public/' . $user->image);
        }

        $validated['image'] = $request->file('image')->store('profile_images', 'public');
    }

    // Update Password
    if (!empty($validated['password'])) {
        $validated['password'] = Hash::make($validated['password']);
    } else {
        unset($validated['password']);
    }

    $user->update($validated);

    return response()->json([
        'success' => true,
        'message' => 'Profile updated successfully',
        'user' => $user
    ]);
}




public function uploadUsers(Request $request)
{
    // dd($request->all());
    $request->validate([
        'file' => 'required|mimetypes:text/csv,text/plain,application/vnd.ms-excel|max:2048'
        ]);
    Excel::import(new UsersImport, $request->file('file'));

    return response()->json([
        'success' => true,
        'message' => 'Users uploaded successfully'
    ]);
}
}
