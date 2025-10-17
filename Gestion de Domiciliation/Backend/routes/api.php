<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\BureauEquipeController;
use App\Http\Controllers\BureauEquipeHistoryController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DomiciliationController;
use App\Http\Controllers\DomiciliationHistoryController;
use App\Http\Controllers\GerantController;
use App\Http\Controllers\GestionnaireController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ResiliationBureauEquipeController;
use App\Http\Controllers\ResiliationController;
use App\Http\Controllers\SocieteController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\CheckTokenExpiration;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\DashboardController;

// Enregistrement de middleware (si pas fait ailleurs)
app()->router->aliasMiddleware('check.expiration', CheckTokenExpiration::class);


Route::get('/private-image/{path}', [ClientController::class, 'getPrivateImage'])
    ->where('path', '.*'); // Allows slashes in the path


Route::post('/domiciliations/{id}/renew', [DomiciliationController::class, 'renew']);

// --- Route Inscription ---
Route::post('/register', function (Request $request) {
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:eryx_users',
        'password' => 'required|string|min:6|confirmed',
        'is_admin' => 'boolean',
    ]);

    if (isset($validated['is_admin']) && $validated['is_admin']) {
        $adminCount = User::where('is_admin', true)->count();
        if ($adminCount >= 1) {
            return response()->json(['error' => 'Il ne peut y avoir qu’un seul administrateur.'], 422);
        }
    }

    $user = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => Hash::make($validated['password']),
        'is_admin' => $validated['is_admin'] ?? false,
    ]);

    // Création du token complet (avec préfixe '2|...')
    $token = $user->createToken('auth_token')->plainTextToken;

    // Enregistrement du token complet dans remember_token
    $user->remember_token = $token;
    $user->save();

    return response()->json([
        'access_token' => $token,
        'token_type' => 'Bearer',
        'user' => $user,
    ]);
});

Route::middleware(['auth:sanctum'])->get('/abonnement/status', function () {
    $abonnement = \App\Models\Abonnement::latest()->first();

    if (!$abonnement || now()->gt($abonnement->date_fin)) {
        return response()->json([
            'message' => 'Abonnement expiré. Veuillez contacter le support.'
        ], 403);
    }

    return response()->json(['status' => 'ok']);
});

// --- Route Login ---
Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required'
    ]);

    $abonnement = \App\Models\Abonnement::orderByDesc('date_debut')->first();

    if (!$abonnement || now()->gt($abonnement->date_fin)) {
        // Mets à jour le statut si besoin
        if ($abonnement && $abonnement->statut !== 'inactif') {
            $abonnement->statut = 'inactif';
            $abonnement->save();
        }

        return response()->json([
            'message' => 'Connexion impossible : abonnement expiré. Contactez le support.'
        ], 403);
    }

    if (!Auth::attempt($credentials)) {
        return response()->json(['message' => 'Identifiants invalides'], 401);
    }

    $user = Auth::user();

    // Create token
    $tokenResult = $user->createToken('auth_token');
    $plainTextToken = $tokenResult->plainTextToken;
    $token = $tokenResult->accessToken;

    // Set expiration (2 hours from now)
    $token->expires_at = Carbon::now('Africa/Casablanca')->addHour();
    $token->save();
    $user->remember_token = $plainTextToken;
    $user->save();

    return response()->json([
        'access_token' => $plainTextToken,
        'token_type' => 'Bearer',
        'expires_at' => $token->expires_at ? $token->expires_at->toDateTimeString() : null,
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'is_admin' => $user->is_admin,
        ],
    ]);
});

// --- Route pour récupérer user connecté ---
Route::middleware(['auth:sanctum', 'check.expiration'])->get('/user', function (Request $request) {
    return $request->user();
});

// --- Déconnexion ---
Route::middleware(['auth:sanctum', 'check.expiration'])->post('/logout', function (Request $request) {
    $request->user()->currentAccessToken()->delete();

    return response()->json([
        'message' => 'Déconnexion réussie',
    ]);
});

