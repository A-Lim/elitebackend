<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSystemSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() 
    {
        Schema::create('systemsettings', function (Blueprint $table) 
        {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('systemsettingcategory_id');
            $table->string('name', 100);
            $table->string('code', 100)->unique();
            $table->text('description')->nullable();
            $table->text('value')->nullable();
            $table->timestamps();

            $table->foreign('systemsettingcategory_id')
                ->references('id')->on('systemsettingcategories')
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
        Schema::dropIfExists('systemsettings');
    }
}
