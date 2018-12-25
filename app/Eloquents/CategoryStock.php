<?php
/**
 * CategoryStock
 */

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Model;

class CategoryStock extends Model
{
    /**
     * モデルと関連しているテーブル
     *
     * @var string
     */
    protected $table = 'categories_stocks';
}
