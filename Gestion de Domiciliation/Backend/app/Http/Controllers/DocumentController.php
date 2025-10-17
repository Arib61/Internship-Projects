<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function index()
    {
        return Document::with('uploader')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'path' => 'required|string|max:255',
            'type' => 'required|in:CIN,CERTIFICAT_NEGATIVE,ATTESTATION,CONTRAT,FACTURE,RECU_PAIEMENT,AUTRE',
            'size' => 'required|integer',
            'uploaded_by' => 'required|exists:eryx_users,id',
            'entity_type' => 'required|string|max:50',
            'entity_id' => 'required|integer',
            'validity_start_date' => 'required|date',
            'validity_end_date' => 'required|date|after_or_equal:validity_start_date',
        ]);

        $document = Document::create($validated);

        return response()->json([
            'message' => 'Document créé avec succès',
            'data' => $document
        ], 201);
    }

    public function show(Document $document)
    {
        return $document->load('uploader');
    }

    public function update(Request $request, Document $document)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'path' => 'required|string|max:255',
            'type' => 'required|in:CIN,CERTIFICAT_NEGATIVE,ATTESTATION,CONTRAT,FACTURE,RECU_PAIEMENT,AUTRE',
            'size' => 'required|integer',
            'uploaded_by' => 'required|exists:eryx_users,id',
            'entity_type' => 'required|string|max:50',
            'entity_id' => 'required|integer',
            'validity_start_date' => 'required|date',
            'validity_end_date' => 'required|date|after_or_equal:validity_start_date',
        ]);

        $document->update($validated);

        return response()->json([
            'message' => 'Document mis à jour avec succès',
            'data' => $document
        ]);
    }

    public function destroy(Document $document)
    {
        $document->delete();
        return response()->json(['message' => 'Document supprimé avec succès']);
    }
}
