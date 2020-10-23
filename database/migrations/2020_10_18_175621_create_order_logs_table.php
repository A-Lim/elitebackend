<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('workflow_id')->unsigned();
            $table->bigInteger('order_id')->unsigned();
            $table->bigInteger('process_id')->unsigned();
            $table->string('from_status', 100);
            $table->string('to_status', 100);
            $table->bigInteger('created_by')->unsigned();
            $table->timestamps();
            
            $table->index('order_id');
            $table->foreign('workflow_id')
                ->references('id')
                ->on('workflows')
                ->onDelete('cascade');

            $table->foreign('process_id')
                ->references('id')
                ->on('processes')
                ->onDelete('cascade');

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
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
        Schema::dropIfExists('order_logs');
    }
}
