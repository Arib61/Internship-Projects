<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Auth;
use App\Models\User;


class CheckTokenExpiration
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

if (!$user instanceof User) {
    return response()->json(['message' => 'Invalid user object'], 401);
}

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Récupérer le token via l'en-tête Authorization Bearer
        $token = $request->user()->currentAccessToken();

        if (!$token) {
            return response()->json(['message' => 'Token not found.'], 401);
        }

        if ($token->expires_at && $token->expires_at->isPast()) {
            // Mettre à jour le status de l'utilisateur
            $user->status = 'INACTIVE';
            $user->save();

            // Supprimer le token expiré
            $token->delete();

            return response()->json(['message' => 'Token expired, user set to inactive.'], 401);
        }

        return $next($request);
    }
}
