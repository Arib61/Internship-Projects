<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'eryx_clients',
            'eryx_documents',
            'eryx_domiciliations',
            'eryx_domiciliation_histories',
            'eryx_gerants',
            'eryx_gestionnaires',
            'eryx_resiliations',
            'eryx_societes',
            'eryx_templates',
            'eryx_users',
           
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (!Schema::hasColumn($tableName, 'deleted_at')) {
                        $table->softDeletes();
                    }
                });
            }
        }
    }

    public function down(): void
    {
        $tables = [
            'eryx_clients',
            'eryx_documents',
            'eryx_domiciliations',
            'eryx_domiciliation_histories',
            'eryx_gerants',
            'eryx_gestionnaires',
            'eryx_resiliations',
            'eryx_societes',
            'eryx_templates',
            'eryx_users',
          
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropSoftDeletes();
                });
            }
        }
    }
};