Route::get('/storage/{path}', function ($path) {
    $filePath = storage_path('app/public/' . $path);

    if (!file_exists($filePath)) {
        abort(404);
    }

    $mimeType = mime_content_type($filePath);

    return response()->file($filePath, [
        'Content-Type' => $mimeType,
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
        'Access-Control-Allow-Headers' => '*',
    ]);
})->where('path', '.*')->name('storage.file');


Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLinkEmail']);
Route::post('/reset-password', [PasswordResetController::class, 'reset']);
Route::post('/validate-reset-token', [PasswordResetController::class, 'validateToken']);
// --- Routes API protégées avec vérification token et expiration ---
Route::middleware(['auth:sanctum', 'check.expiration'])->group(function () {
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'show']); // Pour récupérer les données du profil
        Route::put('/', [ProfileController::class, 'update']); // Pour mettre à jour le profil
        Route::post('/avatar', [ProfileController::class, 'updateAvatar']); // Pour changer l'avatar
        Route::get('/export', [ProfileController::class, 'export']); // Pour exporter les données
    });
    Route::prefix('dashboard')->group(function () {
        Route::get('/stats', [DashboardController::class, 'getStats']);
        Route::get('/expiring-contracts', [DashboardController::class, 'expiringContracts']);
        Route::get('/recent-activities', [DashboardController::class, 'recentActivities']);
        Route::get('/chart-data', [DashboardController::class, 'chartData']);
        Route::get('/revenue-graph', [DashboardController::class, 'revenueGraph']);
        Route::get('/situation-breakdown', [DashboardController::class, 'situationBreakdown']);
    });

    Route::put('/password', [ProfileController::class, 'updatePassword']);
    Route::post('/domiciliations/{id}/resiliate', [DomiciliationController::class, 'resiliate']);
    Route::get('/generate-contract-pdf/{id}', [DomiciliationController::class, 'generateContractPdf']);
    Route::get('/generate-contract-word/{id}', [DomiciliationController::class, 'generateContractWord']);
    Route::apiResource('users', UserController::class);
    Route::apiResource('societes', SocieteController::class);
    Route::apiResource('gerants', GerantController::class);
    Route::apiResource('gestionnaires', GestionnaireController::class);
    Route::apiResource('clients', ClientController::class);
    Route::apiResource('activity-logs', ActivityLogController::class);
    Route::apiResource('documents', DocumentController::class);
    Route::apiResource('templates', TemplateController::class);
    Route::get('/domiciliations/{id}/pdf', [DomiciliationController::class, 'downloadPDF']);
    Route::apiResource('domiciliations', DomiciliationController::class);
    // Route::apiResource('domiciliation-histories', DomiciliationHistoryController::class);
    Route::apiResource('resiliations', ResiliationController::class);
    Route::apiResource('payments', PaymentController::class);
    Route::apiResource('bureaux-equipe', BureauEquipeController::class);
    Route::apiResource('bureaux-equipe-histories', BureauEquipeHistoryController::class)->only(['index', 'show']);
    Route::apiResource('resiliations-bureaux', ResiliationBureauEquipeController::class);
    Route::post('/bureaux-equipe/{id}/renew', [BureauEquipeController::class, 'renew']);
    Route::apiResource('resiliations-bureaux', ResiliationBureauEquipeController::class);
    Route::post('/bureaux-equipe/{id}/resiliate', [BureauEquipeController::class, 'resiliate']);
    Route::get('/domiciliations/historiques', [DomiciliationController::class, 'historiques']);
    Route::apiResource('domiciliation-histories', DomiciliationHistoryController::class);
    Route::get('bureaux-equipe/{id}/contract-data', [BureauEquipeController::class, 'getContractData2']);
    Route::get('/resiliations/download-word/{id}', [ResiliationController::class, 'downloadResiliationWord']);
    // Route::get('/resiliations/download-pdf/{id}', [\App\Http\Controllers\ResiliationController::class, 'downloadResiliationPdf']);
        Route::get('/resiliations/resiliationContractPDF/{id}', [ResiliationController::class, 'generateContractPdf']);
    Route::get('/resiliations/resiliationContractWORD/{id}', [ResiliationController::class, 'generateContractWord']);
    Route::get('/bureau/resiliations/resiliationContractPDF/{id}', [ResiliationBureauEquipeController::class, 'generateContractPdf']);
    Route::get('/bureau/resiliations/resiliationContractWORD/{id}', [ResiliationBureauEquipeController::class, 'generateContractWord']);
    // Route spécifique pour récupérer les données du contrat (AVANT la route resource)


    // ✅ Protéger toutes les routes DELETE par le middleware 'admin'
    Route::middleware('admin.delete')->group(function () {
        Route::delete('/clients/{id}', [ClientController::class, 'destroy']);
        Route::delete('/gestionnaires/{id}', [GestionnaireController::class, 'destroy']);
        Route::delete('/societes/{id}', [SocieteController::class, 'destroy']);
        Route::delete('/resiliations/{id}', [ResiliationController::class, 'destroy']);
        Route::delete('/templates/{id}', [TemplateController::class, 'destroy']);
        Route::delete('/users/{id}', [UserController::class, 'destroy']);
        Route::delete('/domiciliations/{id}', [DomiciliationController::class, 'destroy']);
        // Route::delete('/historiques/{id}', [DomiciliationHistoryController::class, 'destroy']);
        Route::delete('/documents/{id}', [DocumentController::class, 'destroy']);
        Route::delete('/gerants/{id}', [GerantController::class, 'destroy']);
    });



    Route::get('/resiliations/generate-word/{id}', [ResiliationController::class, 'generateResiliationWord']);
    Route::get('/download/resiliation/{id}/{filename}', [ResiliationController::class, 'downloadResiliation'])
        ->name('download.resiliation');
});




// --- Commande artisan pour resiliations ---
Route::post('/resiliations/check', function () {
    Artisan::call('resiliations:check');
    return response()->json(['message' => 'Commande exécutée. Vérifiez vos emails.']);
});
