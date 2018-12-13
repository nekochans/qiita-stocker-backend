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
     * @param string $accountId
     * @param StockValues $stockEntities
     * @return mixed
     */
    public function save(string $accountId, StockValues $stockEntities);
}
