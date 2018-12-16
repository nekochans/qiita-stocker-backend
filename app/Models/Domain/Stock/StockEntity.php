<?php
/**
 * StockEntity
 */

namespace App\Models\Domain\Stock;

/**
 * Class StockEntity
 * @package App\Models\Domain\Stock
 */
class StockEntity
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
     * StockEntity constructor.
     * @param StockEntityBuilder $builder
     */
    public function __construct(StockEntityBuilder $builder)
    {
        $this->id = $builder->getId();
        $this->stockValue = $builder->getStockValue();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return StockValue
     */
    public function getStockValue(): StockValue
    {
        return $this->stockValue;
    }

    /**
     * ストックが更新されているか確認する
     *
     * @param StockValue $stockValue
     * @return bool
     */
    public function isUpdatedExceptTag(StockValue $stockValue): bool
    {
        $isChanged = false;

        if ($this->getStockValue()->getTitle() !== $stockValue->getTitle() ||
            $this->getStockValue()->getUserId() !== $stockValue->getUserId() ||
            $this->getStockValue()->getProfileImageUrl() !== $stockValue->getProfileImageUrl() ||
            $this->getStockValue()->getArticleCreatedAt() != $stockValue->getArticleCreatedAt() ||
            $this->getStockValue()->getArticleId() !== $stockValue->getArticleId()
        ) {
            $isChanged = true;
        }

        return $isChanged;
    }
}
