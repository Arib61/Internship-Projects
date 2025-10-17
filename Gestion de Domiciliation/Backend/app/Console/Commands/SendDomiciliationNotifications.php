<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Domiciliation;
use App\Mail\NotificationFinProcheGestionnaire;
use App\Mail\NotificationFinTermineeClient;
use App\Mail\NotificationFinProcheClient; // New mail class for 15-day notice
use App\Mail\NotificationFinRappelClient; // New mail class for 5-day after notice
use App\Models\Societe;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendDomiciliationNotifications extends Command
{
    protected $signature = 'domiciliations:send-notifications';
    protected $description = 'Send domiciliation expiration notifications';

    public function handle()
    {
        $today = Carbon::today();
        $societe = Societe::first();
        // Gestionnaire notifications
        $this->sendGestionnaireNotifications($today, $societe);
        
        // Client notifications
        $this->sendClient15DayNotifications($today, $societe);
        $this->sendClientEndDateNotifications($today, $societe);
        $this->sendClient5DayAfterNotifications($today, $societe);

        $this->info('All notifications processed.');
    }

    /**
     * Send notifications to gestionnaire 5 days before expiration
     */
    protected function sendGestionnaireNotifications(Carbon $today, Societe $societe)
    {
        $date5jours = $today->copy()->addDays(5)->toDateString();

        Domiciliation::whereRaw('DATE(date_fin) = ?', [$date5jours])
            ->each(function ($domiciliation) {
                $this->processGestionnaireNotification($domiciliation);
            });
    }

    protected function processGestionnaireNotification($domiciliation)
    {
        try {
            $gestionnaire = $domiciliation->client->gestionnaire;
            $societe = Societe::first();

            if ($gestionnaire && $gestionnaire->email) {
                Mail::to($gestionnaire->email)
                    ->send(new NotificationFinProcheGestionnaire($domiciliation, $societe));
                Log::info("5-day notice sent to gestionnaire", [
                    'domiciliation_id' => $domiciliation->id,
                    'manager_email' => $gestionnaire->email,
                    'societe' => $societe
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Failed to send 5-day notice to gestionnaire", [
                'domiciliation_id' => $domiciliation->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send notifications to clients 15 days before expiration
     */
    protected function sendClient15DayNotifications(Carbon $today, Societe $societe)
    {
        $date15jours = $today->copy()->addDays(15)->toDateString();

        Domiciliation::whereRaw('DATE(date_fin) = ?', [$date15jours])
            ->each(function ($domiciliation) {
                $this->processClient15DayNotification($domiciliation);
            });
    }

    protected function processClient15DayNotification($domiciliation)
    {
        try {
            $client = $domiciliation->client;
            $societe = Societe::first();
            if ($client && $client->email) {
                Mail::to($client->email)
                    ->send(new NotificationFinProcheClient($domiciliation, $societe));

                Log::info("15-day notice sent to client", [
                    'domiciliation_id' => $domiciliation->id,
                    'client_email' => $client->email
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Failed to send 15-day notice to client", [
                'domiciliation_id' => $domiciliation->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send notifications on the end date
     */
    protected function sendClientEndDateNotifications(Carbon $today)
    {
        $todayString = $today->toDateString();

        Domiciliation::whereRaw('DATE(date_fin) = ?', [$todayString])
            ->each(function ($domiciliation) {
                $this->processEndDateNotification($domiciliation);
            });
    }

    protected function processEndDateNotification($domiciliation)
    {
        try {
            $client = $domiciliation->client;
            $gestionnaire = $client->gestionnaire;
            $societe = Societe::first();
            Log::info("Domiciliations finies :" . $domiciliation . "\n Client : " . $domiciliation->client . "\n Gestionnaire :" . $gestionnaire->email);

            // Send to client
            if ($client->email) {
                try {
                    Mail::to($client->email)
                        ->send(new NotificationFinTermineeClient($domiciliation, $societe));
                    // ->queue() for production use

                    Log::info("End notice sent to client", [
                        'domiciliation_id' => $domiciliation->id,
                        'client_email' => $client->email,
                        'time' => now()->toDateTimeString()
                    ]);
                } catch (\Exception $e) {
                    Log::error("Failed to send client end notification", [
                        'email' => $client->email,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error("Failed to send end date notices", [
                'domiciliation_id' => $domiciliation->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send notifications to clients 5 days after expiration
     */
    protected function sendClient5DayAfterNotifications(Carbon $today, Societe $societe)
    {
        $date5joursApres = $today->copy()->subDays(5)->toDateString();

        Domiciliation::whereRaw('DATE(date_fin) = ?', [$date5joursApres])
            ->each(function ($domiciliation) {
                $this->processClient5DayAfterNotification($domiciliation);
            });
    }

    protected function processClient5DayAfterNotification($domiciliation)
    {
        try {
            $client = $domiciliation->client;
            $societe = Societe::first();

            if ($client && $client->email) {
                Mail::to($client->email)
                    ->send(new NotificationFinRappelClient($domiciliation, $societe));

                Log::info("5-day after notice sent to client", [
                    'domiciliation_id' => $domiciliation->id,
                    'client_email' => $client->email
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Failed to send 5-day after notice to client", [
                'domiciliation_id' => $domiciliation->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}