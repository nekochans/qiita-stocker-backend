<?php
/**
 * CategoryStockEntities
 */

namespace App\Models\Domain\Category;

/**
 * Class CategoryStockEntities
 * @package App\Models\Domain\Category
 */
class CategoryStockEntities
{
    /**
     *
     * @var CategoryStockEntity[]
     */
    private $categoryStockEntities;

    /**
     * CategoryStockEntities constructor.
     * @param CategoryStockEntity ...$categoryStockEntities
     */
    public function __construct(CategoryStockEntity ...$categoryStockEntities)
    {
        $this->categoryStockEntities = $categoryStockEntities;
    }

    /**
     * @return CategoryStockEntity[]
     */
    public function getCategoryStockEntities(): array
    {
        return $this->categoryStockEntities;
    }

    /**
     * ArticleIDリストを生成する
     *
     * @return array
     */
    public function buildArticleIdList(): array
    {
        $categoryStockEntityList = $this->getCategoryStockEntities();

        $stockArticleIdList = [];
        foreach ($categoryStockEntityList as $categoryStockEntity) {
            array_push($stockArticleIdList, $categoryStockEntity->getStockValue()->getArticleId());
        }
        return $stockArticleIdList;
    }
}
