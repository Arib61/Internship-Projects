<?php

namespace App\Http\Controllers;
use App\Models\Gestionnaire;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return User::all();
    }


public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required',
        'email' => 'required|email|unique:eryx_users',
        'password' => 'required|min:6',

    ]);

    if (!empty($validated['is_admin']) && $validated['is_admin'] === true) {
        $adminCount = User::where('is_admin', true)->count();
        if ($adminCount >= 1) {
            throw ValidationException::withMessages([
                'is_admin' => ['Il ne peut y avoir qu’un seul administrateur.']
            ]);
        }

    }

    $validated['password'] = Hash::make($validated['password']);



    try {
        $user = User::create($validated);
    } catch (QueryException $e) {
        if (str_contains($e->getMessage(), 'Limite de 4 gestionnaires non admin atteinte')) {
            return response()->json([
                'message' => 'Vous ne pouvez pas créer plus de 4 gestionnaires non administrateurs.'
            ], 422); // or 400
        }

        throw $e;
    }


    return response()->json([
        'message' => 'Utilisateur créé avec succès',
        'user' => [
            "id" => $user->id,
        ]
    ], 201);
}


    public function show($id)
    {
        return User::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $data = $request->only(['name', 'email', 'status', 'is_admin']);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return response()->json([
            'message' => 'Utilisateur mis à jour avec succès',
            'data' => $user
        ]);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json([
            'message' => 'Utilisateur supprimé avec succès'
        ]);
    }
}
