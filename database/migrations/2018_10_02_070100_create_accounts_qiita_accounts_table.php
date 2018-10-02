<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountsQiitaAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accounts_qiita_accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('account_id');
            $table->string('qiita_account_id', 255);
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamps();
            $table->unique('account_id');
            $table->unique('qiita_account_id');
            $table->foreign('account_id')->references('id')->on('accounts');
            $table->index('qiita_account_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('accounts_qiita_accounts');
    }
}
