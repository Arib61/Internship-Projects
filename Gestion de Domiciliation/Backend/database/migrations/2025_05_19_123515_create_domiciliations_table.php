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
        Schema::create('eryx_domiciliations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('eryx_clients');
            $table->foreignId('gestionnaire_id')->constrained('eryx_gestionnaires');
            $table->foreignId('created_by')->constrained('eryx_users');
            $table->date('date_debut');
            $table->date('date_fin');
            $table->integer('renewal_count')->default(0);
            $table->double('montant');
            $table->enum('situation', ['DEMANDE', 'EN_COURS', 'REJETTE', 'RESILIE', 'ACTIVE'])
                ->default('DEMANDE');
            $table->boolean('payement')->default(false);
            $table->string('reference_numero', 50)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eryx_domiciliations');
    }
};
