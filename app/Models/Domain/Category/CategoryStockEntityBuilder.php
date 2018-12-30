<?php
/**
 * CategoryStockEntityBuilder
 */

namespace App\Models\Domain\Category;

/**
 * Class CategoryStockEntityBuilder
 * @package App\Models\Domain\Category
 */
class CategoryStockEntityBuilder
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
     * @return string
     */
    public function getArticleId(): string
    {
        return $this->articleId;
    }

    /**
     * @param string $articleId
     */
    public function setArticleId(string $articleId): void
    {
        $this->articleId = $articleId;
    }

    public function build()
    {
        return new CategoryStockEntity($this);
    }
}
