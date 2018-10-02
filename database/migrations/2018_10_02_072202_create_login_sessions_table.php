<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLoginSessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('login_sessions', function (Blueprint $table) {
            $table->string('id', 255);
            $table->unsignedInteger('account_id');
            $table->dateTime('expired_on');
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamps();
            $table->primary('id');
            $table->foreign('account_id')->references('id')->on('accounts');
            $table->index('account_id');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('login_sessions');
    }
}
