<?php

namespace App\Http\Controllers;

use App\Models\Societe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SocieteController extends Controller
{
    /**
     * Display the first société resource.
     */
    public function index()
    {
        return Societe::first();
    }

    /**
     * Store a newly created société.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'societe_nom' => 'required|string|max:255',
            'president_date_naissance' => 'required|date',
            'president_cin' => 'required|string|max:255',
            'president_adresse' => 'required|string|max:255',
            'nom_complet_francais' => 'required|string|max:255',
            'nom_complet_arabe' => 'required|string|max:255',
            'adresse_siege_social_francais' => 'required|string',
            'adresse_siege_social_arabe' => 'required|string',
            'ice' => 'required|string|max:50',
            'rc' => 'required|string|max:50',
            'identifiant_fiscal' => 'required|string|max:50',
            'tp' => 'required|string|max:50',
            'telephone' => 'required|string|max:20',
            'email' => 'required|email',
            'logo' => 'nullable|file|mimes:jpeg,png,gif,svg|max:2048',
            'icon' => 'nullable|file|mimes:jpeg,png,gif,svg|max:2048',
            'website' => 'nullable|string',
            'capital_social' => 'required|numeric',
            'type_entreprise' => 'sometimes|required|in:MORALE,PHYSIQUE',
            'form_juridique' => 'sometimes|required|string',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('societes', 'public');
        }

        if ($request->hasFile('icon')) {
            $validated['icon'] = $request->file('icon')->store('societes', 'public');
        }

        $societe = Societe::create($validated);

        return response()->json([
            'message' => 'Société créée avec succès',
            'data' => $societe
        ], 201);
    }

    /**
     * Display the specified société.
     */
    public function show($id)
    {
        return Societe::findOrFail($id);
    }

    /**
     * Update the specified société.
     */
    public function update(Request $request, $id)
    {
        $societe = Societe::findOrFail($id);

        $validated = $request->validate([
            'societe_nom' => 'required|string|max:255',
            'president_date_naissance' => 'required|date',
            'president_cin' => 'required|string|max:255',
            'president_adresse' => 'required|string|max:255',
            'nom_complet_francais' => 'required|string|max:255',
            'nom_complet_arabe' => 'required|string|max:255',
            'adresse_siege_social_francais' => 'sometimes|required|string',
            'adresse_siege_social_arabe' => 'sometimes|required|string',
            'ice' => 'sometimes|required|string|max:50',
            'rc' => 'sometimes|required|string|max:50',
            'identifiant_fiscal' => 'sometimes|required|string|max:50',
            'tp' => 'sometimes|required|string|max:50',
            'telephone' => 'sometimes|required|string|max:20',
            'email' => 'sometimes|required|email',
            'logo' => 'nullable|file|mimes:jpeg,png,gif,svg|max:2048',
            'icon' => 'nullable|file|mimes:jpeg,png,gif,svg|max:2048',
            'website' => 'nullable|string',
            'capital_social' => 'sometimes|required|numeric',
            'type_entreprise' => 'sometimes|required|in:MORALE,PHYSIQUE',
            'form_juridique' => 'sometimes|required|string',
        ]);

        // Supprimer l'ancien logo si un nouveau est envoyé
        if ($request->hasFile('logo')) {
            if ($societe->logo) {
                Storage::disk('public')->delete($societe->logo);
            }
            $validated['logo'] = $request->file('logo')->store('societes', 'public');
        }

        // Supprimer l'ancien icon si un nouveau est envoyé
        if ($request->hasFile('icon')) {
            if ($societe->icon) {
                Storage::disk('public')->delete($societe->icon);
            }
            $validated['icon'] = $request->file('icon')->store('societes', 'public');
        }

        $societe->update($validated);

        return response()->json([
            'message' => 'Société mise à jour avec succès',
            'data' => $societe
        ], 200);
    }

    /**
     * Remove the specified société.
     */
    public function destroy($id)
    {
        $societe = Societe::findOrFail($id);

        // Supprimer les fichiers stockés
        if ($societe->logo) {
            Storage::disk('public')->delete($societe->logo);
        }
        if ($societe->icon) {
            Storage::disk('public')->delete($societe->icon);
        }

        $societe->delete();

        return response()->json(['message' => 'Société supprimée avec succès.']);
    }
}