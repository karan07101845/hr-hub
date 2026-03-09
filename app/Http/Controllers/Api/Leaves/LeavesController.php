<?php

namespace App\Http\Controllers\Api\Leaves;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Leaves;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class LeavesController extends Controller
{

    /**
     * Apply Leave (Employee)
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
     * Get Logged-in User Leaves
     */
    public function myLeaves()
    {
        $leaves = Leaves::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $leaves
        ]);
    }


    /**
     * Get All Leaves (Admin / Manager / Team Leader)
     * Supports status filtering
     */
    public function allLeaves(Request $request)
    {
        $user = Auth::user();

        $query = Leaves::with(['user', 'manager', 'approver'])
            ->orderBy('created_at', 'desc');

        // ROLE BASED FILTER
        if ($user->role === 'admin') {
            // Admin can see all leaves
        } elseif ($user->role === 'manager') {

            $teamMembers = User::where('team_id', $user->team_id)->pluck('id');

            $query->whereIn('user_id', $teamMembers);
        } elseif ($user->role === 'team_leader') {

            $teamMembers = User::where('team_id', $user->team_id)
                ->where('role', 'employee')
                ->pluck('id');

            $query->whereIn('user_id', $teamMembers);
        } else {

            // Employee only their own leaves
            $query->where('user_id', $user->id);
        }

        // STATUS FILTER
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $leaves = $query->get();

        return response()->json([
            'success' => true,
            'data' => $leaves
        ]);
    }


    /**
     * Dashboard Summary Counts
     * Used for Admin Dashboard Buttons
     */
    public function summary()
    {
        $user = Auth::user();

        $query = Leaves::query();

        if ($user->role === 'manager') {

            $teamMembers = User::where('team_id', $user->team_id)->pluck('id');

            $query->whereIn('user_id', $teamMembers);
        } elseif ($user->role === 'team_leader') {

            $teamMembers = User::where('team_id', $user->team_id)
                ->where('role', 'employee')
                ->pluck('id');
            $query->whereIn('user_id', $teamMembers);
        } elseif ($user->role === 'employee') {
            $query->where('user_id', $user->id);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'all'      => $query->count(),
                'pending'  => (clone $query)->where('status', 'pending')->count(),
                'approved' => (clone $query)->where('status', 'approved')->count(),
                'rejected' => (clone $query)->where('status', 'rejected')->count(),
            ]
        ]);
    }

    /**
     * Approve / Reject Leave (Admin / Manager)
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status'  => 'required|in:approved,rejected',
            'remarks' => 'nullable|string'
        ]);

        $user = Auth::user();

        $leave = Leaves::find($id);

        if (!$leave) {
            return response()->json([
                'success' => false,
                'message' => 'Leave not found.'
            ], 404);
        }

        // ROLE CHECK
        if ($user->role === 'admin') {

            // admin can approve any leave
        } elseif ($user->role === 'manager') {

            if ($leave->manager_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only approve your team leaves.'
                ], 403);
            }
        } else {

            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }

        $leave->update([
            'status'      => $request->status,
            'remarks'     => $request->remarks,
            'approved_by' => $user->id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Leave status updated successfully.',
            'data'    => $leave
        ]);
    }


    /**
     * Delete Leave
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

        $user = Auth::user();

        if ($user->role !== 'admin' && $leave->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot delete this leave.'
            ], 403);
        }

        $leave->delete();

        return response()->json([
            'success' => true,
            'message' => 'Leave deleted successfully.'
        ]);
    }
}
