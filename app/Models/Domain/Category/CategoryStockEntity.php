<?php
/**
 * CategoryStockEntity
 */

namespace App\Models\Domain\Category;

class CategoryStockEntity
{
    /**
     * カテゴリID
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
     * Article ID
     *
     * @var string
     */
    private $articleId;

    /**
     * CategoryStockEntity constructor.
     * @param CategoryStockEntityBuilder $builder
     */
    public function __construct(CategoryStockEntityBuilder $builder)
    {
        $this->id = $builder->getId();
        $this->categoryId = $builder->getCategoryId();
        $this->articleId = $builder->getArticleId();
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
     * @return string
     */
    public function getArticleId(): string
    {
        return $this->articleId;
    }
}
