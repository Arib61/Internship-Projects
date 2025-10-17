<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEryxHistoryTaskTable extends Migration
{
    public function up()
    {
        Schema::create('eryx_history_task', function (Blueprint $table) {
            $table->id();
            $table->text('description');

            $table->unsignedBigInteger('gestionnaire_id'); 
            $table->unsignedBigInteger('task_id'); 

            $table->timestamps();

            // Clés étrangères
            $table->foreign('gestionnaire_id')
                ->references('id')
                ->on('eryx_gestionnaires')
                ->onDelete('cascade');

            $table->foreign('task_id')
                ->references('id')
                ->on('eryx_task')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('eryx_history_task');
    }
}
