<?php
/**
 * CategoryStockEntity
 */

namespace App\Models\Domain\Category;

use App\Models\Domain\Stock\StockValue;

class CategoryStockEntity
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
     * CategoryStockEntity constructor.
     * @param CategoryStockEntityBuilder $builder
     */
    public function __construct(CategoryStockEntityBuilder $builder)
    {
        $this->id = $builder->getId();
        $this->categoryId = $builder->getCategoryId();
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
     * @return int
     */
    public function getCategoryId(): int
    {
        return $this->categoryId;
    }

    /**
     * @return StockValue
     */
    public function getStockValue(): StockValue
    {
        return $this->stockValue;
    }

    /**
     * カテゴリとストックのリレーションが作成されていなかった場合に使用するメッセージ
     *
     * @return string
     */
    public static function categoryStockNotFoundMessage(): string
    {
        return '不正なリクエストが行われました。';
    }

    /**
     * IDのバリデーションエラー時に利用するメッセージ
     *
     * @return string
     */
    public static function idValidationErrorMessage(): string
    {
        return '不正なリクエストが行われました。';
    }
}
