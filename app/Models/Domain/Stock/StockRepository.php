<?php
/**
 * StockRepository
 */

namespace App\Models\Domain\Stock;

/**
 * Interface StockRepository
 * @package App\Models\Domain\Stock
 */
interface StockRepository
{
    /**
     * ストックを保存する
     *
     * @param StockValues $stockEntities
     * @return mixed
     */
    public function save(StockValues $stockEntities);
}
