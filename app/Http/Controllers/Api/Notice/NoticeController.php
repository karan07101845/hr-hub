<?php

namespace App\Http\Controllers\Api\Notice;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;


use Illuminate\Http\Request;

class NoticeController extends Controller
{


    public function list()

    {

        $notices = Notice::orderBy('created_at', 'desc')->get();

        return response()->json(['data' => $notices]);
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'expired_at' => 'nullable|date',
            'type' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'notice_users' => 'nullable|array',
            'notice_users.*.user_id' => 'required|exists:users,id',
            'notice_users.*.seen' => 'required|boolean',
            'notice_users.*.seen_at' => 'nullable|date',
            'notice_type' => 'required|in:private,public',


        ]);
        //     $test=;
        // print_r($test);
        // dd('hheh');
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        // Handle Image Upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('notice', 'public');
        }

        $notice = Notice::create([
            'title' => $request->title,
            'description' => $request->description,
            'created_by' => Auth::id(),
            'expired_at' => $request->expired_at,
            'type' => $request->type,
            'status' => $request->status,
            'image' => $imagePath,
            'notice_users' => json_encode($request->notice_users),
            'notice_type' => $request->notice_type,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notice created successfully',
            'data'    => $notice
        ], 201);
    }

    public function edit($id)
    {
        $notice = Notice::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $notice
        ]);
    }

    public function update(Request $request, $id)
    {
        $notice = Notice::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'expired_at' => 'nullable|date',
            'type' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'notice_users' => 'nullable|array',
            'notice_users.*.user_id' => 'required|exists:users,id',
            'notice_users.*.seen' => 'required|boolean',
            'notice_users.*.seen_at' => 'nullable|date',
            'notice_type' => 'required|in:private,public',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $data = $request->only([
            'title',
            'description',
            'expired_at',
            'type',
            'status',
            'notice_type',
        ]);

        // Image update
        if ($request->hasFile('image')) {
            if ($notice->image && Storage::disk('public')->exists($notice->image)) {
                Storage::disk('public')->delete($notice->image);
            }

            $data['image'] = $request->file('image')->store('notice', 'public');
        }

        // notice_users update
        if ($request->has('notice_users')) {
            $data['notice_users'] = json_encode($request->notice_users);
        }

        $notice->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Notice updated successfully',
            'data' => $notice
        ]);
    }


    public function destroy($id)

    {

        Notice::findOrFail($id)->delete();

        return response()->json(['message' => 'Notice deleted successfully']);
    }


    public function toggle($id)

    {

        $notice = Notice::findOrFail($id);

        $notice->status = $notice->status === 'active' ? 'inactive' : 'active';

        $notice->save();

        return response()->json(['message' => 'Notice status updated']);
    }

    public function getByType($notice_type)
    {
        // Validate allowed types
        if (!in_array($notice_type, ['public', 'private'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid notice type'
            ], 400);
        }

        $notices = Notice::where('notice_type', $notice_type)

            ->get();

        return response()->json([
            'success' => true,
            'type' => $notice_type,
            'data' => $notices
        ]);
    }

    public function show($id)
    {
        $notice = Notice::getByIdSelected($id);

        if (!$notice) {
            return response()->json([
                'success' => false,
                'message' => 'Notice not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $notice,
            'image' => $notice->image ? asset('storage/' . $notice->image) : null,
        ]);
    }
}
