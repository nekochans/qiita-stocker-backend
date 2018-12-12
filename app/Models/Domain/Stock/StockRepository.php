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
     * @param StockEntities $stockEntities
     * @return mixed
     */
    public function save(StockEntities $stockEntities);
}
