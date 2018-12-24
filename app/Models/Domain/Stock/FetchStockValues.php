<?php
/**
 * FetchStockValues
 */

namespace App\Models\Domain\Stock;

/**
 * Class FetchStockValues
 * @package App\Models\Domain\Stock
 */
class FetchStockValues
{
    /**
     * ストックの総数
     *
     * @var int
     */
    private $totalCount;

    /**
     *
     * @var StockValue[]
     */
    private $stockValues;


    /**
     * FetchStockValues constructor.
     * @param StockValue ...$stockValues
     * @param int $totalCount
     */
    public function __construct(int $totalCount, StockValue ...$stockValues)
    {
        $this->totalCount = $totalCount;
        $this->stockValues = $stockValues;
    }

    /**
     * @return StockValue[]
     */
    public function getStockValues(): array
    {
        return $this->stockValues;
    }

    /**
     * @return int
     */
    public function getTotalCount(): int
    {
        return $this->totalCount;
    }
}
