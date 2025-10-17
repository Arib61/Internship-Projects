<?php

namespace App\Http\Controllers;

use App\Models\BureauEquipe;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\BureauEquipeHistory;
use App\Models\ResiliationBureauEquipe;
use App\Models\Societe;

class BureauEquipeController extends Controller
{

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
        $user = $this->getAuthenticatedUser($request);
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $query = BureauEquipe::query();

        if (!$user->is_admin) {
            $query->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                    ->orWhere('gestionnaire_id', $user->id);
            });
        }

        return response()->json($query->get());
    }


    public function store(Request $request)
    {
        try {
            // Validation et vérification de l'authentification
            $token = $request->header('Authorization');
            if ($token && str_starts_with($token, 'Bearer ')) {
                $token = substr($token, 7);
            }
            $user = User::where('remember_token', $token)->first();
            if (!$user) {
                return response()->json(["error" => "Unauthorized"], 401);
            }
            $request['created_by'] = $user->id;

            $validated = $request->validate([
                'client_id' => 'required|exists:eryx_clients,id',
                'gestionnaire_id' => 'required|exists:eryx_gestionnaires,id',
                'date_debut' => 'required|date',
                'date_fin' => 'required|date|after_or_equal:date_debut',
                'duree_mois' => 'nullable|integer',
                'created_by' => 'required|exists:eryx_users,id',
                'montant' => 'required|numeric',
                'situation' => 'sometimes|required|in:DEMANDE,EN_COURS,REJETTE,RESILIE',
                'payement' => 'boolean',
                'reference_numero' => 'nullable|string|max:50',
                'notes' => 'nullable|string',
            ]);

            $bureauEquipe = BureauEquipe::create($validated);

            return response()->json([
                'message' => 'Bureau équipe créé avec succès',
                'data' => $bureauEquipe
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Une erreur est survenue lors de la création',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id, Request $request)
    {
        $user = $this->getAuthenticatedUser($request);
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $bureau = BureauEquipe::findOrFail($id);

        if (!$user->is_admin && $bureau->created_by != $user->id && $bureau->gestionnaire_id != $user->id) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        return $bureau;
    }


    public function update(Request $request, $id)
    {
        $bureauEquipe = BureauEquipe::findOrFail($id);

        $validated = $request->validate([
            'client_id' => 'sometimes|required|exists:eryx_clients,id',
            'gestionnaire_id' => 'sometimes|required|exists:eryx_gestionnaires,id',
            'created_by' => 'sometimes|required|exists:eryx_users,id',
            'date_debut' => 'sometimes|required|date',
            'date_fin' => 'sometimes|required|date|after_or_equal:date_debut',
            'duree_mois' => 'nullable|integer',
            'montant' => 'sometimes|required|numeric',
            'situation' => 'sometimes|required|in:DEMANDE,EN_COURS,REJETTE',
            'payement' => 'boolean',
            'reference_numero' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $bureauEquipe->update($validated);

        return response()->json([
            'message' => 'Bureau équipe mis à jour avec succès',
            'data' => $bureauEquipe
        ]);
    }

    public function destroy($id)
    {
        $equipe = BureauEquipe::findOrFail($id);
        $equipe->delete();

        return response()->json(['message' => 'Supprimé avec succès']);
    }

    /**
     * Récupérer les données nécessaires pour générer le contrat
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getContractData($id)
    {
        try {
            Log::info("Début de getContractData pour ID: " . $id);

            // Récupérer le bureau d'équipe
            $bureau = BureauEquipe::findOrFail($id);
            Log::info("Bureau trouvé: ", $bureau->toArray());

            // Initialiser les variables
            $client = null;
            $societe = null;

            // Récupérer les données du client
            try {
                // Essayer d'abord avec le modèle Eloquent si il existe
                if (class_exists('App\Models\Client')) {
                    $client = \App\Models\Client::find($bureau->client_id);
                } else {
                    // Sinon utiliser DB directement
                    $client = DB::table('eryx_clients')->where('id', $bureau->client_id)->first();
                }

                if (!$client) {
                    // Essayer sans préfixe eryx_
                    $client = DB::table('clients')->where('id', $bureau->client_id)->first();
                }

                Log::info("Client trouvé: ", $client ? (array)$client : ['null']);
            } catch (\Exception $e) {
                Log::error("Erreur lors de la récupération du client: " . $e->getMessage());
                $client = null;
            }

            // Récupérer les données de la société
            try {
                // Essayer d'abord avec le modèle Eloquent si il existe
                if (class_exists('App\Models\Societe')) {
                    $societe = \App\Models\Societe::first();
                } else {
                    // Sinon utiliser DB directement
                    $societe = DB::table('eryx_societes')->first();
                }

                if (!$societe) {
                    // Essayer sans préfixe eryx_
                    $societe = DB::table('societes')->first();
                }

                Log::info("Société trouvée: ", $societe ? (array)$societe : ['null']);
            } catch (\Exception $e) {
                Log::error("Erreur lors de la récupération de la société: " . $e->getMessage());
                $societe = null;
            }

            // Si pas de société en base, utiliser des données par défaut
            if (!$societe) {
                $societe = (object) [
                    'nom_francais' => 'MAEXPERTISE CONSULTING',
                    'forme_juridique' => 'SARL AU',
                    'capital_social' => '100.000,00',
                    'rc' => '172295',
                    'adresse_siege_social_francais' => '44, AVENUE DE FRANCE, 3ÈME ÉTAGE, APPT N° 16, AGDAL-RABAT',
                    'president_nom' => 'Mr. MOURAD EL MANSOURI',
                    'telephone' => '+212 5 37 XX XX XX',
                    'email' => 'contact@maexpertise.ma',
                    'ice' => '000000000000000',
                    'identifiant_fiscal' => '00000000',
                    'tp' => '00000000'
                ];
                Log::info("Utilisation des données par défaut pour la société");
            }

            // Si pas de client trouvé, utiliser des données par défaut
            if (!$client) {
                $client = (object) [
                    'nom_francais' => 'BENZAL CAR',
                    'ice' => '003653811000080',
                    'president_nom' => 'Mr. BENZAL OUTMAN',
                    'president_cin' => 'S517579',
                    'nom_complet_francais' => 'Mr. BENZAL OUTMAN'
                ];
                Log::info("Utilisation des données par défaut pour le client");
            }

            $response = [
                'message' => 'Données récupérées avec succès',
                'is_renewal' => ($bureau->created_at != $bureau->updated_at),
                'data' => [
                    'client' => $client,
                    'societe' => $societe,
                    'bureau' => $bureau
                ]
            ];

            Log::info("Réponse finale: ", $response);

            return response()->json($response, 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error("Bureau non trouvé: " . $e->getMessage());
            return response()->json([
                'message' => 'Bureau d\'équipe non trouvé',
                'error' => 'Le bureau demandé n\'existe pas'
            ], 404);
        } catch (\Exception $e) {
            Log::error("Erreur générale dans getContractData: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());

            return response()->json([
                'message' => 'Erreur lors de la récupération des données',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }

    // Ajoutez cette méthode à la fin du BureauEquipeController
    public function renew($id, Request $request)
    {
        try {
            // Authentification
            $token = $request->header('Authorization');
            if ($token && str_starts_with($token, 'Bearer ')) {
                $token = substr($token, 7);
            }
            $user = User::where('remember_token', $token)->first();
            if (!$user) {
                return response()->json(["error" => "Unauthorized"], 401);
            }

            $bureau = BureauEquipe::findOrFail($id);

            // Vérifier que le bureau est actif
            if (!in_array($bureau->situation, ['DEMANDE', 'EN_COURS'])) {
                return response()->json(["error" => "Seuls les bureaux actifs peuvent être renouvelés"], 400);
            }

            DB::beginTransaction();

            try {
                // Sauvegarder l'ancien état (pour historique)
                $oldValues = $bureau->toArray();

                // Calculer les nouvelles dates
                $oldEndDate = Carbon::parse($bureau->date_fin);
                $newStartDate = $oldEndDate->copy()->addDay();
                $durationMonths = $this->calculateMonthsDifference(
                    $bureau->date_debut,
                    $bureau->date_fin
                );
                $newEndDate = $newStartDate->copy()->addMonths($durationMonths);

                // Mettre à jour le bureau
                $bureau->update([
                    'date_debut' => $newStartDate->format('Y-m-d'),
                    'date_fin' => $newEndDate->format('Y-m-d'),
                    'payement' => false, // Réinitialiser le paiement
                    'situation' => 'DEMANDE',
                    // Remettre en statut demande
                ]);

                // Créer l'entrée d'historique - PARTIE MANQUANTE
                BureauEquipeHistory::create([
                    'bureaux_equipe_id' => $bureau->id,
                    'modified_by' => $user->id,
                    'modification_date' => now(),
                    'old_values' => $oldValues,
                    'new_values' => $bureau->fresh()->toArray(),
                    'notes' => 'Renouvellement du bureau'
                ]);

                // Générer les données du contrat (optionnel)
                $contractData = $this->getContractData($bureau->id);

                DB::commit();

                return response()->json([
                    'message' => 'Bureau renouvelé avec succès',
                    'data' => [
                        'bureau' => $bureau,
                        'contract_data' => $contractData->getData() // Si nécessaire
                    ]
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Erreur renouvellement bureau', [
                'bureau_id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                "error" => "Erreur lors du renouvellement",
                "details" => $e->getMessage()
            ], 500);
        }
    }

    // Ajoutez cette méthode utilitaire dans la classe
    private function calculateMonthsDifference($startDate, $endDate)
    {
        return Carbon::parse($startDate)->diffInMonths(Carbon::parse($endDate));
    }

    public function resiliate($id, Request $request)
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

            $bureau = BureauEquipe::findOrFail($id);

            DB::beginTransaction();

            try {
                // Sauvegarder les anciennes valeurs
                $oldValues = $bureau->toArray();

                // Mettre à jour le statut
                $bureau->update(['situation' => 'RESILIE']);

                // Créer la résiliation
                ResiliationBureauEquipe::create([
                    'bureaux_equipe_id' => $bureau->id,
                    'date_resiliation' => now(),
                    'raison' => $request->raison,
                    'status' => 'APPROVED',
                    'created_by' => $user->id
                ]);

                // Historique
                BureauEquipeHistory::create([
                    'bureaux_equipe_id' => $bureau->id,
                    'modified_by' => $user->id,
                    'modification_date' => now(),
                    'old_values' => $oldValues,
                    'new_values' => $bureau->fresh()->toArray(),
                    'notes' => 'Résiliation: ' . $request->raison
                ]);

                DB::commit();

                return response()->json([
                    'message' => 'Bureau résilié avec succès',
                    'bureau' => $bureau
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Erreur complète lors de la résiliation', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Erreur lors de la résiliation',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getContractData2($id)
    {
        try {
            Log::info("Début de getContractData pour ID: " . $id);

            // Récupérer le bureau d'équipe
            $bureau = BureauEquipe::findOrFail($id);
            Log::info("Bureau trouvé: ", $bureau->toArray());

            // Initialiser les variables
            $client = null;
            $societe = null;

            // Récupérer les données du client
            try {
                // Essayer d'abord avec le modèle Eloquent si il existe
                if (class_exists('App\Models\Client')) {
                    $client = \App\Models\Client::find($bureau->client_id);
                } else {
                    // Sinon utiliser DB directement
                    $client = DB::table('eryx_clients')->where('id', $bureau->client_id)->first();
                }

                if (!$client) {
                    // Essayer sans préfixe eryx_
                    $client = DB::table('clients')->where('id', $bureau->client_id)->first();
                }

                Log::info("Client trouvé: ", $client ? (array)$client : ['null']);
            } catch (\Exception $e) {
                Log::error("Erreur lors de la récupération du client: " . $e->getMessage());
                $client = null;
            }

            // Récupérer les données de la société
            try {
                // Essayer d'abord avec le modèle Eloquent si il existe
                if (class_exists('App\Models\Societe')) {
                    $societe = \App\Models\Societe::first();
                } else {
                    $societe = Societe::first();
                }

                if (!$societe) {
                    // Essayer sans préfixe eryx_
                    $societe = DB::table('societes')->first();
                }

                Log::info("Société trouvée: ", $societe ? (array)$societe : ['null']);
            } catch (\Exception $e) {
                Log::error("Erreur lors de la récupération de la société: " . $e->getMessage());
                $societe = null;
            }

            // Si pas de société en base, utiliser des données par défaut
            if (!$societe) {
                $societe = (object) [
                    'nom_francais' => 'MAEXPERTISE CONSULTING',
                    'forme_juridique' => 'SARL AU',
                    'capital_social' => '100.000,00',
                    'rc' => '172295',
                    'adresse_siege_social_francais' => '44, AVENUE DE FRANCE, 3ÈME ÉTAGE, APPT N° 16, AGDAL-RABAT',
                    'president_nom' => 'Mr. MOURAD EL MANSOURI',
                    'telephone' => '+212 5 37 XX XX XX',
                    'email' => 'contact@maexpertise.ma',
                    'ice' => '000000000000000',
                    'identifiant_fiscal' => '00000000',
                    'tp' => '00000000',
                    'type_entreprise' => 'PHYSIQUE',
                ];
                Log::info("Utilisation des données par défaut pour la société");
            }

            $response = [
                'message' => 'Données récupérées avec succès',
                'is_renewal' => false,
                'data' => [
                    'client' => $client,
                    'societe' => $societe,
                    'bureau' => $bureau,
                    'gerant' => $client->gerant
                ]
            ];

            Log::info("Réponse finale: ", $response);

            return response()->json($response, 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error("Bureau non trouvé: " . $e->getMessage());
            return response()->json([
                'message' => 'Bureau d\'équipe non trouvé',
                'error' => 'Le bureau demandé n\'existe pas'
            ], 404);
        } catch (\Exception $e) {
            Log::error("Erreur générale dans getContractData: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());

            return response()->json([
                'message' => 'Erreur lors de la récupération des données',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }
}
