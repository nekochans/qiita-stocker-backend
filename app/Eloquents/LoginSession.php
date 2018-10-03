<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Model;

class LoginSession extends Model
{
    /**
     * モデルと関連しているテーブル
     *
     * @var string
     */
    protected $table = 'login_sessions';
}
