<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AlterPostNullableFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $table = DB::getTablePrefix().'posts';
        DB::statement("ALTER TABLE {$table} MODIFY excerpt TEXT NULL");
        DB::statement("ALTER TABLE {$table} MODIFY meta_description TEXT NULL");
        DB::statement("ALTER TABLE {$table} MODIFY meta_keywords TEXT NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $table = DB::getTablePrefix().'posts';
        DB::statement("ALTER TABLE {$table} MODIFY excerpt TEXT NOT NULL");
        DB::statement("ALTER TABLE {$table} MODIFY meta_description TEXT NOT NULL");
        DB::statement("ALTER TABLE {$table} MODIFY meta_keywords TEXT NOT NULL");
    }
}
