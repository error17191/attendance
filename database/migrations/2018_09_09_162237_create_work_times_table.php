<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkTimesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('work_times', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('task_id');
            $table->date('day');
            $table->timestamp('started_work_at');
            $table->timestamp('stopped_work_at')->nullable();
            $table->integer('seconds')->default(0);
            $table->unsignedInteger('day_seconds')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('work_times');
    }
}
