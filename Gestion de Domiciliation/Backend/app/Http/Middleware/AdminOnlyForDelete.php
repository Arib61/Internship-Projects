<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // ✅ Import nécessaire
use Symfony\Component\HttpFoundation\Response;

class AdminOnlyForDelete
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('delete')) {
            $user = Auth::user(); // ✅ Utilise la façade Auth correctement

            if (!$user || !$user->is_admin) {
                return response()->json([
                    'message' => 'Action non autorisée. Seuls les administrateurs peuvent effectuer cette action.'
                ], 403);
            }
        }

        return $next($request);
    }
}
