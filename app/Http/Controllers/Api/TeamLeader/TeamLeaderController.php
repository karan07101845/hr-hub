<?php

namespace App\Http\Controllers\Api\TeamLeader;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Leaves;
use App\Models\Notice;
use App\Models\Teams;
use Illuminate\Http\Request;
use App\Models\TeamLeader;
use Illuminate\Support\Facades\Hash;


class TeamLeaderController extends Controller
{

    //Overview section

    public function overview()
    {
        $leader = Auth::user();

        $teamMembers = User::where('team_id', $leader->team_id)
            ->where('role', 'employee')
            ->count();

        $attendance = Attendance::where('user_id', $leader->id)
            ->latest()
            ->first();

        $pendingLeave = Leaves::where('status', 'pending')
            ->whereIn('user_id', function ($q) use ($leader) {
                $q->select('id')->from('users')
                    ->where('team_id', $leader->team_id);
            })
            ->count();

        $notices = Notice::latest()->take(5)->get();

        return response()->json([
            "success" => true,
            "team_members" => $teamMembers,
            "yesterday_attendance" => $attendance->status ?? "Absent",
            "avg_performance" => "90%",
            "pending_leave" => $pendingLeave,
            "public_notices" => $notices
        ]);
    }

    //Teams section

    public function teamMembers()
    {
        $leader = Auth::user();

        $members = User::where('team_id', $leader->team_id)
            ->where('role', 'employee')
            ->select('id', 'name', 'emp_code', 'designation')
            ->get();

        return response()->json([
            "success" => true,
            "members" => $members
        ]);
    }

    public function teamAttendance()
    {
        $leader = Auth::user();

        $members = User::where('team_id', $leader->team_id)
            ->where('role', 'employee')
            ->pluck('id');

        $attendance = Attendance::whereIn('user_id', $members)
            ->latest()
            ->get();

        return response()->json([
            "success" => true,
            "attendance" => $attendance
        ]);
    }


    //Reviews Function

    public function addReview(Request $request)
    {
        $request->validate([
            "employee_name" => "required",
            "performance" => "required",
            "comment" => "required"
        ]);

        TeamLeader::create([
            "tl_id" => Auth::id(),
            "employee_name" => $request->employee_name,
            "performance" => $request->performance,
            "comment" => $request->comment
        ]);

        return response()->json([
            "success" => true,
            "message" => "Review added successfully"
        ]);
    }

    public function getReviews()
    {
        $leader = Auth::user();

        $reviews = TeamLeader::where('tl_id', $leader->id)->get();

        return response()->json([
            "success" => true,
            "data" => $reviews
        ]);
    }

    public function deleteReview($id)
    {
        $review = TeamLeader::find($id);

        if (!$review) {
            return response()->json([
                "success" => false,
                "message" => "Review not found"
            ]);
        }

        $review->delete();

        return response()->json([
            "success" => true,
            "message" => "Review deleted"
        ]);
    }

    public function updateReview(Request $request, $id)
    {
        $request->validate([
            "performance" => "required",
            "comment" => "required"
        ]);

        $review = TeamLeader::find($id);

        if (!$review) {
            return response()->json([
                "success" => false,
                "message" => "Review not found"
            ]);
        }

        $review->update([
            "performance" => $request->performance,
            "comment" => $request->comment
        ]);

        return response()->json([
            "success" => true,
            "message" => "message upadted successfully",
            "data" => $review
        ]);
    }

    //Profile section

    public function profile()
    {
        $user = Auth::user();

        return response()->json([
            "success" => true,
            "data" => [
                "id" => $user->id,
                "name" => $user->name,
                "email" => $user->email,
                "role" => $user->role,
                "designation" => $user->designation,
                "emp_code" => $user->emp_code
            ]
        ]);
    }

    //Change password Function 

    public function changePassword(Request $request)
    {
        $request->validate([
            "current_password" => "required",
            "new_password" => "required|min:6|confirmed"
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                "success" => false,
                "message" => "Current password is incorrect"
            ], 400);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            "success" => true,
            "message" => "Password changed successfully"
        ]);
    }

    public function logout()
    {
        $user = Auth::user();

        $user->tokens()->delete();

        return response()->json([
            "success" => true,
            "message" => "Logged out successfully"
        ]);
    }
}
