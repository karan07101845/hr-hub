<?php

namespace App\Http\Controllers\Api\TeamLeader;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\user;
use App\Models\Attendance;
use App\Models\Leaves;
use App\Models\Notice;
use App\Models\Teams;


class TeamLeaderDashboardController extends Controller
{

    public function overview()
{
    $leader = auth()->user();

    $teamMembers = User::where('team_id',$leader->team_id)
        ->where('role','employee')
        ->count();

    $attendance = Attendance::where('user_id',$leader->id)
        ->latest()
        ->first();

    $pendingLeave = Leaves::where('status','pending')
        ->whereIn('user_id', function($q) use ($leader){
            $q->select('id')->from('users')
            ->where('team_id',$leader->team_id);
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

}