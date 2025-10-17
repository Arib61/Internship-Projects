<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Abonnement;
use Illuminate\Support\Facades\Auth;

class CheckAbonnementExpiration
{
    public function handle($request, Closure $next)
    {
        $abonnement = Abonnement::orderByDesc('date_debut')->first();

        if (!$abonnement || now()->gt($abonnement->date_fin)) {
            // Mise à jour du statut
            if ($abonnement) {
                $abonnement->update([
                    'statut' => 'inactif',
                    'jours_restants' => 0,
                ]);
            }

            // Si utilisateur connecté → le déconnecter
            if (Auth::check()) {
                $user = Auth::user();
                $user->currentAccessToken()?->delete();
            }

            return response()->json([
                'message' => 'Abonnement expiré. Veuillez contacter le support.',
            ], 403);
        } else {
            $joursRestants = now()->diffInDays($abonnement->date_fin);
            $abonnement->update(['jours_restants' => $joursRestants]);
        }

        return $next($request);
    }
}
