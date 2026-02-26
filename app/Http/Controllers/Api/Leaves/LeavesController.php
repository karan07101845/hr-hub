<?php

namespace App\Http\Controllers\API\Leaves;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Leaves;
use Illuminate\Support\Facades\Auth;

class LeavesController extends Controller
{
    /**
     * Apply Leave
     */
    public function apply(Request $request)
    {
        $request->validate([
            'leave_type' => 'required|string',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'reason'     => 'required|string',
        ]);

        try {

            $user = Auth::user();

            $leave = Leaves::create([
                'user_id'    => $user->id,
                'manager_id' => $user->manager_id ?? null,
                'leave_type' => $request->leave_type,
                'start_date' => $request->start_date,
                'end_date'   => $request->end_date,
                'reason'     => $request->reason,
                'status'     => 'pending',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Leave request sent successfully.',
                'data'    => $leave
            ], 201);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get My Leaves
     */
    public function myLeaves()
    {
        $leaves = Leaves::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $leaves
        ]);
    }

    /**
     * Get All Leaves (For Manager / Admin / Team Leader)
     */
    public function allLeaves()
    {
        $leaves = Leaves::with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $leaves
        ]);
    }

    /**
     * Approve / Reject Leave
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status'  => 'required|in:approved,rejected',
            'remarks' => 'nullable|string'
        ]);

        $leave = Leaves::find($id);

        if (!$leave) {
            return response()->json([
                'success' => false,
                'message' => 'Leave not found.'
            ], 404);
        }

        $leave->update([
            'status'      => $request->status,
            'remarks'     => $request->remarks,
            'approved_by' => Auth::id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Leave status updated successfully.',
            'data'    => $leave
        ]);
    }

    /**
     * Delete Leave (Optional)
     */
    public function destroy($id)
    {
        $leave = Leaves::find($id);

        if (!$leave) {
            return response()->json([
                'success' => false,
                'message' => 'Leave not found.'
            ], 404);
        }

        $leave->delete();

        return response()->json([
            'success' => true,
            'message' => 'Leave deleted successfully.'
        ]);
    }
}