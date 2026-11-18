<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSeamlessSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('seamless_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('operator_token');
            $table->string('secret_key');
            $table->longText('setting_url')->nullable();
            $table->string('setting_name')->nullable();
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
        Schema::dropIfExists('seamless_settings');
    }
}
