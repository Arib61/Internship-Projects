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
        Schema::table('eryx_clients', function (Blueprint $table) {
            $table->text('cin')->nullable()->change();
            $table->text('certificat_negative')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('eryx_clients', function (Blueprint $table) {
            $table->binary('cin')->nullable()->change();
            $table->binary('certificat_negative')->nullable()->change();
        });
    }
};
