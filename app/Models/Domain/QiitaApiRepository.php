<?php
/**
 * QiitaApiRepository
 */

namespace App\Models\Domain;

use App\Models\Domain\Stock\StockValues;

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
     * @return StockValues
     */
    public function fetchStock(string $qiitaUserName): StockValues;
}
