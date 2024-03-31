<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDoctorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('doctors', function (Blueprint $table) {
            $table->integer('id')->primary()->unsigned();

            $table->string('mobile');
            $table->string('image_path');
            $table->integer('gender');
            $table->time('session_duration');
            $table->time('old_session_duration');
            $table->boolean('is_active')->default(1);
            $table->integer('clinic_id')->unsigned();
            $table->integer('specialist_id')->unsigned();
            $table->foreign('id')->references('id')->on('users')
                ->onDelete('cascade');
            $table->foreign('clinic_id')->references('id')->on('clinics')
                ->onDelete('cascade');
            $table->foreign('specialist_id')->references('id')->on('specialists')
                ->onDelete('cascade');
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
        Schema::dropIfExists('doctors');
    }
}
