<?php

namespace App\Http\Controllers\Api\Employee;

use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Teams;
use App\Models\User;

class TeamsController extends Controller
{
    /**
     * Display all teams
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            $teams = Teams::with('manager')->get();
        } elseif ($user->role === 'manager') {
            $teams = Teams::with('manager')
                ->where('manager_id', $user->id)
                ->get();
        } elseif ($user->role === 'team_leader') {
            $teams = Teams::whereJsonContains('team_leaders', $user->id)
                ->with('manager')
                ->get();
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $teams
        ]);
    }

    /**
     * Store new team
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'manager_id' => 'nullable|exists:users,id',
            'team_leaders' => 'nullable|array'
        ]);

        $user = Auth::user();

        if ($user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Only admin can create teams'
            ], 403);
        }

        $leaders = $request->team_leaders ?? [];

        if (!empty($leaders)) {

            // Check if users exist AND are team leaders
            $validLeaders = User::whereIn('id', $leaders)
                ->where('role', 'team_leader')
                ->pluck('id')
                ->toArray();

            if (count($validLeaders) !== count($leaders)) {
                return response()->json([
                    'success' => false,
                    'message' => 'One or more selected users are not valid team leaders'
                ], 422);
            }

            // 🔥 Check if leader already assigned in another team
            $alreadyAssigned = Teams::where(function ($query) use ($leaders) {
                foreach ($leaders as $leader) {
                    $query->orWhereJsonContains('team_leaders', $leader);
                }
            })->exists();

            if ($alreadyAssigned) {
                return response()->json([
                    'success' => false,
                    'message' => 'One or more team leaders are already assigned to another team'
                ], 422);
            }
        }

        $team = Teams::create([
            'name' => $request->name,
            'description' => $request->description,
            'manager_id' => $request->manager_id,
            'team_leaders' => $leaders
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Team created successfully',
            'data' => $team
        ]);
    }
    /**
     * Show single team
     */
    public function show($id)
    {
        $user = Auth::user();
        $team = Teams::with('manager')->findOrFail($id);

        if ($user->role === 'manager' && $team->manager_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You can only view your own team'
            ], 403);
        }

        if ($user->role === 'team_leader' && !in_array($user->id, $team->team_leaders ?? [])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $team
        ]);
    }

    /**
     * Update team
     */
    public function update(Request $request, $id)
    {

        $user = Auth::user();

        if ($user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Only admin can update teams'
            ], 403);
        }
        $team = Teams::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'manager_id' => 'nullable|exists:users,id',
            'team_leaders' => 'nullable|array'
        ]);

        $leaders = $request->team_leaders ?? [];

        if (!empty($leaders)) {

            // Validate role
            $validLeaders = User::whereIn('id', $leaders)
                ->where('role', 'team_leader')
                ->pluck('id')
                ->toArray();

            if (count($validLeaders) !== count($leaders)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid team leader selected'
                ], 422);
            }

            // 🔥 Check if assigned in another team (excluding current team)
            $alreadyAssigned = Teams::where('id', '!=', $id)
                ->where(function ($query) use ($leaders) {
                    foreach ($leaders as $leader) {
                        $query->orWhereJsonContains('team_leaders', $leader);
                    }
                })
                ->exists();

            if ($alreadyAssigned) {
                return response()->json([
                    'success' => false,
                    'message' => 'One or more team leaders are already assigned to another team'
                ], 422);
            }
        }

        $team->update([
            'name' => $request->name,
            'description' => $request->description,
            'manager_id' => $request->manager_id,
            'team_leaders' => $leaders
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Team updated successfully',
            'data' => $team
        ]);
    }
    /**
     * Delete team
     */
    public function destroy($id)
    {
        $user = Auth::user();

        if ($user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Only admin can delete teams'
            ], 403);
        }
        $team = Teams::findOrFail($id);
        $team->delete();

        return response()->json([
            'success' => true,
            'message' => 'Team deleted successfully'
        ]);
    }

    // Assign member to a Team

    public function addMembers(Request $request, $id)
    {
        $request->validate([
            'members' => 'required|array',
            'members.*' => 'exists:users,id'
        ]);

        $user = Auth::user();

        if (!in_array($user->role, ['admin', 'manager'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to add members'
            ], 403);
        }

        $team = Teams::findOrFail($id);

        if ($user->role === 'manager' && $team->manager_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You can only manage your own team'
            ], 403);
        }

        $members = User::whereIn('id', $request->members)
            ->where('role', 'employee')
            ->pluck('id');

        if ($members->count() !== count($request->members)) {
            return response()->json([
                'success' => false,
                'message' => 'Only employees can be added as team members'
            ], 422);
        }

        User::whereIn('id', $members)->update(['team_id' => $team->id]);

        return response()->json([
            'success' => true,
            'message' => 'Members added to team successfully'
        ]);
    }
}
