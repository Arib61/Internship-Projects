<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Gestionnaire;
use App\Models\Abonnement;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Check if an admin already exists
        if (User::where('is_admin', true)->exists()) {
            $this->command->warn('Admin already exists. Skipping...');
            return;
        }

        // Create the admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'), // Use a secure password!
            'is_admin' => true,
        ]);

        // Création du token complet (avec préfixe '2|...')
        $token = $admin->createToken('auth_token')->plainTextToken;

        // Enregistrement du token complet dans remember_token
        $admin->remember_token = $token;
        $admin->save();

        // Create the linked gestionnaire
        Gestionnaire::create([
            'nom' => 'Admin',
            'prenom' => 'User',
            'email' => 'admin@example.com',
            'telephone' => '0600000000',
            'adresse' => '123 Rue de Laravel',
            'ville' => 'Casablanca',
            'user_id' => $admin->id,
        ]);

        // Créer un abonnement actif de 6 mois à partir d'aujourd'hui
        $dateDebut = Carbon::now();
        $dateFin = $dateDebut->copy()->addMonths(6);
        $joursRestants = $dateDebut->diffInDays($dateFin);

        Abonnement::create([
            'date_debut' => $dateDebut,
            'date_fin' => $dateFin,
            'jours_restants' => $joursRestants,
            'statut' => 'actif',
        ]);

        $this->command->info('Admin user, linked gestionnaire, and abonnement created.');
    }
}
