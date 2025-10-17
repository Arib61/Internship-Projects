<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateLimitOneAdminTrigger extends Migration
{
    public function up()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS limit_one_admin');

        DB::unprepared("
            CREATE TRIGGER limit_one_admin
            BEFORE INSERT ON eryx_users
            FOR EACH ROW
            BEGIN
                DECLARE admin_count INT;

                SELECT COUNT(*) INTO admin_count FROM eryx_users WHERE is_admin = TRUE;

                IF NEW.is_admin = TRUE AND admin_count >= 1 THEN
                    SIGNAL SQLSTATE '45000' 
                    SET MESSAGE_TEXT = 'Un administrateur existe déjà. Impossible d''ajouter un autre admin.';
                END IF;
            END
        ");
    }

    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS limit_one_admin');
    }
}
