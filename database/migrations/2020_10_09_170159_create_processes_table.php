<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProcessesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('processes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('workflow_id')->unsigned();
            $table->string('name', 100);
            $table->text('statuses');
            $table->string('default', 100);
            $table->integer('seq')->unsigned();
            $table->boolean('is_published')->default(true);

            $table->foreign('workflow_id')
                  ->references('id')
                  ->on('workflows')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('processes');
    }
}
