<?php
/**
 * StockRepository
 */

namespace App\Models\Domain\Stock;

use App\Models\Domain\Account\AccountEntity;

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
     * @param StockValues $stockValues
     * @return mixed
     */
    public function save(string $accountId, StockValues $stockValues);

    /**
     * ストック一覧を取得する
     *
     * @param AccountEntity $accountEntity
     * @return StockEntities
     */
    public function search(AccountEntity $accountEntity): StockEntities;
}
