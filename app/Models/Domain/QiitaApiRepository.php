<?php
/**
 * QiitaApiRepository
 */

namespace App\Models\Domain;

use App\Models\Domain\Stock\StockValues;
use App\Models\Domain\Account\AccountEntity;
use App\Models\Domain\Stock\FetchStockValues;
use App\Models\Domain\Category\CategoryStockEntities;

/**
 * Interface QiitaApiRepository
 * @package App\Models\Domain
 */
interface QiitaApiRepository
{
    /**
     * ストック一覧を取得する
     *
     * @param AccountEntity $accountEntity
     * @param int $page
     * @param int $perPage
     * @return FetchStockValues
     */
    public function fetchStocks(AccountEntity $accountEntity, int $page, int $perPage): FetchStockValues;

    /**
     * アイテム一覧を取得する
     *
     * @param AccountEntity $accountEntity
     * @param CategoryStockEntities $categoryStockEntities
     * @return StockValues
     */
    public function fetchItems(AccountEntity $accountEntity, CategoryStockEntities $categoryStockEntities): StockValues;
}
