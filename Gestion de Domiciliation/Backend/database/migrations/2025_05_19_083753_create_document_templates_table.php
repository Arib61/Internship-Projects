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
        Schema::create('eryx_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->longText('content');
            $table->string('path');
            $table->enum('type', ['blade', 'word', 'pdf', 'html'])->default('blade');
            $table->enum('document_type', ['CONTRAT_DOMICILIATION', 'ATTESTATION_DOMICILIATION', 'FACTURE', 'RECU_PAIEMENT', 'CERTIFICAT', 'AUTRE']);
            $table->json('variables');
            $table->string('version');
            $table->foreignId('created_by')->nullable()->constrained('eryx_users')->onDelete('set null')->onUpdate('cascade');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes(); // 👉 Ajout du champ deleted_at pour soft delete
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eryx_templates');
    }
};
