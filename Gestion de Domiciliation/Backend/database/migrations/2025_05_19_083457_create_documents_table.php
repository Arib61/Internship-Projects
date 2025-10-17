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
        Schema::create('eryx_documents', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('path', 255);
            $table->enum('type', [
                'CIN', 'CERTIFICAT_NEGATIVE', 'ATTESTATION', 'CONTRAT',
                'FACTURE', 'RECU_PAIEMENT', 'AUTRE'
            ]);
            $table->integer('size');
            $table->foreignId('uploaded_by')->constrained('eryx_users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('entity_type', 50);
            $table->unsignedBigInteger('entity_id');
            $table->date('validity_start_date');
            $table->date('validity_end_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eryx_documents');
    }
};
