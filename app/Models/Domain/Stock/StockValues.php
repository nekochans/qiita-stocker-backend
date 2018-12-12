<?php
/**
 * StockValues
 */

namespace App\Models\Domain\Stock;

/**
 * Class StockValues
 * @package App\Models\Domain\Stock
 */
class StockValues
{
    /**
     *
     * @var StockValue[]
     */
    private $stockValues;

    /**
     * StockEntities constructor.
     * @param StockValue ...$stockValues
     */
    public function __construct(StockValue ...$stockValues)
    {
        $this->stockValues = $stockValues;
    }

    /**
     * @return StockValue[]
     */
    public function getStockEntities(): array
    {
        return $this->stockValues;
    }
}
