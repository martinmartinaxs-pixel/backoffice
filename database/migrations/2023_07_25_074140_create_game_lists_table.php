<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGameListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('game_lists', function (Blueprint $table) {
            $table->id();
            $table->string('game_code');
            $table->string('game_id');
            $table->string('game_name');
            $table->string('game_status')->default(1);
            $table->string('game_image')->nullable();
            $table->longText('game_setting')->nullable();
            $table->string('game_type');
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
        Schema::dropIfExists('game_lists');
    }
}
