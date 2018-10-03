<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Model;

class AccessToken extends Model
{
    /**
     * モデルと関連しているテーブル
     *
     * @var string
     */
    protected $table = 'accounts_access_tokens';
}
