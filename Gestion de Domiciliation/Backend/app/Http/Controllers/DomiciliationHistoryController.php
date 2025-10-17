<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\DomiciliationHistory;
use App\Models\Gestionnaire;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DomiciliationHistoryController extends Controller


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

        $query = DomiciliationHistory::with([
            'domiciliation:id,reference_numero,created_by,gestionnaire_id',
            'modifiedBy:id,name'
        ]);

        if (!$user->is_admin) {
            $query->whereHas('domiciliation', function ($q) use ($user) {
                $q->where('created_by', $user->id)
                    ->orWhere('gestionnaire_id', $user->id);
            });
        }

        // Filtres supplémentaires
        if ($request->has('reference')) {
            $query->whereHas('domiciliation', function ($q) use ($request) {
                $q->where('reference_numero', 'LIKE', '%' . $request->reference . '%');
            });
        }

        if ($request->has('modified_by')) {
            $query->where('modified_by', $request->modified_by);
        }

        if ($request->has('modification_date')) {
            $query->whereDate('modification_date', $request->modification_date);
        }

        $histories = $query->get();

        // ID collect
        $clientIds = $histories->flatMap(fn($h) => [$h->old_values['client_id'] ?? null, $h->new_values['client_id'] ?? null])->filter()->unique()->values();
        $gestionnaireIds = $histories->flatMap(fn($h) => [$h->old_values['gestionnaire_id'] ?? null, $h->new_values['gestionnaire_id'] ?? null])->filter()->unique()->values();
        $userIds = $histories->flatMap(fn($h) => [$h->old_values['created_by'] ?? null, $h->new_values['created_by'] ?? null])->filter()->unique()->values();

        $clients = Client::whereIn('id', $clientIds)->pluck('nom_francais', 'id')->toArray();
        $gestionnaires = Gestionnaire::whereIn('id', $gestionnaireIds)->pluck('prenom', 'id')->toArray();
        $users = User::whereIn('id', $userIds)->pluck('name', 'id')->toArray();

        foreach ($histories as $history) {
            $old = $history->old_values;
            $new = $history->new_values;

            if (isset($old['client_id'])) $old['client_id'] = $clients[$old['client_id']] ?? 'Client inconnu (ID: ' . $old['client_id'] . ')';
            if (isset($new['client_id'])) $new['client_id'] = $clients[$new['client_id']] ?? 'Client inconnu (ID: ' . $new['client_id'] . ')';

            if (isset($old['gestionnaire_id'])) $old['gestionnaire_id'] = $gestionnaires[$old['gestionnaire_id']] ?? 'Gestionnaire inconnu (ID: ' . $old['gestionnaire_id'] . ')';
            if (isset($new['gestionnaire_id'])) $new['gestionnaire_id'] = $gestionnaires[$new['gestionnaire_id']] ?? 'Gestionnaire inconnu (ID: ' . $new['gestionnaire_id'] . ')';

            if (isset($old['created_by'])) $old['created_by'] = $users[$old['created_by']] ?? 'Utilisateur inconnu (ID: ' . $old['created_by'] . ')';
            if (isset($new['created_by'])) $new['created_by'] = $users[$new['created_by']] ?? 'Utilisateur inconnu (ID: ' . $new['created_by'] . ')';

            $history->old_values = $old;
            $history->new_values = $new;
        }

        Log::info('Domiciliation histories retrieved successfully', $histories->toArray());

        return response()->json($histories->toArray());
    }


    public function destroy($id)
    {
        $history = DomiciliationHistory::findOrFail($id);
        $history->delete();

        return response()->json(['message' => 'Historique supprimé avec succès']);
    }

    public function show($id, Request $request)
    {
        $user = $this->getAuthenticatedUser($request);
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $history = DomiciliationHistory::with(['domiciliation', 'modifiedBy'])->findOrFail($id);

        if (!$user->is_admin) {
            $dom = $history->domiciliation;

            if (!$dom || ($dom->created_by != $user->id && $dom->gestionnaire_id != $user->id)) {
                return response()->json(['error' => 'Forbidden'], 403);
            }
        }

        return response()->json($history);
    }
}
