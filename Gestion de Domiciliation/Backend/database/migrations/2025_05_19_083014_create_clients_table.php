<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('eryx_clients', function (Blueprint $table) {
            $table->id();
            $table->string('nom_francais');
            $table->string('nom_arabe');
            $table->text('adresse_siege_social_francais');
            $table->text('adresse_siege_social_arabe');
            $table->string('ice', 50)->unique();
            $table->binary('cin'); 
            $table->binary('certificat_negative'); 
            $table->string('rc', 50)->unique();
            $table->string('identifiant_fiscal', 50)->unique();
            $table->string('tp', 50)->unique();
            $table->string('tribunal', 100);
            $table->string('type_tribunal', 100);
            $table->string('telephone', 20);
            $table->string('email');
            $table->string('pays', 100);
            $table->string('ville', 100);
            $table->string('website')->nullable();
            $table->float('capital_social');
            $table->enum('type_entreprise', ['MORALE', 'PHYSIQUE']);
            $table->enum('type_client', [
                'SARL', 'SARL AU', 'SA', 'SNC', 'ASSOCIATION', 'COOPERATIVE',
                'SAS', 'SASU', 'GIE', 'SCP', 'SCI',
                'ENTREPRISE INDIVIDUELLE', 'AUTO-ENTREPRENEUR',
            ]);
            $table->enum('status', ['PROSPECT', 'ACTIVE', 'INACTIVE'])->default('PROSPECT');
            $table->foreignId('gerant_id')->nullable()->constrained('eryx_gerants')->onDelete('set null');
            $table->foreignId('gestionnaire_id')->nullable()->constrained('eryx_gestionnaires')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eryx_clients');
    }
};
