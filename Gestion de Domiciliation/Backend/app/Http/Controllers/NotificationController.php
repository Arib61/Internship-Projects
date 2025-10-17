<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // Liste toutes les notifications
    public function index()
    {
        return Notification::all();
    }

    // Crée une nouvelle notification
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:eryx_users,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|string|max:50',
            'read' => 'boolean',
            'data' => 'nullable|array',
        ]);

        $notification = Notification::create($validated);

        return response()->json([
            'message' => 'Notification créée avec succès',
            'data' => $notification
        ], 201);
    }

    // Affiche une notification précise
    public function show($id)
    {
        $notification = Notification::findOrFail($id);
        return $notification;
    }

    // Met à jour une notification
    public function update(Request $request, $id)
    {
        $notification = Notification::findOrFail($id);

        $validated = $request->validate([
            'user_id' => 'exists:eryx_users,id',
            'title' => 'string|max:255',
            'message' => 'string',
            'type' => 'string|max:50',
            'read' => 'boolean',
            'data' => 'nullable|array',
        ]);

        $notification->update($validated);

        return response()->json([
            'message' => 'Notification mise à jour avec succès',
            'data' => $notification
        ]);
    }

    // Supprime une notification
    public function destroy($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->delete();

        return response()->json(['message' => 'Notification supprimée avec succès.'], 200);
    }
}
