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
        Schema::create('eryx_gerants', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('cin', 20);
            $table->text('address')->nullable();
            $table->timestamp('date_naissance')->nullable();
            $table->string('telephone', 20)->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eryx_gerants');
    }
};
