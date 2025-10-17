<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index()
    {
        return ActivityLog::with('user')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:eryx_users,id',
            'action' => 'required|string|max:100',
            'entity_type' => 'required|string|max:50',
            'entity_id' => 'required|integer',
            'old_values' => 'nullable|array',
            'new_values' => 'nullable|array',
            'ip_address' => 'nullable|string|max:45',
            'user_agent' => 'nullable|string',
        ]);

        $log = ActivityLog::create($validated);

        return response()->json($log, 201);
    }

    public function show(ActivityLog $activityLog)
    {
        return $activityLog->load('user');
    }

    public function update(Request $request, ActivityLog $activityLog)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:eryx_users,id',
            'action' => 'required|string|max:100',
            'entity_type' => 'required|string|max:50',
            'entity_id' => 'required|integer',
            'old_values' => 'nullable|array',
            'new_values' => 'nullable|array',
            'ip_address' => 'nullable|string|max:45',
            'user_agent' => 'nullable|string',
        ]);

        $activityLog->update($validated);

        return response()->json($activityLog);
    }

    public function destroy(ActivityLog $activityLog)
    {
        $activityLog->delete();
        return response()->json(['message' => 'Log supprimé']);
    }
}
