<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
    public function index()
    {
        return Client::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom_francais' => 'required|string|max:255',
            'nom_arabe' => 'required|string|max:255',
            'adresse_siege_social_francais' => 'required|string',
            'adresse_siege_social_arabe' => 'required|string',
            'ice' => 'required|string|max:50',
            'rc' => 'required|string|max:50',
            'identifiant_fiscal' => 'required|string|max:50',
            'tp' => 'required|string|max:50',
            'tribunal' => 'required|string|max:100',
            'type_tribunal' => 'required|string|max:100',
            'telephone' => 'required|string|max:20',
            'email' => 'required|email',
            'pays' => 'required|string|max:100',
            'ville' => 'required|string|max:100',
            'website' => 'nullable|string',
            'capital_social' => 'required|numeric',
            'type_entreprise' => 'required|in:MORALE,PHYSIQUE',
            'type_client' => 'required|string',
            'status' => 'nullable|in:PROSPECT,ACTIVE,INACTIVE',
            'gerant_id' => 'nullable|exists:eryx_gerants,id',
            'gestionnaire_id' => 'nullable|exists:eryx_gestionnaires,id',
            'certificat_negative' => 'nullable|file|mimes:pdf,jpeg,png|max:2048',
            'cin' => 'nullable|file|mimes:pdf,jpeg,png|max:2048',
        ]);

        // Gestion du certificat négatif
        if ($request->hasFile('certificat_negative')) {
            $validated['certificat_negative'] = $request->file('certificat_negative')->store('clients', 'public');
        }

        // Gestion du CIN
        if ($request->hasFile('cin')) {
            $validated['cin'] = $request->file('cin')->store('clients', 'public');
        }

        $client = Client::create($validated);

        return response()->json([
            'message' => 'Client créé avec succès',
            'data' => $client
        ], 201);
    }

    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'nom_francais' => 'sometimes|required|string|max:255',
            'nom_arabe' => 'sometimes|required|string|max:255',
            'adresse_siege_social_francais' => 'sometimes|required|string',
            'adresse_siege_social_arabe' => 'sometimes|required|string',
            'ice' => 'sometimes|required|string|max:50',
            'rc' => 'sometimes|required|string|max:50',
            'identifiant_fiscal' => 'sometimes|required|string|max:50',
            'tp' => 'sometimes|required|string|max:50',
            'tribunal' => 'sometimes|required|string|max:100',
            'type_tribunal' => 'sometimes|required|string|max:100',
            'telephone' => 'sometimes|required|string|max:20',
            'email' => 'sometimes|required|email',
            'pays' => 'sometimes|required|string|max:100',
            'ville' => 'sometimes|required|string|max:100',
            'website' => 'nullable|string',
            'capital_social' => 'sometimes|required|numeric',
            'type_entreprise' => 'sometimes|required|in:MORALE,PHYSIQUE',
            'type_client' => 'sometimes|required|string',
            'status' => 'nullable|in:PROSPECT,ACTIVE,INACTIVE',
            'gerant_id' => 'nullable|exists:eryx_gerants,id',
            'gestionnaire_id' => 'nullable|exists:eryx_gestionnaires,id',
            'certificat_negative' => 'nullable|file|mimes:pdf,jpeg,png|max:2048',
            'cin' => 'nullable|file|mimes:pdf,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('certificat_negative')) {
            if ($client->certificat_negative) {
                Storage::disk('public')->delete($client->certificat_negative);
            }
            $validated['certificat_negative'] = $request->file('certificat_negative')->store('clients', 'public');
        }
        // Gestion du CIN
        if ($request->hasFile('cin')) {
            if ($client->certificat_negative) {
                Storage::disk('public')->delete($client->certificat_negative);
            }
            $validated['cin'] = $request->file('cin')->store('clients', 'public');
        }
        $client->update($validated);

        return response()->json([
            'message' => 'Client mis à jour avec succès',
            'data' => $client
        ], 200);
    }

    public function show($id)
    {
        try {
            $client = Client::findOrFail($id);
            return response()->json($client);
        } catch (\Exception $e) {
            \Log::error('Client show error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Client $client)
    {
        $user = Auth::user();

        if (!$user || !$user->is_admin) {
            return response()->json(['message' => 'Action non autorisée. Seuls les administrateurs peuvent supprimer des clients.'], 403);
        }

        if ($client->certificat_negative) {
            Storage::disk('public')->delete($client->certificat_negative);
        }

        if ($client->cin) {
            Storage::disk('public')->delete($client->cin);
        }

        $client->delete();

        return response()->json(['message' => 'Client supprimé avec succès.'], 204);
    }
}
