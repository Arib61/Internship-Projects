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
        Schema::create('eryx_societe', function (Blueprint $table) {
            $table->id();
            $table->string('societe_nom');
            $table->date('president_date_naissance');
            $table->string('president_cin');
            $table->string('president_adresse');
            $table->string('nom_complet_francais')->unique();
            $table->string('nom_complet_arabe')->unique();
            $table->text('adresse_siege_social_francais');
            $table->text('adresse_siege_social_arabe');
            $table->string('ice', 50)->unique();
            $table->string('rc', 50)->unique();
            $table->string('identifiant_fiscal', 50)->unique();
            $table->string('tp', 50)->unique();
            $table->string('telephone', 20);
            $table->string('email');
            $table->text('logo')->nullable();
            $table->text('icon')->nullable();
            $table->string('website')->nullable();
            $table->float('capital_social');
            $table->enum('type_entreprise', ['MORALE', 'PHYSIQUE']);
            $table->enum('form_juridique', [
                'SARL', 'SARL AU', 'SA', 'SNC', 'ASSOCIATION', 'COOPERATIVE',
                'SAS', 'SASU', 'GIE', 'SCP', 'SCI',
                'ENTREPRISE INDIVIDUELLE', 'AUTO-ENTREPRENEUR',
            ]);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eryx_societe');
    }
};