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
        Schema::create('eryx_payments', function (Blueprint $table) {
            $table->id();
            $table->string('reference_type', 50);
            $table->unsignedBigInteger('reference_id');
            $table->double('montant');
            $table->timestamp('date_payment');
            $table->enum('mode_payment', ['ESPECES', 'CHEQUE', 'VIREMENT', 'CARTE']);
            $table->string('reference_payment', 100);
            $table->unsignedBigInteger('received_by');
            $table->string('invoice_number', 50)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Clé étrangère vers users
            $table->foreign('received_by')->references('id')->on('eryx_users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eryx_payments');
    }
};
