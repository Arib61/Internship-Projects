<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateCheckUniqueAdminTrigger extends Migration
{
    public function up()
    {
        DB::unprepared("
            CREATE TRIGGER check_unique_admin
            BEFORE INSERT ON eryx_users
            FOR EACH ROW
            BEGIN
                IF NEW.is_admin = TRUE AND (SELECT COUNT(*) FROM eryx_users WHERE is_admin = TRUE) >= 1 THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Only one admin allowed';
                END IF;
            END
        ");
    }

    public function down()
    {
        DB::unprepared("DROP TRIGGER IF EXISTS check_unique_admin");
    }
}

