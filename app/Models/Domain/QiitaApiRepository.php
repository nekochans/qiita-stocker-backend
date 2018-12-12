<?php
/**
 * QiitaApiRepository
 */

namespace App\Models\Domain;

use App\Models\Domain\Stock\StockEntities;

/**
 * Interface QiitaApiRepository
 * @package App\Models\Domain
 */
interface QiitaApiRepository
{
    /**
     * ストック一覧を取得する
     *
     * @param string $qiitaUserName
     * @return StockEntities
     */
    public function fetchStock(string $qiitaUserName): StockEntities;
}
