<?php
/**
 * StockRepository
 */

namespace App\Infrastructure\Repositories\Eloquent;

use App\Models\Domain\Stock\StockValues;

/**
 * Class StockRepository
 * @package App\Infrastructure\Repositories\Eloquent
 */
class StockRepository implements \App\Models\Domain\Stock\StockRepository
{
    /**
     * ストックを保存する
     *
     * @param StockValues $stockEntities
     * @return mixed|void
     */
    public function save(StockValues $stockEntities)
    {
        // ストックをテーブルに保存する
    }
}
