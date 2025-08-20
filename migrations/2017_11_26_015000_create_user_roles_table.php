<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateUserRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_roles', function (Blueprint $table) {
            $columns = collect(DB::connection()->getSchemaBuilder()->getColumns(DB::getTablePrefix().'users'));
            $idType = $columns->firstWhere('name', 'id')['type_name'] ?? 'integer';
            if ($idType === 'bigint') {
                $table->unsignedBigInteger('user_id')->index();
            } else {
                $table->unsignedInteger('user_id')->index();
            }

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('role_id')->unsigned()->index();
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->primary(['user_id', 'role_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_roles');
    }
}
