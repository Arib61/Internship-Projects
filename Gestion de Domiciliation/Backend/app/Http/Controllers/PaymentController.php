<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    // Liste tous les paiements
    public function index()
    {
        return Payment::all();
    }

    // Crée un nouveau paiement
    public function store(Request $request)
    {
        $request->validate([
            'reference_type' => 'required|string|max:50',
            'reference_id' => 'required|integer',
            'montant' => 'required|numeric',
            'date_payment' => 'required|date',
            'mode_payment' => 'required|in:ESPECES,CHEQUE,VIREMENT,CARTE',
            'reference_payment' => 'required|string|max:100',
            'received_by' => 'required|exists:eryx_users,id',
            'invoice_number' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $payment = Payment::create($request->all());

        return response()->json($payment, 201);
    }

    // Affiche un paiement précis
    public function show($id)
    {
        $payment = Payment::findOrFail($id);
        return response()->json($payment);
    }

    // Met à jour un paiement
    public function update(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);

        $request->validate([
            'reference_type' => 'sometimes|string|max:50',
            'reference_id' => 'sometimes|integer',
            'montant' => 'sometimes|numeric',
            'date_payment' => 'sometimes|date',
            'mode_payment' => 'sometimes|in:ESPECES,CHEQUE,VIREMENT,CARTE',
            'reference_payment' => 'sometimes|string|max:100',
            'received_by' => 'sometimes|exists:eryx_users,id',
            'invoice_number' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $payment->update($request->all());

        return response()->json($payment);
    }

    // Supprime un paiement
    public function destroy($id)
    {
        Payment::destroy($id);
        return response()->json(null, 204);
    }
}

