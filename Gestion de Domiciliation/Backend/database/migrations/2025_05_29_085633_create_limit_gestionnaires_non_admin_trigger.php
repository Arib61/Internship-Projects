<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateLimitGestionnairesNonAdminTrigger extends Migration
{
    public function up()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS limit_gestionnaires_non_admin');

        DB::unprepared("
            CREATE TRIGGER limit_gestionnaires_non_admin
            BEFORE INSERT ON eryx_users
            FOR EACH ROW
            BEGIN
                DECLARE non_admin_count INT;

                SELECT COUNT(*) INTO non_admin_count
                FROM eryx_users u
                JOIN eryx_gestionnaires g ON g.user_id = u.id
                WHERE u.is_admin = 0;

                IF non_admin_count >= 4 THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Limite de 4 gestionnaires non admin atteinte.';
                END IF;
            END
        ");
    }

    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS limit_gestionnaires_non_admin');
    }
}

