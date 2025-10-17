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
        Schema::create('eryx_task', function (Blueprint $table) {
        $table->id();
        $table->text('description');
        $table->date('date_debut');
        $table->date('date_fin');
        $table->unsignedBigInteger('status_id');      // FK vers eryx_status
        $table->unsignedBigInteger('priorite_id');    // FK vers eryx_priorite
        $table->foreignId('gestionnaire_id')->constrained('eryx_gestionnaires')->onDelete('cascade');
        $table->foreign('priorite_id')->references('id')->on('eryx_priorite')->onDelete('restrict');
       
        
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eryx_task');
    }
};





