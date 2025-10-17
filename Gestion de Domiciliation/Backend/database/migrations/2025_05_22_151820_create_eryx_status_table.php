<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('eryx_status', function (Blueprint $table) {
            $table->id();
            $table->string('nom')->unique();  
            $table->timestamps();
        });

        // Insert valeurs par défaut
        DB::table('eryx_status')->insert([
            ['nom' => 'EN_ATTENTE'],
            ['nom' => 'EN_COURS'],
            ['nom' => 'TERMINE'],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('eryx_status');
    }
};
