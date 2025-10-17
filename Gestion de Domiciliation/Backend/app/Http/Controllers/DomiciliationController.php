<?php

namespace App\Http\Controllers;

use App\Models\Domiciliation;
use App\Models\DomiciliationHistory;
use App\Models\Template;
use App\Models\User;
use App\Models\Societe;
use App\Models\Client;
use App\Models\Gestionnaire;
use App\Models\Resiliation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Blade;
use Carbon\Carbon;
use Illuminate\Support\Str;
use TCPDF;
use Illuminate\Support\Facades\Auth;

class DomiciliationController extends Controller
{
    private const PERIOD_TRANSLATIONS = [
        3 => "ثلاثة أشهر",
        6 => "ستة أشهر",
        12 => "سنة واحدة",
        15 => "خمسة عشر شهرا",
        18 => "ثمانية عشر شهرا",
        24 => "سنتين",
        27 => "سبعة وعشرون شهرا",
        30 => "ثلاثون شهرا",
        36 => "ثلاث سنوات"
    ];

    /**
     * Récupérer toutes les domiciliations
     */


    private function getAuthenticatedUser(Request $request)
    {
        $token = $request->header('Authorization');
        if ($token && str_starts_with($token, 'Bearer ')) {
            $token = substr($token, 7);
        }

        return User::where('remember_token', $token)->first();
    }

