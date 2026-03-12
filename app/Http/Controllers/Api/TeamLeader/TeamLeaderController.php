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


class TeamLeaderController extends Controller
{

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
}
