<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;



class AuthController extends Controller
{
    /**
     * Register User
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            // Basic Information
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:users,email',
            'emp_code' => 'required|string|max:50|unique:users,emp_code',
            'joining_date' => 'required|date|before_or_equal:today',
            'designation' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:6|max:255',
            'role' => 'required|in:admin,employee,manager,team_leader,sales',
            'manager_id' => 'nullable|exists:users,id',
            'team_id' => 'nullable|exists:teams,id',

            // Image
            'image' => 'nullable|image|max:2048',

            // Document Information
            'aadhaar_number' => 'required|regex:/^\d{12}$/',
            'pan_number' => 'required|regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/',

            // Guardian Details
            'father_name' => 'required|string|max:255',
            'mother_name' => 'required|string|max:255',

            // Experience Details
            'years_of_experience' => 'required|numeric|min:0|max:70',
            'training_experience' => 'nullable|string|max:1000',

            // Previous Company Details
            'previous_company_name' => 'nullable|string|max:255',
            'previous_designation' => 'nullable|string|max:255',
            'previous_company_duration' => 'nullable|numeric|min:0|max:70',
        ]);

        $user = new User();

        // Fill mass-assignable fields
        $user->fill($validated);

        // Secure password handling
        $user->password = isset($validated['password'])
            ? Hash::make($validated['password'])
            : Hash::make('default123');

        // Handle image upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('profile_images', 'public');
            $user->image = $imagePath;
        }

        $user->save();

        // Create API Token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully',
            'token' => $token,
            'user' => $user
        ], 201);
    }

    public function login(Request $request)
    {

        $request->validate([
            'email' => 'required|email',
            'password' => 'required'

        ]);
        $user = User::where('email',$request->email)->first();
        // $user = User::all();
        // return  $user;
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User Not Found'
            ], 401);
        }
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Incorrect Password'
            ], 401);
        }
        if ($user) {
            $token = $user->createToken(name: 'auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login Successfully',
                'token' => $token

            ], 200);
        }
    }

    public function logout(Request $request)
    {

        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }
}
