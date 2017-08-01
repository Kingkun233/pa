<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHomeworksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('homeworks', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('course_id');
            $table->integer('class_id');
            $table->string('requirement');
            $table->string('standard');
            $table->string('name');
            $table->dateTime('start_day');
            $table->dateTime('end_day');
            $table->integer('extend_from');
            $table->smallInteger('round');
            $table->smallInteger('state');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('homeworks');
    }
}
