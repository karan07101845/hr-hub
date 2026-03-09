<?php

namespace App\Http\Controllers\Api\Attendance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\AttendanceImport;

class AttendanceController extends Controller
{
    /**
     * Get All Attendance (Admin)
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Attendance::with('user')->orderBy('date', 'desc');

        /*
    |--------------------------------------------------------------------------
    | Role Based Data Access
    |--------------------------------------------------------------------------
    */

        /*
|--------------------------------------------------------------------------
| Role Based Data Access
|--------------------------------------------------------------------------
*/

        if ($user->role === 'admin') {
            // Admin sees everything
        } elseif ($user->role === 'manager') {

            // get teams under this manager
            $teamIds = \App\Models\Teams::where('manager_id', $user->id)
                ->pluck('id');

            // get all users from those teams
            $userIds = \App\Models\User::whereIn('team_id', $teamIds)
                ->pluck('id');

            // include manager
            $userIds->push($user->id);

            $query->whereIn('user_id', $userIds);
        } else {
            // Team Leader + Employee → only own attendance
            $query->where('user_id', $user->id);
        }

        /*
    |--------------------------------------------------------------------------
    | Optional Date Filter
    |--------------------------------------------------------------------------
    */

        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('date', [$request->from, $request->to]);
        }

        return response()->json([
            'success' => true,
            'data' => $query->get()
        ]);
    }

    /**
     * Upload Attendance Excel
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048'
        ]);

        Excel::import(new AttendanceImport, $request->file('file'));

        return response()->json([
            'success' => true,
            'message' => 'Attendance uploaded successfully'
        ]);
    }

    /**
     * Get Logged-in User Monthly Report
     */
    public function myReport(Request $request)
    {
        $user = Auth::user();

        $month = $request->query('month', date('m'));
        $year  = $request->query('year', date('Y'));

        $records = Attendance::where('user_id', $user->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->orderBy('date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'records' => $records
        ]);
    }

    /**
     * Get Last Day Status
     */
    public function lastStatus()
    {
        $user = Auth::user();

        $latest = Attendance::where('user_id', $user->id)
            ->orderBy('date', 'desc')
            ->first();

        return response()->json([
            'status' => $latest->status ?? 'A'
        ]);
    }

    public function update(Request $request, $id)
    {
        $attendance = Attendance::find($id);

        if (!$attendance) {
            return response()->json([
                'success' => false,
                'message' => 'Attendance not found'
            ], 404);
        }

        $attendance->update([
            'in_time'     => $request->in_time,
            'out_time'    => $request->out_time,
            'status'      => $request->status,
            'remarks'     => $request->remarks,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Attendance updated successfully',
            'data' => $attendance
        ]);
    }

    public function destroy($id)
    {
        $attendance = Attendance::find($id);

        if (!$attendance) {
            return response()->json([
                'success' => false,
                'message' => 'Attendance not found'
            ], 404);
        }

        $attendance->delete();

        return response()->json([
            'success' => true,
            'message' => 'Attendance deleted successfully'
        ]);
    }
}