    public function index(Request $request)
    {
        try {
            $user = $this->getAuthenticatedUser($request);
            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $query = Domiciliation::with(['client', 'gestionnaire', 'createdBy']);

            if (!$user->is_admin) {
                $query->where(function ($q) use ($user) {
                    $q->where('created_by', $user->id)
                        ->orWhere('gestionnaire_id', $user->id);
                });
            }

            if ($request->has('client_id')) {
                $query->where('client_id', $request->client_id);
            }

            if ($request->has('gestionnaire_id')) {
                $query->where('gestionnaire_id', $request->gestionnaire_id);
            }

            if ($request->has('situation')) {
                $query->where('situation', $request->situation);
            }

            if ($request->has('payement')) {
                $query->where('payement', $request->boolean('payement'));
            }

            $perPage = $request->get('per_page', 15);
            $domiciliations = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return response()->json($domiciliations);
        } catch (\Exception $e) {
            Log::error('Erreur récupération domiciliations', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Erreur lors de la récupération des domiciliations'], 500);
        }
    }


    /**
     * Créer ou renouveler une domiciliation
     */
    public function store(Request $request)
    {
        try {
            // Auth validation
            $token = $request->header('Authorization');
            if ($token && str_starts_with($token, 'Bearer ')) {
                $token = substr($token, 7);
            }

            $user = User::where('remember_token', $token)->first();
            if (!$user) {
                return response()->json(["error" => "Unauthorized"], 401);
            }

            $request['created_by'] = $user->id;

            // Validate request
            $validated = $request->validate([
                'client_id' => 'required|exists:eryx_clients,id',
                'gestionnaire_id' => 'required|exists:eryx_gestionnaires,id',
                'date_debut' => 'required|date',
                'date_fin' => 'required|date|after_or_equal:date_debut',
                'created_by' => 'required|exists:eryx_users,id',
                'montant' => 'required|numeric|min:0',
                'situation' => 'required|in:DEMANDE,EN_COURS,REJETTE',
                'payement' => 'boolean',
                'reference_numero' => 'nullable|string|max:50',
                'notes' => 'nullable|string',
            ]);

            // Initialiser le renewal_count à 0 pour une nouvelle domiciliation
            $validated['renewal_count'] = 0;

            // Vérifier si le client a déjà une domiciliation active
            $existingDomiciliation = Domiciliation::where('client_id', $validated['client_id'])
                ->whereIn('situation', ['DEMANDE', 'EN_COURS'])
                ->first();

            DB::beginTransaction();

            try {
                $domiciliation = null;
                $isUpdate = false;

                if ($existingDomiciliation) {
                    // Client existe déjà avec une domiciliation active
                    Log::info('Client avec domiciliation existante trouvé', [
                        'client_id' => $validated['client_id'],
                        'existing_domiciliation_id' => $existingDomiciliation->id
                    ]);

                    // Sauvegarder les anciennes valeurs dans l'historique
                    $oldValues = $existingDomiciliation->toArray();
                    $newValues = $validated;

                    // Créer l'entrée d'historique
                    DomiciliationHistory::create([
                        'domiciliation_id' => $existingDomiciliation->id,
                        'modified_by' => $user->id,
                        'modification_date' => now(),
                        'old_values' => $oldValues, // Le mutator s'occupera du formatage
                        'new_values' => $newValues, // Le mutator s'occupera du formatage
                        'notes' => 'Renouvellement de domiciliation - Anciennes valeurs sauvegardées'
                    ]);

                    // Mettre à jour la domiciliation existante
                    $validated['renewal_count'] = $existingDomiciliation->renewal_count + 1;
                    $existingDomiciliation->update($validated);

                    $domiciliation = $existingDomiciliation->fresh();
                    $isUpdate = true;

                    Log::info('Domiciliation mise à jour avec succès', [
                        'domiciliation_id' => $domiciliation->id,
                        'new_renewal_count' => $domiciliation->renewal_count
                    ]);
                } else {
                    // Nouveau client ou client sans domiciliation active
                    Log::info('Création d\'une nouvelle domiciliation', [
                        'client_id' => $validated['client_id']
                    ]);

                    $domiciliation = Domiciliation::create($validated);
                }

                // Get required data pour la génération du PDF
                $client = Client::with('gerant')->findOrFail($validated['client_id']);
                $gestionnaire = Gestionnaire::findOrFail($validated['gestionnaire_id']);

                $societe = Societe::first();



                if (!$societe) {
                    Log::warning('Aucune société trouvée');
                }

                // Calculate period in Arabic
                $diffMonths = $this->calculateMonthsDifference(
                    $validated['date_debut'],
                    $validated['date_fin']
                );
                $periodeArabe = $this->getArabicPeriod($diffMonths);




                DB::commit();

                $message = $isUpdate
                    ? 'Domiciliation renouvelée avec succès'
                    : 'Domiciliation créée avec succès';

                return response()->json([
                    'message' => $message,
                    'is_renewal' => $isUpdate,
                    'data' => [
                        'domiciliation_id' => $domiciliation->id,
                        'renewal_count' => $domiciliation->renewal_count,
                        'client' => $client,
                        'gestionnaire' => $gestionnaire,

                        'societe' => $societe,

                    ]
                ], $isUpdate ? 200 : 201);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Erreur création/mise à jour domiciliation', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                "error" => "Erreur lors de la création/mise à jour de la domiciliation",
                "details" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher une domiciliation spécifique
     */
    public function show($id, Request $request)
    {
        try {
            $user = $this->getAuthenticatedUser($request);
            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $domiciliation = Domiciliation::with(['client.gerant', 'gestionnaire', 'createdBy:id,name,email'])
                ->findOrFail($id);

            // Restriction d'accès si non admin
            if (
                !$user->is_admin &&
                $domiciliation->created_by !== $user->id &&
                $domiciliation->gestionnaire_id !== $user->id
            ) {
                return response()->json(['error' => 'Forbidden'], 403);
            }

            return response()->json([
                'domiciliation' => $domiciliation,
                'period_arabic' => $this->getArabicPeriod(
                    $this->calculateMonthsDifference($domiciliation->date_debut, $domiciliation->date_fin)
                )
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur récupération domiciliation', [
                'domiciliation_id' => $id,
                'message' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Domiciliation non trouvée'], 404);
        }
    }


    /**
     * Mettre à jour une domiciliation
     */
    public function update(Request $request, $id)
    {
        try {
            // Auth validation
            $token = $request->header('Authorization');
            if ($token && str_starts_with($token, 'Bearer ')) {
                $token = substr($token, 7);
            }

            $user = User::where('remember_token', $token)->first();
            if (!$user) {
                return response()->json(["error" => "Unauthorized"], 401);
            }

            $domiciliation = Domiciliation::findOrFail($id);
            $authenticatedUser = $user;

            $validated = $request->validate([
                'gestionnaire_id' => 'sometimes|exists:eryx_gestionnaires,id',
                'date_debut' => 'sometimes|date',
                'date_fin' => 'sometimes|date|after_or_equal:date_debut',
                'montant' => 'sometimes|numeric|min:0',
                'situation' => 'sometimes|in:DEMANDE,EN_COURS,REJETTE',
                'payement' => 'sometimes|boolean',
                'reference_numero' => 'nullable|string|max:50',
                'notes' => 'nullable|string',
            ]);

            DB::beginTransaction();

            try {
                // Sauvegarder les anciennes valeurs dans l'historique
                $oldValues = $domiciliation->toArray();

                // Mettre à jour la domiciliation
                $domiciliation->update($validated);

                // Créer l'entrée d'historique
                DomiciliationHistory::create([
                    'domiciliation_id' => $domiciliation->id,
                    'modified_by' => $user->id,
                    'modification_date' => now(),
                    'old_values' => $oldValues, // Le mutator s'occupera du formatage
                    'new_values' => $validated, // Le mutator s'occupera du formatage
                    'notes' => 'Modification de domiciliation'
                ]);

                DB::commit();

                return response()->json([
                    'message' => 'Domiciliation mise à jour avec succès',
                    'domiciliation' => $domiciliation->fresh()
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Erreur mise à jour domiciliation', [
                'domiciliation_id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Erreur lors de la mise à jour de la domiciliation'], 500);
        }
    }

    /**
     * Supprimer une domiciliation
     */
    public function destroy($id, Request $request)
    {
        try {
            $token = $request->header('Authorization');
            if ($token && str_starts_with($token, 'Bearer ')) {
                $token = substr($token, 7);
            }

            $user = User::where('remember_token', $token)->first();
            if (!$user) {
                return response()->json(["error" => "Unauthorized"], 401);
            }

            $domiciliation = Domiciliation::findOrFail($id);
            DB::beginTransaction();

            try {
                $oldValues = $domiciliation->toArray();

                // 1. Mettre à jour la situation
                $domiciliation->update([
                    'situation' => 'RESILIE'
                ]);

                Log::debug('Creating resiliation', [
                    'domiciliation_id' => $domiciliation->id,
                    'date_resiliation' => now()->toDateString(),
                    'raison' => $request->input('raison', 'Résiliation manuelle'),
                    'created_by' => $user->id,
                    'status' => 'PENDING'
                ]);
                Log::debug('USER CHECK', ['user' => $user]);
                // 2. Créer une entrée dans la table des résiliations
                Resiliation::create([
                    'domiciliation_id' => $domiciliation->id,
                    'date_resiliation' => now(),
                    'raison' => 'Résiliation automatique via suppression',
                    'created_by' => optional($user)->id ?? $domiciliation->created_by,
                    'status' => 'PENDING'
                ]);
                // 3. Ajouter à l’historique
                DomiciliationHistory::create([
                    'domiciliation_id' => $domiciliation->id,
                    'modified_by' => optional($user)->id ?? null,
                    'modification_date' => now(),
                    'old_values' => $oldValues,
                    'new_values' => ['situation' => 'RESILIE'],
                    'notes' => 'Domiciliation résiliée automatiquement'
                ]);

                DB::commit();

                return response()->json(['message' => 'Domiciliation résiliée avec succès']);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la résiliation', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Erreur lors de la résiliation'], 500);
        }
    }


    /**
     * Obtenir l'historique complet d'une domiciliation
     */
    public function getDomiciliationHistory($id)
    {
        try {
            $domiciliation = Domiciliation::findOrFail($id);

            $histories = DomiciliationHistory::where('domiciliation_id', $id)
                ->with('modifiedBy:id,name,email')
                ->orderBy('modification_date', 'desc')
                ->get()
                ->map(function ($history) {
                    return [
                        'id' => $history->id,
                        'modification_date' => $history->modification_date,
                        'modified_by' => $history->modifiedBy,
                        'old_values' => $history->old_values, // Utilise l'accessor
                        'new_values' => $history->new_values, // Utilise l'accessor
                        'notes' => $history->notes,
                        'created_at' => $history->created_at
                    ];
                });

            return response()->json([
                'domiciliation_id' => $id,
                'current_renewal_count' => $domiciliation->renewal_count,
                'history' => $histories
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur récupération historique domiciliation', [
                'domiciliation_id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Historique non trouvé'], 404);
        }
    }

    /**
     * Obtenir les statistiques de renouvellement d'un client
     */
    public function getClientRenewalStats($clientId)
    {
        try {
            $client = Client::findOrFail($clientId);

            $domiciliations = Domiciliation::where('client_id', $clientId)
                ->orderBy('created_at', 'desc')
                ->get();

            $totalRenewals = $domiciliations->sum('renewal_count');
            $activeDomiciliations = $domiciliations->whereIn('situation', ['DEMANDE', 'EN_COURS'])->count();

            return response()->json([
                'client_id' => $clientId,
                'client_name' => $client->nom . ' ' . $client->prenom,
                'total_domiciliations' => $domiciliations->count(),
                'active_domiciliations' => $activeDomiciliations,
                'total_renewals' => $totalRenewals,
                'domiciliations' => $domiciliations->map(function ($dom) {
                    return [
                        'id' => $dom->id,
                        'renewal_count' => $dom->renewal_count,
                        'situation' => $dom->situation,
                        'date_debut' => $dom->date_debut,
                        'date_fin' => $dom->date_fin,
                        'montant' => $dom->montant,
                        'payement' => $dom->payement
                    ];
                })
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur récupération statistiques client', [
                'client_id' => $clientId,
                'message' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Statistiques non trouvées'], 404);
        }
    }

    /**
     * Obtenir les domiciliations par client
     */
    public function getDomiciliationByClient($clientId, Request $request)
    {
        try {
            $user = $this->getAuthenticatedUser($request);
            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $query = Domiciliation::where('client_id', $clientId)
                ->with(['gestionnaire', 'createdBy:id,name,email']);

            if (!$user->is_admin) {
                $query->where(function ($q) use ($user) {
                    $q->where('created_by', $user->id)
                        ->orWhere('gestionnaire_id', $user->id);
                });
            }

            $domiciliations = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'client_id' => $clientId,
                'domiciliations' => $domiciliations
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur récupération domiciliations par client', [
                'client_id' => $clientId,
                'message' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Erreur lors de la récupération des domiciliations'], 500);
        }
    }


    /**
     * Obtenir les domiciliations par gestionnaire
     */
    public function getDomiciliationByGestionnaire($gestionnaireId, Request $request)
    {
        try {
            $user = $this->getAuthenticatedUser($request);
            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Admin peut tout voir
            if ($user->is_admin || $user->id == $gestionnaireId) {
                $domiciliations = Domiciliation::where('gestionnaire_id', $gestionnaireId)
                    ->with(['client', 'createdBy:id,name,email'])
                    ->orderBy('created_at', 'desc')
                    ->get();

                return response()->json([
                    'gestionnaire_id' => $gestionnaireId,
                    'domiciliations' => $domiciliations
                ]);
            }

            // Non autorisé
            return response()->json(['error' => 'Forbidden'], 403);
        } catch (\Exception $e) {
            Log::error('Erreur récupération domiciliations par gestionnaire', [
                'gestionnaire_id' => $gestionnaireId,
                'message' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Erreur lors de la récupération des domiciliations'], 500);
        }
    }


    /**
     * Télécharger le PDF d'une domiciliation
     */
    public function downloadPDF($id)
    {
        try {
            $domiciliation = Domiciliation::findOrFail($id);
            $client = Client::with('gerant')->findOrFail($domiciliation->client_id);
            $societe = Societe::first();
            $template = Template::where('document_type', 'CONTRAT_DOMICILIATION')
                ->where('is_active', true)
                ->first();

            if (!$societe) {
                return response()->json(['error' => 'Société non trouvée'], 404);
            }

            if (!$template) {
                return response()->json(['error' => 'Template non trouvé'], 404);
            }

            $diffMonths = $this->calculateMonthsDifference(
                $domiciliation->date_debut,
                $domiciliation->date_fin
            );
            $periodeArabe = $this->getArabicPeriod($diffMonths);

            $pdfPath = $this->generateDomiciliationPDF(
                $domiciliation,
                $client,
                $societe,
                $periodeArabe,
                $diffMonths,
                $template
            );

            if (!$pdfPath) {
                return response()->json(['error' => 'Erreur lors de la génération du PDF'], 500);
            }

            $fullPath = storage_path('app/' . $pdfPath);

            if (!File::exists($fullPath)) {
                return response()->json(['error' => 'Fichier PDF non trouvé'], 404);
            }

            return response()->download(
                $fullPath,
                'domiciliation_' . $domiciliation->id . '.pdf',
                ['Content-Type' => 'application/pdf']
            );
        } catch (\Exception $e) {
            Log::error('Erreur téléchargement PDF domiciliation', [
                'domiciliation_id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Erreur lors du téléchargement du PDF',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les statistiques générales des domiciliations
     */
    public function getStats()
    {
        try {
            $totalDomiciliations = Domiciliation::count();
            $activeDomiciliations = Domiciliation::whereIn('situation', ['DEMANDE', 'EN_COURS'])->count();
            $rejectedDomiciliations = Domiciliation::where('situation', 'REJETTE')->count();
            $paidDomiciliations = Domiciliation::where('payement', true)->count();
            $totalRenewals = Domiciliation::sum('renewal_count');
            $totalAmount = Domiciliation::sum('montant');

            // Statistiques par mois (12 derniers mois)
            $monthlyStats = [];
            for ($i = 11; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $count = Domiciliation::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count();

                $monthlyStats[] = [
                    'month' => $date->format('Y-m'),
                    'month_name' => $date->format('F Y'),
                    'count' => $count
                ];
            }

            return response()->json([
                'total_domiciliations' => $totalDomiciliations,
                'active_domiciliations' => $activeDomiciliations,
                'rejected_domiciliations' => $rejectedDomiciliations,
                'paid_domiciliations' => $paidDomiciliations,
                'total_renewals' => $totalRenewals,
                'total_amount' => $totalAmount,
                'monthly_stats' => $monthlyStats
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur récupération statistiques', [
                'message' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Erreur lors de la récupération des statistiques'], 500);
        }
    }

    /**
     * Test de rendu de template
     */
    public function testTemplateRendering($id)
    {
        try {
            $domiciliation = Domiciliation::findOrFail($id);
            $client = Client::with('gerant')->findOrFail($domiciliation->client_id);
            $societe = Societe::first();
            $template = Template::where('document_type', 'CONTRAT_DOMICILIATION')
                ->where('is_active', true)
                ->first();

            if (!$template) {
                return response()->json(['error' => 'Template non trouvé'], 404);
            }

            $diffMonths = $this->calculateMonthsDifference(
                $domiciliation->date_debut,
                $domiciliation->date_fin
            );
            $periodeArabe = $this->getArabicPeriod($diffMonths);

            $viewData = [
                'domiciliation' => $domiciliation,
                'client' => $client,
                'societe' => $societe,
                'gerant' => $client->gerant,
                'gestionnaire' => $domiciliation->gestionnaire,
                'periodeArabe' => $periodeArabe,
                'currentDate' => Carbon::now()->format('d/m/Y'),
                'periodeMonths' => $diffMonths
            ];

            $html = $this->renderTemplate($template, $viewData);

            return response()->json([
                'template_info' => [
                    'id' => $template->id,
                    'type' => $template->type,
                    'path' => $template->path,
                    'file_exists' => Storage::exists($template->path),
                ],
                'rendered_html' => $html ? substr($html, 0, 1000) . '...' : null,
                'html_length' => $html ? strlen($html) : 0,
                'view_data_keys' => array_keys($viewData),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    // ==================== MÉTHODES PRIVÉES ====================

    private function getArabicPeriod(int $months): string
    {
        return self::PERIOD_TRANSLATIONS[$months] ?? "غير محدد";
    }

    private function calculateMonthsDifference(string $startDate, string $endDate): int
    {
        return Carbon::parse($startDate)->diffInMonths(Carbon::parse($endDate));
    }

    private function prepareTemplateVariables(array $data, string $periodeArabe): array
    {
        return [
            'DATE_DEBUT' => Carbon::parse($data['date_debut'])->format('d/m/Y'),
            'DATE_FIN' => Carbon::parse($data['date_fin'])->format('d/m/Y'),
            'DATE_TODAY' => Carbon::now()->format('d/m/Y'),
            'PERIOD_AR' => $periodeArabe,
            'MONTANT' => number_format($data['montant'], 2) . ' درهم'
        ];
    }

    private function generateDomiciliationPDF(
        Domiciliation $domiciliation,
        Client $client,
        Societe $societe,
        string $periodeArabe,
        int $diffMonths,
        Template $template
    ) {
        try {
            Log::info('Starting PDF generation', [
                'domiciliation_id' => $domiciliation->id,
                'template_id' => $template->id,
                'template_path' => $template->path,
                'template_type' => $template->type
            ]);

            $uniqueId = Str::random(8);
            $fileName = 'domiciliation_' . $domiciliation->id . '_' . $uniqueId . '.pdf';
            $storagePath = 'public/domiciliations';
            $fullPath = storage_path('app/' . $storagePath . '/' . $fileName);

            // Ensure directory exists
            if (!File::exists(storage_path('app/' . $storagePath))) {
                File::makeDirectory(storage_path('app/' . $storagePath), 0755, true);
                Log::info('Created directory: ' . storage_path('app/' . $storagePath));
            }

            // Prepare data for the template
            $viewData = [
                'domiciliation' => $domiciliation,
                'client' => $client,
                'societe' => $societe,
                'gerant' => $client->gerant,
                'gestionnaire' => $domiciliation->gestionnaire,
                'periodeArabe' => $periodeArabe,
                'currentDate' => Carbon::now()->format('d/m/Y'),
                'periodeMonths' => $diffMonths
            ];

            // Get the rendered HTML based on template type
            $html = $this->renderTemplate($template, $viewData);

            if (!$html) {
                throw new \Exception('Failed to render template');
            }

            Log::info('Template rendered successfully', ['html_length' => strlen($html)]);

            // Create new TCPDF instance with RTL config
            $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

            // Set document information
            $pdf->SetCreator('Domiciliation System');
            $pdf->SetAuthor($societe->nom ?? 'System');
            $pdf->SetTitle('عقد الهيمنة - Contrat de Domiciliation');
            $pdf->SetSubject('عقد الهيمنة التجارية');

            // Set RTL and Arabic language support
            $pdf->setRTL(true);
            $pdf->setLanguageArray([
                'a_meta_charset' => 'UTF-8',
                'a_meta_dir' => 'rtl',
                'a_meta_language' => 'ar',
            ]);

            // Remove default header/footer
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);

            // Set margins
            $pdf->SetMargins(15, 15, 15);
            $pdf->SetAutoPageBreak(TRUE, 15);

            // Add a page
            $pdf->AddPage();

            // Set Arabic font (aealarabiya is included with TCPDF and supports Arabic)
            $pdf->SetFont('aealarabiya', '', 12);

            Log::info('Writing HTML to PDF');

            // Write HTML content to PDF
            $pdf->writeHTML($html, true, false, true, false, '');

            Log::info('Saving PDF to: ' . $fullPath);

            // Save the PDF
            $pdf->Output($fullPath, 'F');

            // Verify file was created
            if (!File::exists($fullPath)) {
                Log::error('PDF file was not created', ['path' => $fullPath]);
                throw new \Exception('PDF file was not created at: ' . $fullPath);
            }

            $fileSize = File::size($fullPath);
            Log::info('PDF generated successfully', [
                'path' => $fullPath,
                'size' => $fileSize . ' bytes'
            ]);

            return $storagePath . '/' . $fileName;
        } catch (\Exception $e) {
            Log::error('PDF Generation Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'domiciliation_id' => $domiciliation->id ?? 'unknown'
            ]);
            return null;
        }
    }

    /**
     * Render template content based on template type
     */
    private function renderTemplate(Template $template, array $data): ?string
    {
        try {
            Log::info('Rendering template', [
                'template_type' => $template->type,
                'template_path' => $template->path
            ]);

            switch ($template->type) {
                case 'blade':
                    return $this->renderBladeTemplate($template, $data);

                case 'html':
                    return $this->renderHtmlTemplate($template, $data);

                default:
                    // For other types, try to use the content directly
                    return $this->renderContentTemplate($template, $data);
            }
        } catch (\Exception $e) {
            Log::error('Template rendering failed', [
                'error' => $e->getMessage(),
                'template_id' => $template->id
            ]);
            return null;
        }
    }

    /**
     * Render Blade template from storage
     */
    private function renderBladeTemplate(Template $template, array $data): ?string
    {
        try {
            // Check if template file exists in storage
            if (!Storage::exists($template->path)) {
                Log::error('Template file not found in storage', ['path' => $template->path]);
                // Fallback to content from database
                return $this->renderContentTemplate($template, $data);
            }

            // Get template content from storage
            $templateContent = Storage::get($template->path);

            // Create a temporary view file
            $tempViewName = 'temp_' . $template->id . '_' . time();
            $tempViewPath = resource_path('views/temp');

            // Ensure temp directory exists
            if (!File::exists($tempViewPath)) {
                File::makeDirectory($tempViewPath, 0755, true);
            }

            $tempViewFile = $tempViewPath . '/' . $tempViewName . '.blade.php';

            // Write template content to temporary file
            File::put($tempViewFile, $templateContent);

            // Render the view
            $html = View::make('temp.' . $tempViewName, $data)->render();

            // Clean up temporary file
            File::delete($tempViewFile);

            return $html;
        } catch (\Exception $e) {
            Log::error('Blade template rendering failed', [
                'error' => $e->getMessage(),
                'template_id' => $template->id
            ]);

            // Fallback to content rendering
            return $this->renderContentTemplate($template, $data);
        }
    }

    /**
     * Render HTML template with variable substitution
     */
    private function renderHtmlTemplate(Template $template, array $data): ?string
    {
        try {
            // Get template content (from storage or database)
            $content = Storage::exists($template->path)
                ? Storage::get($template->path)
                : $template->content;

            // Simple variable substitution for HTML templates
            return $this->substituteVariables($content, $data);
        } catch (\Exception $e) {
            Log::error('HTML template rendering failed', [
                'error' => $e->getMessage(),
                'template_id' => $template->id
            ]);
            return null;
        }
    }

    /**
     * Render template using content with variable substitution
     */
    private function renderContentTemplate(Template $template, array $data): ?string
    {
        try {
            // Use content from database as fallback
            $content = $template->content;

            // Simple variable substitution
            return $this->substituteVariables($content, $data);
        } catch (\Exception $e) {
            Log::error('Content template rendering failed', [
                'error' => $e->getMessage(),
                'template_id' => $template->id
            ]);
            return null;
        }
    }

    /**
     * Simple variable substitution for templates
     */
    private function substituteVariables(string $content, array $data): string
    {
        // Flatten nested objects for substitution
        $variables = $this->flattenArray($data);

        // Replace variables in format {{ $variable }} or {variable}
        foreach ($variables as $key => $value) {
            $content = str_replace([
                '{{ $' . $key . ' }}',
                '{{$' . $key . '}}',
                '{' . $key . '}',
                '{{ ' . $key . ' }}',
            ], $value, $content);
        }

        return $content;
    }

    /**
     * Flatten nested array/object for variable substitution
     */
    private function flattenArray(array $array, string $prefix = ''): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $newKey = $prefix ? $prefix . '.' . $key : $key;

            if (is_array($value) || is_object($value)) {
                $result = array_merge($result, $this->flattenArray((array)$value, $newKey));
            } else {
                $result[$newKey] = $value;
                $result[$key] = $value; // Also keep the simple key
            }
        }

        return $result;
    }

    /**
     * Renouveler une domiciliation existante
     */
    public function renew($id, Request $request)
    {
        try {
            // Auth validation
            $token = $request->header('Authorization');
            if ($token && str_starts_with($token, 'Bearer ')) {
                $token = substr($token, 7);
            }

            $user = User::where('remember_token', $token)->first();
            if (!$user) {
                return response()->json(["error" => "Unauthorized"], 401);
            }

            $domiciliation = Domiciliation::findOrFail($id);

            // Vérifier que la domiciliation est active
            if (!in_array($domiciliation->situation, ['DEMANDE', 'EN_COURS'])) {
                return response()->json(["error" => "Seules les domiciliations actives peuvent être renouvelées"], 400);
            }

            DB::beginTransaction();

            try {
                // Sauvegarder les anciennes valeurs dans l'historique
                $oldValues = $domiciliation->toArray();

                // Calculer les nouvelles dates
                $newStartDate = Carbon::parse($domiciliation->date_fin)->addDay();
                $durationMonths = $this->calculateMonthsDifference($domiciliation->date_debut, $domiciliation->date_fin);
                $newEndDate = Carbon::parse($newStartDate)->addMonths($durationMonths);

                // Préparer les nouvelles valeurs
                $newValues = [
                    'date_debut' => $newStartDate->format('Y-m-d'),
                    'date_fin' => $newEndDate->format('Y-m-d'),
                    'renewal_count' => $domiciliation->renewal_count + 1,
                    'payement' => false, // Réinitialiser le statut de paiement
                    'situation' => 'DEMANDE', // Remettre en statut demande
                ];

                // Créer l'entrée d'historique
                DomiciliationHistory::create([
                    'domiciliation_id' => $domiciliation->id,
                    'modified_by' => $user->id,
                    'modification_date' => now(),
                    'old_values' => $oldValues,
                    'new_values' => $newValues,
                    'notes' => 'Renouvellement de domiciliation'
                ]);

                // Mettre à jour la domiciliation
                $domiciliation->update($newValues);

                // Générer le nouveau PDF
                $pdfPath = null;
                $client = Client::with('gerant')->findOrFail($domiciliation->client_id);
                $societe = Societe::first();
                $template = Template::where('document_type', 'CONTRAT_DOMICILIATION')
                    ->where('is_active', true)
                    ->first();

                if ($template && $societe) {
                    $diffMonths = $this->calculateMonthsDifference($newStartDate, $newEndDate);
                    $periodeArabe = $this->getArabicPeriod($diffMonths);

                    $pdfPath = $this->generateDomiciliationPDF(
                        $domiciliation->fresh(),
                        $client,
                        $societe,
                        $periodeArabe,
                        $diffMonths,
                        $template
                    );
                }

                DB::commit();

                return response()->json([
                    'message' => 'Domiciliation renouvelée avec succès',
                    'data' => [
                        'domiciliation' => $domiciliation->fresh(),
                        'pdf_path' => $pdfPath
                    ]
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Erreur renouvellement domiciliation', [
                'domiciliation_id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                "error" => "Erreur lors du renouvellement de la domiciliation",
                "details" => $e->getMessage()
            ], 500);
        }
    }

    public function historiques()
    {
        $historiques = DomiciliationHistory::with(['domiciliation', 'modifiedBy'])
            ->orderByDesc('modification_date')
            ->get()
            ->map(function ($history) {
                return [
                    'old_reference'   => $history->domiciliation->reference_numero ?? '-',
                    'new_reference'   => $history->new_values['reference_numero'] ?? '-',
                    'renewed_at'      => $history->modification_date->format('Y-m-d H:i'),
                    'user_name'       => $history->modifiedBy->name ?? '-',
                    'notes'           => $history->notes,
                ];
            });

        return response()->json(['data' => $historiques]);
    }

    public function resiliate($id, Request $request)
    {
        Log::info('Debug Résiliation', [
            'id' => $id,
            'body' => $request->all(),
            'token' => $request->header('Authorization'),
            'user' => Auth::user()?->id ?? 'non connecté'
        ]);
        try {
            $token = $request->header('Authorization');
            if ($token && str_starts_with($token, 'Bearer ')) {
                $token = substr($token, 7);
            }

            $user = User::where('remember_token', $token)->first();
            if (!$user) {
                return response()->json(["error" => "Unauthorized"], 401);
            }

            $domiciliation = Domiciliation::findOrFail($id);

            if ($domiciliation->situation === 'RESILIE') {
                return response()->json(["message" => "Cette domiciliation est déjà résiliée"], 400);
            }

            DB::beginTransaction();

            $oldValues = $domiciliation->toArray();

            $domiciliation->update([
                'situation' => 'RESILIE',
            ]);

            Resiliation::create([
                'domiciliation_id' => $domiciliation->id,
                'date_resiliation' => now(),
                'raison' => $request->input('raison', 'Résiliation manuelle'),
                'created_by' => $user->id,
                'status' => 'PENDING'
            ]);

            DomiciliationHistory::create([
                'domiciliation_id' => $domiciliation->id,
                'modified_by' => $user->id,
                'modification_date' => now(),
                'old_values' => $oldValues,
                'new_values' => ['situation' => 'RESILIE'],
                'notes' => 'Résiliation manuelle de la domiciliation'
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Domiciliation résiliée avec succès',
                'domiciliation' => $domiciliation->fresh()
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur résiliation manuelle', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Erreur lors de la résiliation'], 500);
        }
    }

    public function generateContractPdf($id)
    {
        try {
            $domiciliation = Domiciliation::with(['client', 'gestionnaire'])->findOrFail($id);
            $societe = Societe::first();

            $periodeMonths = $this->calculateMonthsDifference($domiciliation->date_debut, $domiciliation->date_fin);
            $periodeArabe = $this->convertPeriodToArabic($periodeMonths);

            $client = $domiciliation->client;
            $gerant = $client->gerant;

            $currentDate = now()->format('d/m/Y');
            Log::info('$client', [$client->nom_francais]);
            Log::info('$gerant', [$gerant->nom]);
            Log::info('societe', [$societe->nom_complet_francais]);

            $data = compact(
                'domiciliation',
                'societe',
                'client',
                'gerant',
                'periodeArabe',
                'periodeMonths',
                'currentDate'
            );

            $data2 = compact(
                'societe',
                'gerant',
                'client'
            );

            $htmlContract1 = view('templates.domiciliationContract_1', $data)->render();
            $htmlContract2 = view('templates.domiciliationContract_2', $data2)->render();

            // Return raw HTML to frontend
            // Add response headers for large content
            return response()->json([
                'htmlContract1' => $htmlContract1,
                'htmlContract2' => $htmlContract2,
                'renewal_count' => $domiciliation->renewal_count,
            ], 200, [
                'Content-Type' => 'application/json',
                'Cache-Control' => 'no-cache',
            ])->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        } catch (\Exception $e) {
            Log::error('Erreur génération HTML', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['error' => 'Une erreur interne est survenue.'], 500);
        }
    }

    public function generateContractWord($id)
    {
        try {
            $domiciliation = Domiciliation::with(['client', 'gestionnaire'])->findOrFail($id);
            $societe = Societe::first();

            $periodeMonths = $this->calculateMonthsDifference($domiciliation->date_debut, $domiciliation->date_fin);
            $periodeArabe = $this->convertPeriodToArabic($periodeMonths);

            $client = $domiciliation->client;
            $gerant = $client->gerant;

            $currentDate = now()->format('d/m/Y');

            $data1 = compact(
                'domiciliation',
                'societe',
                'client',
                'gerant',
                'periodeArabe',
                'periodeMonths',
                'currentDate'
            );


            // Return raw HTML to frontend
            return response()->json([
                'data1' => $data1,
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur génération HTML', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    private function convertPeriodToArabic($months)
    {
        return self::PERIOD_TRANSLATIONS[$months] ?? $months . ' أشهر';
    }
}
