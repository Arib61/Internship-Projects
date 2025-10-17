<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateSituationEnumInEryxBureauxEquipeTable extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE eryx_bureaux_equipe MODIFY situation ENUM('DEMANDE', 'EN_COURS', 'REJETTE', 'RESILIE') NOT NULL");
    }

    public function down()
    {
        DB::statement("ALTER TABLE eryx_bureaux_equipe MODIFY situation ENUM('DEMANDE', 'EN_COURS', 'REJETTE') NOT NULL");
    }
}
