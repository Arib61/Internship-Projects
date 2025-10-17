<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ResiliationBureauEquipe;
use Illuminate\Support\Facades\DB;
use App\Models\BureauEquipe;
use App\Models\Societe;

class ResiliationBureauEquipeController extends Controller
{
    private function getAuthenticatedUser(Request $request)
    {
        $token = $request->header('Authorization');
        if ($token && str_starts_with($token, 'Bearer ')) {
            $token = substr($token, 7);
        }

        return User::where('remember_token', $token)->first();
    }

    // 🟢 Lister toutes les résiliations
    public function index(Request $request)
    {
        $user = $this->getAuthenticatedUser($request);
        if (!$user) return response()->json(['error' => 'Unauthorized'], 401);

        $query = ResiliationBureauEquipe::with('bureauEquipe');

        if (!$user->is_admin) {
            $query->whereHas('bureauEquipe', function ($q) use ($user) {
                $q->where('created_by', $user->id)
                  ->orWhere('gestionnaire_id', $user->id);
            });
        }

        return response()->json($query->get());
    }

    // 🟢 Afficher une résiliation
    public function show(Request $request, $id)
    {
        $user = $this->getAuthenticatedUser($request);
        if (!$user) return response()->json(['error' => 'Unauthorized'], 401);

        $resiliation = ResiliationBureauEquipe::with('bureauEquipe')->findOrFail($id);

        $bureau = $resiliation->bureauEquipe;

        if (!$user->is_admin && $bureau->created_by != $user->id && $bureau->gestionnaire_id != $user->id) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        return response()->json($resiliation);
    }

    // 🟢 Créer une nouvelle résiliation
    public function store(Request $request)
    {
        $user = $this->getAuthenticatedUser($request);
        if (!$user) return response()->json(["error" => "Unauthorized"], 401);

        $validated = $request->validate([
            'bureaux_equipe_id' => 'required|exists:eryx_bureaux_equipe,id',
            'date_resiliation' => 'required|date',
            'raison' => 'required|string',
            'status' => 'required|in:PENDING,APPROVED,REJECTED',
        ]);

        $resiliation = ResiliationBureauEquipe::create([
            'bureaux_equipe_id' => $validated['bureaux_equipe_id'],
            'date_resiliation' => $validated['date_resiliation'],
            'raison' => $validated['raison'],
            'status' => $validated['status'],
            'created_by' => $user->id,
        ]);

        return response()->json($resiliation, 201);
    }

    // 🟢 Mettre à jour une résiliation
    public function update(Request $request, $id)
    {
        $user = $this->getAuthenticatedUser($request);
        if (!$user) return response()->json(["error" => "Unauthorized"], 401);

        $resiliation = ResiliationBureauEquipe::with('bureauEquipe')->findOrFail($id);

        $bureau = $resiliation->bureauEquipe;
        if (!$user->is_admin && $bureau->created_by != $user->id && $bureau->gestionnaire_id != $user->id) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'date_resiliation' => 'sometimes|date',
            'raison' => 'sometimes|string',
            'status' => 'sometimes|in:PENDING,APPROVED,REJECTED',
        ]);

        $resiliation->update($validated);
        return response()->json($resiliation);
    }

    // 🟢 Supprimer (soft delete)
    public function destroy(Request $request, $id)
    {
        $user = $this->getAuthenticatedUser($request);
        if (!$user) return response()->json(["error" => "Unauthorized"], 401);
        if (!$user->is_admin) return response()->json(["error" => "Forbidden"], 403);

        $resiliation = ResiliationBureauEquipe::findOrFail($id);
        $resiliation->delete();

        return response()->json(['message' => 'Résiliation supprimée.']);
    }

     public function generateContractPdf($id)
    {
        try {
            $bureau = BureauEquipe::with(['client', 'gestionnaire'])->findOrFail($id);
            $societe = Societe::first();
            $client = $bureau->client;

            $data = compact(
                'societe',
                'bureau',
                'client'
            );

            $htmlContract = view('templates.bureauEquipeResiliationContract', $data)->render();

            return response()->json([
                'htmlContract' => $htmlContract,
            ], 200, [
                'Content-Type' => 'application/json',
                'Cache-Control' => 'no-cache',
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur génération HTML', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['error' => 'Une erreur interne est survenue.'], 500);
        }
    }

    public function generateContractWord($id)
    {
        try {
            $bureau = BureauEquipe::with(['client', 'gestionnaire'])->findOrFail($id);
            $societe = Societe::first();
            $client = $bureau->client;

            $data = compact(
                'bureau',
                'societe',
                'client',
            );

            // Return raw HTML to frontend
            return response()->json([
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur génération HTML', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }
}
