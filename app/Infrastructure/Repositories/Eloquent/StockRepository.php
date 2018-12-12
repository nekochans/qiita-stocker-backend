<?php
/**
 * StockRepository
 */

namespace App\Infrastructure\Repositories\Eloquent;

use App\Models\Domain\Stock\StockEntities;

/**
 * Class StockRepository
 * @package App\Infrastructure\Repositories\Eloquent
 */
class StockRepository implements \App\Models\Domain\Stock\StockRepository
{
    /**
     * ストックを保存する
     *
     * @param StockEntities $stockEntities
     * @return mixed|void
     */
    public function save(StockEntities $stockEntities)
    {
        // ストックをテーブルに保存する
    }
}
