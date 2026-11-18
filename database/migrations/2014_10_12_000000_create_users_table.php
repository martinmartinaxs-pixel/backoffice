<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username');
            $table->string('password');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('currency')->default('TH');
            $table->string('credit')->default('0');
            $table->integer('position')->default(1);
            $table->integer('user_status')->default(1);
            $table->integer('parent_id')->nullable();
            $table->integer('use_wallet')->default(1);
            $table->longText('token')->nullable();
            $table->longText('operator_token')->nullable();
            $table->longText('login_token')->nullable();
            $table->integer('day_sell')->default(0);
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
