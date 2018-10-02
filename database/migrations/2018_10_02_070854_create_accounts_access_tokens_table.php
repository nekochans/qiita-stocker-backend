<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountsAccessTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accounts_access_tokens', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('account_id');
            $table->string('access_token', 255);
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamps();
            $table->unique('account_id');
            $table->unique('access_token');
            $table->foreign('account_id')->references('id')->on('accounts');
            $table->index('access_token');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('accounts_access_tokens');
    }
}
