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
    Schema::create('eryx_resiliations_bureaux_equipe', function (Blueprint $table) {
        $table->id();
        $table->foreignId('bureaux_equipe_id')->constrained('eryx_bureaux_equipe');
        $table->date('date_resiliation');
        $table->text('raison');
        $table->foreignId('created_by')->constrained('eryx_users');
        $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED']);
        $table->timestamps();
        $table->softDeletes(); // 👉 Ajout du champ deleted_at pour soft delete
    });
}

public function down(): void
{
    Schema::dropIfExists('eryx_resiliations_bureaux_equipe');
}

};
