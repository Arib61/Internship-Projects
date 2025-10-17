<?php

namespace App\Http\Controllers;

use App\Models\Gestionnaire;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GestionnaireController extends Controller
{
    /**
     * Récupérer l'utilisateur via Bearer token
     */
    private function getAuthenticatedUser(Request $request)
    {
        $token = $request->header('Authorization');
        if ($token && str_starts_with($token, 'Bearer ')) {
            $token = substr($token, 7);
        }

        return User::where('remember_token', $token)->first();
    }

    public function index(Request $request)
    {
        $user = $this->getAuthenticatedUser($request);

        if (!$user) {
            return response()->json(['error' => 'Non authentifié'], 401);
        }

        $query = Gestionnaire::with('user');

        if (!$user->is_admin) {
            $query->where('user_id', $user->id);
        }

        $gestionnaires = $query->get()->map(function ($gestionnaire) {
            return [
                'id' => $gestionnaire->id,
                'nom' => $gestionnaire->nom,
                'prenom' => $gestionnaire->prenom,
                'user_id' => $gestionnaire->user_id,
                'full_name' => $gestionnaire->nom . ' ' . $gestionnaire->prenom
            ];
        });

       
        return  response()->json(['gestionnaires' =>  $gestionnaires , 'role' => $user->is_admin ]);
    }
    public function store(Request $request)
    {
        $user = $this->getAuthenticatedUser($request);
        if (!$user || !$user->is_admin) {
            return response()->json(['error' => 'Accès refusé.'], 403);
        }

        $gestionnaireCount = Gestionnaire::whereHas('user', function ($query) {
            $query->where('is_admin', false);
        })->count();

        if ($gestionnaireCount >= 4) {
            return response()->json([
                'message' => 'Le nombre maximum de gestionnaires non admin (4) est atteint.'
            ], 400);
        }

        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:eryx_gestionnaires,email',
            'telephone' => 'nullable|string|max:20',
            'adresse' => 'nullable|string',
            'ville' => 'nullable|string|max:100',
            'photo_url' => 'nullable|string|max:255',
            'user_id' => 'required|exists:eryx_users,id',
        ]);

        $gestionnaire = Gestionnaire::create($validated);

        return response()->json([
            'message' => 'Gestionnaire créé avec succès',
            'data' => $gestionnaire
        ], 201);
    }

    public function show($id)
    {
        return Gestionnaire::with('user')->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $user = $this->getAuthenticatedUser($request);
        if (!$user || !$user->is_admin) {
            return response()->json(['error' => 'Accès refusé.'], 403);
        }

        $gestionnaire = Gestionnaire::findOrFail($id);

        $validated = $request->validate([
            'nom' => 'sometimes|required|string|max:255',
            'prenom' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:eryx_gestionnaires,email,' . $id,
            'telephone' => 'nullable|string|max:20',
            'adresse' => 'nullable|string',
            'ville' => 'nullable|string|max:100',
            'photo_url' => 'nullable|string|max:255',
            'user_id' => 'sometimes|required|exists:eryx_users,id',
        ]);

        $gestionnaire->update($validated);

        return response()->json([
            'message' => 'Gestionnaire mis à jour avec succès',
            'data' => $gestionnaire
        ], 200);
    }

    public function destroy($id, Request $request)
    {
        $user = $this->getAuthenticatedUser($request);
        if (!$user || !$user->is_admin) {
            return response()->json(['error' => 'Accès refusé.'], 403);
        }

        $gestionnaire = Gestionnaire::findOrFail($id);
        $gestionnaire->delete();

        return response()->json(['message' => 'Gestionnaire supprimé avec succès.']);
    }
}
