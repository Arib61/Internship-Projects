<?php

namespace App\Http\Controllers;

use App\Models\BureauEquipeHistory;
use Illuminate\Http\Request;
use App\Models\User;

class BureauEquipeHistoryController extends Controller
{
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
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $query = BureauEquipeHistory::with([
            'bureauEquipe:id,reference_numero,created_by,gestionnaire_id',
            'modifier:id,name'
        ]);

        // Restreindre pour les non-admins
        if (!$user->is_admin) {
            $query->whereHas('bureauEquipe', function ($q) use ($user) {
                $q->where('created_by', $user->id)
                  ->orWhere('gestionnaire_id', $user->id);
            });
        }

        // Filtres facultatifs
        if ($request->has('reference')) {
            $query->whereHas('bureauEquipe', function ($q) use ($request) {
                $q->where('reference_numero', 'LIKE', '%' . $request->reference . '%');
            });
        }

        if ($request->has('modified_by')) {
            $query->where('modified_by', $request->modified_by);
        }

        if ($request->has('modification_date')) {
            $query->whereDate('modification_date', $request->modification_date);
        }

        return response()->json($query->get());
    }

    public function show($id)
    {
        $user = $this->getAuthenticatedUser(request());
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $history = BureauEquipeHistory::with(['bureauEquipe', 'modifier'])->findOrFail($id);

        // Vérifie que l'utilisateur a le droit de voir cette ressource
        if (!$user->is_admin &&
            $history->bureauEquipe &&
            $history->bureauEquipe->created_by != $user->id &&
            $history->bureauEquipe->gestionnaire_id != $user->id
        ) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        return response()->json($history);
    }

    public function destroy($id)
    {
        $user = $this->getAuthenticatedUser(request());
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $history = BureauEquipeHistory::with('bureauEquipe')->findOrFail($id);

        // Seuls les admins peuvent supprimer
        if (!$user->is_admin) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $history->delete();
        return response()->json(['message' => 'Historique supprimé avec succès']);
    }
}
