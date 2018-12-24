<?php
/**
 * QiitaApiRepository
 */

namespace App\Models\Domain;

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
     * @return array
     */
    public function fetchStock(string $qiitaUserName, int $page, int $perPage): array;
}
