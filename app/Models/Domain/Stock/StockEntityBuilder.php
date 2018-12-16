<?php
/**
 * StockEntityBuilder
 */

namespace App\Models\Domain\Stock;

/**
 * Class StockEntityBuilder
 * @package App\Models\Domain\Stock
 */
class StockEntityBuilder
{
    /**
     * ストックID
     *
     * @var int
     */
    private $id;

    /**
     * StockValue
     *
     * @var StockValue
     */
    private $stockValue;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return StockValue
     */
    public function getStockValue(): StockValue
    {
        return $this->stockValue;
    }

    /**
     * @param StockValue $stockValue
     */
    public function setStockValue(StockValue $stockValue): void
    {
        $this->stockValue = $stockValue;
    }

    /**
     * @return StockEntity
     */
    public function build(): StockEntity
    {
        return new StockEntity($this);
    }
}
