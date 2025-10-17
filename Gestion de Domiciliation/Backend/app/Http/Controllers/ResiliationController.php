<?php

namespace App\Http\Controllers;

use App\Models\Resiliation;
use App\Models\Domiciliation;
use App\Models\Societe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class ResiliationController extends Controller
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

        $query = Resiliation::with('domiciliation');

        if (!$user->is_admin) {
            $query->whereHas('domiciliation', function ($q) use ($user) {
                $q->where('created_by', $user->id)
                    ->orWhere('gestionnaire_id', $user->id);
            });
        }

        return $query->get();
    }

    public function show($id, Request $request)
    {
        $user = $this->getAuthenticatedUser($request);
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $resiliation = Resiliation::with(['domiciliation', 'user'])->findOrFail($id);

        if (!$user->is_admin) {
            $dom = $resiliation->domiciliation;
            if (!$dom || ($dom->created_by != $user->id && $dom->gestionnaire_id != $user->id)) {
                return response()->json(['error' => 'Forbidden'], 403);
            }
        }

        return $resiliation;
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'domiciliation_id' => 'required|exists:eryx_domiciliations,id',
            'date_resiliation' => 'required|date',
            'raison' => 'required|string',
            'created_by' => 'required|exists:eryx_users,id',
            'status' => 'required|in:PENDING,APPROVED,REJECTED',
        ]);

        $resiliation = Resiliation::create($validated);

        return response()->json([
            'message' => 'Résiliation créée avec succès',
            'data' => $resiliation
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $resiliation = Resiliation::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:PENDING,APPROVED,REJECTED',
            'raison' => 'sometimes|string',
        ]);

        $resiliation->update($validated);

        return response()->json([
            'message' => 'Résiliation mise à jour avec succès',
            'data' => $resiliation
        ], 200);
    }

    public function destroy($id, Request $request)
    {
        $user = $this->getAuthenticatedUser($request);
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $resiliation = Resiliation::with('domiciliation')->find($id);
        if (!$resiliation) {
            return response()->json(['message' => 'Résiliation non trouvée.'], 404);
        }

        if (!$user->is_admin) {
            $dom = $resiliation->domiciliation;
            if (!$dom || ($dom->created_by != $user->id && $dom->gestionnaire_id != $user->id)) {
                return response()->json(['error' => 'Forbidden'], 403);
            }
        }

        $resiliation->delete();
        return response()->json(['message' => 'Résiliation supprimée avec succès.']);
    }

    public function downloadResiliationWord($id)
    {
        try {
            $resiliation = Resiliation::withTrashed()
                ->with(['domiciliation', 'domiciliation.client'])
                ->findOrFail($id);

            $client = $resiliation->domiciliation->client;

            $templatePath = base_path('../Frontend/public/doc/RESILIATION.docx');
            $storagePath = storage_path('resiliations');
            if (!File::exists($storagePath)) {
                File::makeDirectory($storagePath, 0755, true);
            }

            $timestamp = time();
            $filenameWord = "resiliation_{$id}_{$timestamp}.docx";
            $outputPathWord = $storagePath . DIRECTORY_SEPARATOR . $filenameWord;

            $replacements = [
                'Rabat le 17/04/2025' => 'Rabat le ' . Carbon::now()->format('d/m/Y'),
                '8/11/2022' => Carbon::parse($resiliation->domiciliation->date_debut)->format('d/m/Y'),
                'BEEMASTER SERVICE' => strtoupper($client->nom_francais),
                '45 AVENUE DE FRANCE APPT 08 AGDAL RABAT.' => $client->adresse_siege_social_francais,
                '16/04/2025' => Carbon::parse($resiliation->date_resiliation)->format('d/m/Y'),
            ];

            $jsonPath = $storagePath . "/resiliation_data_{$id}.json";
            file_put_contents($jsonPath, json_encode([
                'template_path' => $templatePath,
                'output_path' => $outputPathWord,
                'replacements' => $replacements
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            $scriptPath = base_path('app/Http/Scripts/RESILIATION.py');
            $cmd = escapeshellcmd("python \"$scriptPath\" \"$jsonPath\"");
            shell_exec($cmd);

            if (!file_exists($outputPathWord)) {
                return response()->json(['error' => 'Le fichier Word n’a pas été généré.'], 500);
            }

            return response()->download($outputPathWord, $filenameWord)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la génération du Word',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function downloadResiliationPdf($id)
    {
        try {
            $resiliation = Resiliation::withTrashed()
                ->with(['domiciliation', 'domiciliation.client'])
                ->findOrFail($id);

            $client = $resiliation->domiciliation->client;

            $templatePath = base_path('../Frontend/public/doc/RESILIATION.docx');
            $storagePath = storage_path('resiliations');
            if (!File::exists($storagePath)) {
                File::makeDirectory($storagePath, 0755, true);
            }

            $timestamp = time();
            $filenameWord = "resiliation_{$id}_{$timestamp}.docx";
            $filenamePdf = "resiliation_{$id}_{$timestamp}.pdf";
            $outputPathWord = $storagePath . DIRECTORY_SEPARATOR . $filenameWord;
            $outputPathPdf = $storagePath . DIRECTORY_SEPARATOR . $filenamePdf;

            $replacements = [
                'Rabat le 17/04/2025' => 'Rabat le ' . Carbon::now()->format('d/m/Y'),
                '8/11/2022' => Carbon::parse($resiliation->domiciliation->date_debut)->format('d/m/Y'),
                'BEEMASTER SERVICE' => strtoupper($client->nom_francais),
                '45 AVENUE DE FRANCE APPT 08 AGDAL RABAT.' => $client->adresse_siege_social_francais,
                '16/04/2025' => Carbon::parse($resiliation->date_resiliation)->format('d/m/Y'),
            ];

            $jsonPath = $storagePath . "/resiliation_data_{$id}.json";
            file_put_contents($jsonPath, json_encode([
                'template_path' => $templatePath,
                'output_path' => $outputPathWord,
                'replacements' => $replacements
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            $scriptPath = base_path('app/Http/Scripts/RESILIATION.py');
            $cmd = escapeshellcmd("python \"$scriptPath\" \"$jsonPath\"");
            shell_exec($cmd);

            if (!file_exists($outputPathPdf)) {
                return response()->json(['error' => 'Le fichier PDF n’a pas été généré.'], 500);
            }

            return response()->download($outputPathPdf, $filenamePdf)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la génération du PDF',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function generateContractPdf($id)
    {
        try {
            $domiciliation = Domiciliation::with(['client', 'gestionnaire'])->findOrFail($id);
            $societe = Societe::first();
            $client = $domiciliation->client;

            $data = compact(
                'societe',
                'domiciliation',
                'client'
            );

            $htmlContract = view('templates.resiliationContract', $data)->render();

            return response()->json([
                'htmlContract' => $htmlContract,
            ], 200, [
                'Content-Type' => 'application/json',
                'Cache-Control' => 'no-cache',
            ]);
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
            $client = $domiciliation->client;

            $data = compact(
                'domiciliation',
                'societe',
                'client',
            );

            // Return raw HTML to frontend
            return response()->json([
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur génération HTML', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }
}
