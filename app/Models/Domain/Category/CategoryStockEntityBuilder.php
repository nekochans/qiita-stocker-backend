<?php
/**
 * CategoryStockEntityBuilder
 */

namespace App\Models\Domain\Category;

use App\Models\Domain\Stock\StockValue;

/**
 * Class CategoryStockEntityBuilder
 * @package App\Models\Domain\Category
 */
class CategoryStockEntityBuilder
{
    /**
     * ID
     *
     * @var int
     */
    private $id;

    /**
     * カテゴリID
     *
     * @var int
     */
    private $categoryId;

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
     * @return int
     */
    public function getCategoryId(): int
    {
        return $this->categoryId;
    }

    /**
     * @param int $categoryId
     */
    public function setCategoryId(int $categoryId): void
    {
        $this->categoryId = $categoryId;
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

    public function build()
    {
        return new CategoryStockEntity($this);
    }
}
