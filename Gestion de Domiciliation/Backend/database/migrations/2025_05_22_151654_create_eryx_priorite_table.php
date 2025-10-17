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
        Schema::create('eryx_priorite', function (Blueprint $table) {
            $table->id();
            $table->string('nom')->unique();  // ex : 'faible', 'moyenne', 'élevée'
            $table->timestamps();
        });

        
        DB::table('eryx_priorite')->insert([
            ['nom' => 'faible'],
            ['nom' => 'moyenne'],
            ['nom' => 'élevée'],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('eryx_priorite');
    }
};
