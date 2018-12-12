<?php
/**
 * StockEntities
 */

namespace App\Models\Domain\Stock;

/**
 * Class StockEntities
 * @package App\Models\Domain\Stock
 */
class StockEntities
{
    /**
     *
     * @var StockEntity[]
     */
    private $stockEntities;

    /**
     * StockEntities constructor.
     * @param StockEntity ...$stockEntities
     */
    public function __construct(StockEntity ...$stockEntities)
    {
        $this->stockEntities = $stockEntities;
    }

    /**
     * @return StockEntity[]
     */
    public function getStockEntities(): array
    {
        return $this->stockEntities;
    }
}
