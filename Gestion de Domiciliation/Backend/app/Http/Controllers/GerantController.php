<?php
namespace App\Http\Controllers;

use App\Models\Gerant;
use Illuminate\Http\Request;

class GerantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Gerant::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'cin' => 'required|string|max:20|unique:eryx_gerants',
            'address' => 'nullable|string',
            'date_naissance' => 'nullable|date',
            'telephone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'photo_url' => 'nullable|string|max:255',
        ]);

        $gerant = Gerant::create($validated);

        return response()->json([
            'message' => 'Gérant créé avec succès',
            'data' => $gerant
        ], 201);
    }

    public function show($id)
    {
        return Gerant::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $gerant = Gerant::findOrFail($id);

        $validated = $request->validate([
            'nom' => 'sometimes|required|string|max:255',
            'cin' => 'sometimes|required|string|max:20|unique:eryx_gerants,cin,' . $gerant->id,
            'address' => 'nullable|string',
            'date_naissance' => 'nullable|date',
            'telephone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'photo_url' => 'nullable|string|max:255',
        ]);

        $gerant->update($validated);

        return response()->json([
            'message' => 'Gérant mis à jour avec succès',
            'data' => $gerant
        ], 200);
    }

    public function destroy($id)
    {
        $gerant = Gerant::findOrFail($id);
        $gerant->delete();

        return response()->json(['message' => 'Gérant supprimé avec succès.']);
    }
}
