<?php
 
namespace App\Http\Controllers\API\Notification;
 
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
 
use Illuminate\Http\Request;
use App\Models\Notifications;
use App\Models\User;
 
 
 
 
class NotificationController extends Controller
{
    public function addNotification(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required',
            'type' => 'required|string',
            'message' => 'required|string',
            'link' => 'required|string',
            'is_read' => 'required|boolean'
        ]);
 
        $validated['user_id'] = Auth::id();
 
        Notifications::create($validated);
 
        return response()->json([
            'message' => 'Notification created successfully'
        ]);
    }
 
    public function updateNotification(Request $request, $id)
    {
        $notification = Notifications::findorfail($id);
        $validated = $request->validate([
            'type' => 'required|string',
            'message' => 'required|string',
            'link' => 'required|string',
            'is_read' => 'required|boolean'
        ]);
 
        $validated['user_id'] = $notification->user_id;
 
        $notification->fill($validated);
        $notification->save();
 
        return response()->json([
            'message' => 'Notification updated successfully',
            'data' => $notification
        ]);
    }
}