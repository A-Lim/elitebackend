<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserUsergroupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_usergroup', function (Blueprint $table) {
            $table->bigInteger('usergroup_id')->unsigned();
            $table->bigInteger('user_id')->unsigned();
            $table->foreign('usergroup_id')
                  ->references('id')
                  ->on('usergroups')
                  ->onDelete('cascade');
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
            $table->primary(['usergroup_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_usergroup');
    }
}
