<?php
 
namespace App\Http\Controllers\API\Manager;
 
use App\Http\Controllers\Api\Leaves\LeavesController;
use App\Http\Controllers\Api\Attendance\AttendanceController;
use App\Http\Controllers\Api\employee\TeamsController;
use App\Http\Controllers\Api\Notice\NoticeController;
 
 
 
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Leaves;
use App\Models\Teams;
use App\Models\Notice;
use App\Models\Attendance;
 
 
class ManagerController extends Controller
{
 
    protected $leavesController;
    protected $AtendanceController;
 
    protected $TeamsController;
 
    protected $NoticeController;
    public function __construct()
    {
        $this->leavesController = new LeavesController();
        $this->AtendanceController = new AttendanceController();
        $this->TeamsController = new TeamsController();
        $this->NoticeController = new NoticeController();
    }
 
    public function allTeamLeaves(Request $request)
    {
        // Use the controller instance from constructor
        return $this->leavesController->allLeaves($request);
    }
 
    public function sendLeaves(Request $request)
    {
        // Use the controller instance from constructor
        return $this->leavesController->apply($request);
    }
 
    public function showSummary(Request $request)
    {
        // Use the controller instance from constructor
        return $this->leavesController->summary();
    }
 
 
 
 
    //Attendence Functionds
 
 
    public function getTeamAttendance(Request $request)
    {
        // Use the controller instance from constructor
        return $this->AtendanceController->index($request);
    }
 
public function yestStatus(Request $request)
    {
        // Use the controller instance from constructor
        return $this->AtendanceController->lastStatus();
    }
 
 
    //Teams Functions
 
    public function showTeamMember($id)
    {
        // Use the controller instance from constructor
        return $this->TeamsController->show($id);
    }
 
 
    //Notice functions
    public function ShowNot($notice_type)
    {
        // Use the controller instance from constructor
        return $this->NoticeController->getByType($notice_type);
    }
 
      public function ShowPublic()
    {
        // Use the controller instance from constructor
        return $this->NoticeController->ShowNotice();
    }
 
 
public function getOverview(Request $request)
{
    $user = $request->user();
 
    if (!$user) {
        return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
    }
 
   
    $teamMembers = $user->employees()->count();
 
   
    $attendanceResponse = $this->AtendanceController->lastStatus();
 
    $attendance = $attendanceResponse->original['attendance'] ?? 'N/A';
 
 
    $pendingLeavesResponse = $this->showSummary($request);
 
   
    $pendingLeaves = $pendingLeavesResponse->original['pending'] ?? 0;
 
    $publicNoticesResponse = $this->ShowPublic();
 
   
    $publicNotices = $publicNoticesResponse->original['notices'] ?? [];
 
    return response()->json([
        'success' => true,
        'data' => [
            'team_members' => $teamMembers,
            'yesterday_attendance' => $attendance,
            'pending_leaves' => $pendingLeaves,
            'public_notices' => $publicNotices
        ]
    ]);
}
}