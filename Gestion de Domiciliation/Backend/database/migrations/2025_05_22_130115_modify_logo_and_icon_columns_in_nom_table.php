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
        Schema::table('eryx_societe', function (Blueprint $table) {
            $table->text('logo')->nullable()->change();
            $table->text('icon')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('eryx_societe', function (Blueprint $table) {
            $table->binary('logo')->nullable()->change();
            $table->binary('icon')->nullable()->change();
        });
    }
};
