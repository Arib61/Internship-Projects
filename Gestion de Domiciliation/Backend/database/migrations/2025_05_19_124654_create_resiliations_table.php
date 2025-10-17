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
            Schema::create('eryx_resiliations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('domiciliation_id')->constrained('eryx_domiciliations');
                $table->date('date_resiliation');
                $table->text('raison');
                $table->foreignId('created_by')->constrained('eryx_users');
                $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED']);
                $table->timestamps();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eryx_resiliations');
    }
};
