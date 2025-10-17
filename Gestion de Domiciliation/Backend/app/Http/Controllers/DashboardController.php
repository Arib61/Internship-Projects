<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Domiciliation;
use App\Models\BureauEquipe;
use App\Models\Client;
use App\Models\Payment;
use App\Models\RenewalDomiciliation;
use App\Models\RenewalBureauEquipe;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function getStats()
    {
        $lastMonth = now()->subDays(30);

        return response()->json([
            'domiciliations' => [
                'total' => Domiciliation::count(),
                'trend' => Domiciliation::where('created_at', '>=', $lastMonth)->count(),
            ],
            'bureaux' => [
                'total' => BureauEquipe::count(),
                'trend' => BureauEquipe::where('created_at', '>=', $lastMonth)->count(),
            ],
            'clients' => [
                'total' => Client::count(),
                'trend' => Client::where('created_at', '>=', $lastMonth)->count(),
            ],
            'revenue' => [
                'total' => Payment::sum('montant'),
                'trend' => Payment::where('created_at', '>=', $lastMonth)->sum('montant'),
            ],
        ]);
    }

    public function revenueGraph()
    {
        $data = Payment::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, SUM(montant) as total')
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return response()->json($data);
    }

    public function situationBreakdown()
    {
        $data = Domiciliation::select('situation', DB::raw('COUNT(*) as total'))
            ->groupBy('situation')
            ->get();

        return response()->json($data);
    }

    public function expiringContracts(Request $request)
    {
        $days = $request->get('days', 15);
        $targetDate = Carbon::now()->addDays($days);

        $contracts = Domiciliation::whereDate('date_fin', '<=', $targetDate)
            ->whereNull('deleted_at')
            ->select('id', 'client_id', 'date_fin', 'montant', 'reference_numero')
            ->with('client:id,nom')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'reference_numero' => $item->reference_numero,
                    'client_nom' => $item->client->nom ?? '',
                    'type' => 'domiciliation',
                    'date_fin' => $item->date_fin,
                    'days_remaining' => Carbon::parse($item->date_fin)->diffInDays(Carbon::now(), false),
                    'montant' => $item->montant,
                ];
            });

        $bureaux = BureauEquipe::whereDate('date_fin', '<=', $targetDate)
            ->whereNull('deleted_at')
            ->select('id', 'client_id', 'date_fin', 'montant', 'reference_numero')
            ->with('client:id,nom')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'reference_numero' => $item->reference_numero,
                    'client_nom' => $item->client->nom ?? '',
                    'type' => 'bureau',
                    'date_fin' => $item->date_fin,
                    'days_remaining' => Carbon::parse($item->date_fin)->diffInDays(Carbon::now(), false),
                    'montant' => $item->montant,
                ];
            });

        return response()->json($contracts->merge($bureaux)->sortBy('days_remaining')->values());
    }

    public function recentActivities()
    {
        $activities = [];

        $dom = Domiciliation::latest()->take(5)->get()->map(function ($item) {
            return [
                'id' => 'dom_' . $item->id,
                'type' => 'creation',
                'title' => 'Domiciliation créée',
                'description' => 'Contrat ' . $item->reference_numero . ' pour ' . ($item->client->nom ?? ''),
                'created_at' => $item->created_at,
            ];
        });

        $bur = BureauEquipe::latest()->take(5)->get()->map(function ($item) {
            return [
                'id' => 'bur_' . $item->id,
                'type' => 'creation',
                'title' => 'Bureau Équipe créé',
                'description' => 'Contrat ' . $item->reference_numero . ' pour ' . ($item->client->nom ?? ''),
                'created_at' => $item->created_at,
            ];
        });

        $payments = Payment::latest()->take(5)->get()->map(function ($item) {
            return [
                'id' => 'pay_' . $item->id,
                'type' => 'payment',
                'title' => 'Paiement reçu',
                'description' => 'Paiement de ' . number_format($item->montant, 2, ',', ' ') . ' MAD',
                'created_at' => $item->created_at,
            ];
        });

        return response()->json($dom->merge($bur)->merge($payments)->sortByDesc('created_at')->values());
    }

    public function chartData(Request $request)
    {
        $period = (int) $request->get('period', 30);
        $startDate = now()->subDays($period);

        $contracts = Domiciliation::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $bureaux = BureauEquipe::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $revenue = Payment::select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(montant) as total'))
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $labels = collect();
        for ($i = 0; $i < $period; $i++) {
            $day = now()->subDays($period - 1 - $i)->format('Y-m-d');
            $labels->push($day);
        }

        return response()->json([
            'contracts' => [
                'labels' => $labels->map(fn ($d) => \Carbon\Carbon::parse($d)->format('d M')),
                'domiciliations' => $labels->map(fn ($d) => $contracts[$d]->count ?? 0),
                'bureaux' => $labels->map(fn ($d) => $bureaux[$d]->count ?? 0),
            ],
            'status' => [
                'labels' => ['ACTIVE', 'RESILIE', 'EXPIRE'],
                'data' => [
                    Domiciliation::where('situation', 'ACTIVE')->count(),
                    Domiciliation::where('situation', 'RESILIE')->count(),
                    Domiciliation::where('situation', 'EXPIRE')->count(),
                ]
            ],
            'revenue' => [
                'labels' => $labels->map(fn ($d) => \Carbon\Carbon::parse($d)->format('d M')),
                'data' => $labels->map(fn ($d) => $revenue[$d]->total ?? 0),
            ]
        ]);
    }
}
