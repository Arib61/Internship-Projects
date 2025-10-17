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
        Schema::create('eryx_bureaux_equipe_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bureaux_equipe_id')->constrained('eryx_bureaux_equipe');
            $table->foreignId('modified_by')->constrained('eryx_users');
            $table->timestamp('modification_date');
            $table->json('old_values');
            $table->json('new_values');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes(); // 👉 Ajout du champ deleted_at pour soft delete
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eryx_bureaux_equipe_histories');
    }
};
