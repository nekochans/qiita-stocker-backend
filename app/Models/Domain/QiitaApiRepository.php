<?php
/**
 * QiitaApiRepository
 */

namespace App\Models\Domain;

use App\Models\Domain\Stock\FetchStockValues;

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
     * @param int $page
     * @param int $perPage
     * @return FetchStockValues
     */
    public function fetchStock(string $qiitaUserName, int $page, int $perPage): FetchStockValues;
}
