<?php
 
namespace App\Http\Controllers\Api\Auth;
 
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPasswordMail;
 
 
 
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
            'previous_company_duration' => 'nullable|numeric|min:0|max:70'
           
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
 
         if ($request->hasFile('document')) {
        $user->document = $this->upload($request->file('document'));
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
        $user = User::where('email', $request->email)->first();
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
 
private function upload($file)
{
    return $file->store('documents', 'public');
}
 
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);
 
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }
        // dd();
 
        // Explicitly check current password
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect'
            ], 400);
        }
 
        // Update the password
        $user->password = Hash::make($request->new_password);
        $user->save();
 
        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully!'
        ], 200);
    }
 
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            ]);
            $user = User::where('email', $request->email)->first();
 
    $token = Str::random(64);
 
    DB::table('password_reset_tokens')->where('email', $request->email)->delete();
 
    DB::table('password_reset_tokens')->insert([
        'email' => $request->email,
        'token' => $token,
        'created_at' => Carbon::now()
    ]);
 
    Mail::raw("Click the link below to reset your password:
http:///127.0.0.1:8000/api/auth/reset?token=".$token, function ($message) use ($request) {
    $message->to($request->email);
    $message->subject('Reset Password');
});
 
    return response()->json([
        'status' => true,
        'message' => 'Password reset link has been sent to your email.'
    ], 200);
}// Step 2: Reset password
public function resetPassword(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'token' => 'required|string',
        'password' => 'required|string|min:8|confirmed'
    ]);
 
    // Check if token is valid
    $record = DB::table('password_reset_tokens')
        ->where('email', $request->email)
        ->where('token', $request->token)
        ->first();
 
    if (!$record) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid token'
        ], 400);
    }
 
    $user = User::where('email', $request->email)->first();
 
    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'User not found'
        ], 404);
    }
 
    // Update password
    $user->password = Hash::make($request->password);
    $user->save();
 
    // Delete the used token
    DB::table('password_reset_tokens')->where('email', $request->email)->delete();
 
    return response()->json([
        'success' => true,
        'message' => 'Password has been reset successfully'
    ]);
}
}