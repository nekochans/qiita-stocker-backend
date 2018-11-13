<?php
/**
 * CategoryEntityBuilder
 */

namespace App\Models\Domain\Category;

/**
 * Class CategoryEntityBuilder
 * @package App\Models\Domain
 */
class CategoryEntityBuilder
{
    /**
     * カテゴリID
     *
     * @var int
     */
    private $Id;

    /**
     * カテゴリ名
     *
     * @var CategoryNameValue
     */
    private $categoryNameValue;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->Id;
    }

    /**
     * @param int $Id
     */
    public function setId(int $Id): void
    {
        $this->Id = $Id;
    }

    /**
     * @return CategoryNameValue
     */
    public function getCategoryNameValue(): CategoryNameValue
    {
        return $this->categoryNameValue;
    }

    /**
     * @param CategoryNameValue $categoryNameValue
     */
    public function setCategoryNameValue(CategoryNameValue $categoryNameValue): void
    {
        $this->categoryNameValue = $categoryNameValue;
    }

    /**
     * @return CategoryEntity
     */
    public function build(): CategoryEntity
    {
        return new CategoryEntity($this);
    }
}
