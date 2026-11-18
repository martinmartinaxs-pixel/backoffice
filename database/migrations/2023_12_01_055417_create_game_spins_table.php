<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGameSpinsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('game_spins', function (Blueprint $table) {
            $table->id();
            $table->integer('spin_type')->default(1);
            $table->json('result')->nullable();
            $table->double('multi')->nullable();
            $table->integer('is_fs')->nullable();
            $table->integer('spin_length')->nullable();
            $table->string('game_code')->nullable();
            $table->integer('is_check')->nullable();
            $table->integer('fs_count')->nullable();
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
        Schema::dropIfExists('game_spins');
    }
}
