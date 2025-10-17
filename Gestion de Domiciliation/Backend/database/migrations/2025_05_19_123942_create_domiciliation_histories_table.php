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
        Schema::create('eryx_domiciliation_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('domiciliation_id')->constrained('eryx_domiciliations');
            $table->foreignId('modified_by')->constrained('eryx_users');
            $table->timestamp('modification_date');
            // Utiliser longText au lieu de json pour avoir plus de contrôle sur le format
            $table->longText('old_values');
            $table->longText('new_values');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eryx_domiciliation_histories');
    }
};