<?php
/**
 * LinkHeaderService
 */

namespace App\Models\Domain\Stock;

/**
 * Class LinkHeaderService
 * @package App\Models\Domain\Stock
 */
class LinkHeaderService
{
    /**
     * 次のページが存在するか判定する
     *
     * @param int $page
     * @param int $totalPage
     * @return bool
     */
    public static function hasNextPage(int $page, int $totalPage): bool
    {
        return $page <= $totalPage - 1;
    }

    /**
     * 最後のページが存在するか判定する
     *
     * @param int $page
     * @param int $totalPage
     * @return bool
     */
    public static function hasLastPage(int $page, int $totalPage): bool
    {
        return $page !== $totalPage;
    }

    /**
     * 最初のページが存在するか判定する
     *
     * @param int $page
     * @return bool
     */
    public static function hasFirstPage(int $page): bool
    {
        return $page !== 1;
    }

    /**
     * 前のページが存在するか判定する
     *
     * @param int $page
     * @return bool
     */
    public static function hasPrevPage(int $page): bool
    {
        return $page - 1 > 0;
    }
}
