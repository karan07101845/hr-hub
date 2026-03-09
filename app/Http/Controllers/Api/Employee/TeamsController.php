<?php

namespace App\Http\Controllers\Api\Employee;

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
        $teams = Teams::with('manager')->get();

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
        $team = Teams::with('manager')->findOrFail($id);

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
        $team = Teams::findOrFail($id);
        $team->delete();

        return response()->json([
            'success' => true,
            'message' => 'Team deleted successfully'
        ]);
    }
}
