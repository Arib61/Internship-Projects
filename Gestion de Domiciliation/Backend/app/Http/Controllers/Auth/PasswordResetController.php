<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Events\PasswordReset;

class PasswordResetController extends Controller
{
    /**
     * Envoyer le lien de réinitialisation de mot de passe
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        // Envoyer le lien de réinitialisation
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => 'Un lien de réinitialisation a été envoyé à votre adresse email.',
                'status' => 'success'
            ]);
        }

        // Toujours retourner un message de succès pour des raisons de sécurité
        return response()->json([
            'message' => 'Si cette adresse email existe dans notre système, vous recevrez un lien de réinitialisation.',
            'status' => 'success'
        ]);
    }

    /**
     * Réinitialiser le mot de passe
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'message' => 'Votre mot de passe a été réinitialisé avec succès.',
                'status' => 'success'
            ]);
        }

        return response()->json([
            'message' => 'Le lien de réinitialisation est invalide ou a expiré.',
            'status' => 'error'
        ], 400);
    }

    /**
     * Vérifier la validité du token
     */
    public function validateToken(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
        ]);

        $user = \App\Models\User::where('email', $request->email)->first();
        
        if (!$user) {
            return response()->json([
                'message' => 'Token invalide.',
                'status' => 'error'
            ], 400);
        }

        $tokenExists = \DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$tokenExists) {
            return response()->json([
                'message' => 'Token invalide ou expiré.',
                'status' => 'error'
            ], 400);
        }

        return response()->json([
            'message' => 'Token valide.',
            'status' => 'success'
        ]);
    }
}
