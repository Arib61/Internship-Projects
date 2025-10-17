<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Barryvdh\DomPDF\Facade\Pdf; 
use Illuminate\Support\Facades\Response;

class ProfileController extends Controller
{
    /**
     * Afficher les informations du profil
     */
    public function show(Request $request)
    {
        return response()->json($request->user());
    }

    /**
     * Mettre à jour les informations du profil
     */
    public function update(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json(['error' => 'Utilisateur non authentifié'], 401);
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => [
                    'required',
                    'string',
                    'email',
                    'max:255',
                    Rule::unique('eryx_users')->ignore($user->id),
                ],
            ]);

            $user->fill($validated);

            if ($user->isDirty('email')) {
                $user->email_verified_at = null;

                try {
                    $user->sendEmailVerificationNotification();
                } catch (\Throwable $mailError) {
                    return response()->json([
                        'error' => 'Erreur lors de l’envoi de l’e-mail de vérification.',
                        'message' => $mailError->getMessage(),
                        'file' => $mailError->getFile(),
                        'line' => $mailError->getLine(),
                        'trace' => $mailError->getTraceAsString()
                    ], 500);
                }
            }

            $user->save();

            return response()->json($user);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json([
                'error' => 'Erreur de validation',
                'messages' => $ve->errors()
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Erreur serveur',
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Mettre à jour l'avatar
     */
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
        ]);

        $user = $request->user();

        if ($user->avatar) {
            Storage::delete($user->avatar);
        }

        $path = $request->file('avatar')->store('avatars');

        $user->avatar = $path;
        $user->save();

        return response()->json([
            'avatar' => Storage::url($path)
        ]);
    }

    /**
     * Mettre à jour le mot de passe
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();
        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json(['message' => 'Mot de passe mis à jour avec succès.']);
    }

    /**
     * Exporter les données utilisateur
     */
    public function export(Request $request)
    {
        $user = $request->user();
        $data = $user->toArray();
        $data['created_at_formatted'] = $user->created_at->format('d/m/Y H:i');

        $pdf = Pdf::loadView('pdf.user_export', ['user' => $data]);

        return $pdf->download('mes-donnees-' . now()->format('Y-m-d') . '.pdf');
    }
}
