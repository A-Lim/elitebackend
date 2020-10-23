<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_files', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('workflow_id')->unsigned();
            $table->bigInteger('order_id')->unsigned();
            $table->string('name');
            $table->string('path');
            $table->string('type');
            $table->bigInteger('uploaded_by')->unsigned();
            $table->datetime('uploaded_at');

            $table->index('order_id');
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
        Schema::dropIfExists('order_files');
    }
}
