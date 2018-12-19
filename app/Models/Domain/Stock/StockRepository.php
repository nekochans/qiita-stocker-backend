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
     * @param StockValues $stockValues
     */
    public function save(string $accountId, StockValues $stockValues);

    /**
     * ストック一覧を取得する
     *
     * @param string $accountId
     * @param null $limit
     * @param int $offset
     * @return StockEntities
     */
    public function searchByAccountId(string $accountId, $limit = null, $offset = 0): StockEntities;

    /**
     * ストック一覧の件数を取得する
     *
     * @param string $accountId
     * @return int
     */
    public function getCountByAccountId(string $accountId): int;

    /**
     * ストックを削除する
     *
     * @param string $accountId
     * @param array $articleIdList
     */
    public function delete(string $accountId, array $articleIdList);

    /**
     * ストックを更新する
     *
     * @param StockEntities $stockEntities
     */
    public function update(StockEntities $stockEntities);
    /**
     * stocks_tags テーブルにデータを保存する
     *
     * @param int $stockId
     * @param array $tags
     */
    public function saveStocksTags(int $stockId, array $tags);

    /**
     * stocks_tags テーブルからデータを削除する
     *
     * @param int $stockId
     * @param array $tags
     */
    public function deleteStocksTags(int $stockId, array $tags);
}
