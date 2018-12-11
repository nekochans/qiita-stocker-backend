<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    /**
     * モデルと関連しているテーブル
     *
     * @var string
     */
    protected $table = 'stocks';
}
